<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Analytics
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Analytics_Model_DailyAggregation extends Mage_Core_Model_Abstract
{
    const PARTITION_INCREMENT_PERIOD = '1';
    const PARTITION_INCREMENT_PART = Zend_Date::MONTH;

    const CRON_PERIOD = '1';
    const CRON_PERIOD_PART = Zend_Date::DAY;

    const MAX_ITERATION_PER_RUN = 100;

    /**
     * @var Zend_Date
     */
    private $periodStart;

    /**
     * @var Zend_Date
     */
    private $periodEnd;

    /**
     * @var Zend_Date
     */
    private $currentTime;

    /**
     * @var Oro_Analytics_Model_Resource_DailyAggregation
     */
    private $resource;

    /**
     * Init connections, save local time
     */
    public function __construct()
    {
        $this->_init('oro_analytics/dailyAggregation');
        $this->currentTime = $this->getLocaleDate();
        $this->resource = $this->getResource();
    }

    /**
     * @param string $date
     * @return Zend_Date
     */
    public function getLocaleDate($date = null)
    {
        return Mage::app()->getLocale()->date($date);
    }

    /**
     * Run normal cron job
     */
    public function normalParseDataCronJob()
    {
        $this->parseDataCronJob(array(
            'goToDown'  => false
        ));
    }

    /**
     * Main function to parse data
     *
     * @param array $config
     * @return int
     */
    public function parseDataCronJob($config = array('goToDown' => false))
    {
        if (!$this->getResource()->checkIfAggregationRun()) {
            try {
                $this->getResource()->setAggregationState(true);

                $isRealCron = true;
                $this->currentTime->setTime('00:00:00');
                $dbTime = $this->resource->getDbTime($config['goToDown']);
                if (isset($config['dateStartFirst']) && isset($config['dateStartEnd'])){
                    $config['dateStart'] = $config['dateStartFirst'];
                    $config['dateEnd'] = $config['dateStartEnd'];
                }
                if ($config['goToDown']) {
                    //reverse direction aggregation
                    $this->periodEnd = $this->getLocaleDate(new Zend_Date($dbTime), 'yyyy-MM-dd HH:m:s');
                    if ($dbTime == null) {
                        $this->periodEnd = $this->getUtcDate(new Zend_Date($this->currentTime, 'yyyy-MM-dd 00:00:00'));
                        $this->periodEnd->subDay(1);
                    }
                    $this->periodStart = clone $this->periodEnd;
                    $this->periodStart->sub(self::CRON_PERIOD, self::CRON_PERIOD_PART);
                    $dbStartTime = $this->getLocaleDate(new Zend_Date($this->resource->getStartDbTime(), 'yyyy-MM-dd HH:m:s'));
                    $maxTime = clone $dbStartTime;

                } elseif (!isset($config['dateStart']) && !isset($config['dateEnd'])) {
                    //normal cron aggregation
                    $maxDbDate = $this->getLocaleDate(new Zend_Date($dbTime), 'yyyy-MM-dd HH:m:s');

                    $maxDbDate->setTime('00:00:00');
                    $this->periodStart = $this->getUtcDate(new Zend_Date($maxDbDate));
                    if (!$dbTime) {
                        $this->periodStart->subMonth(1);
                    } else {
                        $this->periodStart->add(self::CRON_PERIOD, self::CRON_PERIOD_PART);
                    }
                    $maxTime = clone $this->currentTime;
                    $this->periodEnd = clone $this->periodStart;
                    $this->periodEnd->add(self::CRON_PERIOD, self::CRON_PERIOD_PART);

                } else {
                    //aggregation by period from user run or then extension installation is run
                    if (isset($config['isRealCron']) && $config['isRealCron']) {
                        $isRealCron = true;
                    } else {
                        $isRealCron = false;
                    }

                    $this->periodStart = $this->getUtcDate(new Zend_Date($config['dateStart'], 'yyyy-MM-dd 00:00:00'));
                    $this->currentTime = $this->getLocaleDate(new Zend_Date($config['dateEnd'], 'yyyy-MM-dd HH:m:s'));

                    $maxTime = clone $this->currentTime;
                    $this->periodEnd = clone $this->periodStart;
                    $this->periodEnd->add(self::CRON_PERIOD, self::CRON_PERIOD_PART);
                }

                $runCount = 0;

                while (((!$config['goToDown'] && $maxTime->sub($this->periodEnd)->toValue() > 0) ||
                        ($config['goToDown'] && $maxTime->sub($this->periodEnd)->toValue() < 0)
                    ) && $runCount < self::MAX_ITERATION_PER_RUN) {

                    $startClone = clone $this->periodStart;
                    $startClone->sub(1, Zend_Date::DAY);

                    $endClone = clone $this->periodEnd;
                    $endClone->sub(1, Zend_Date::DAY);

                    if ($this->resource->checkIfDataExsists($startClone, $endClone)) {
                        $this->_aggregateDate($startClone, $endClone, $isRealCron);
                    }

                    if (!$this->resource->checkIfDataExsists($this->periodStart, $this->periodEnd)) {
                        break;
                    }

                    $this->_aggregateDate($this->periodStart, $this->periodEnd, $isRealCron);

                    $runCount ++;

                    //update time period
                    if (!$config['goToDown']) {
                        $this->periodStart->add(self::CRON_PERIOD, self::CRON_PERIOD_PART);
                        $this->periodEnd->add(self::CRON_PERIOD, self::CRON_PERIOD_PART);
                        $maxTime = clone $this->currentTime;
                    } else{
                        $this->periodStart->sub(self::CRON_PERIOD, self::CRON_PERIOD_PART);
                        $this->periodEnd->sub(self::CRON_PERIOD, self::CRON_PERIOD_PART);
                        $maxTime = clone $dbStartTime;
                    }
                }
                $this->getResource()->setAggregationState(false);
                return $runCount;
            } catch(Exception $e) {
                $this->getResource()->setAggregationState(false);
                return 0;
            }
        }
    }

    /**
     * Aggregate Data
     *
     * @param Zend_Date $periodStart
     * @param Zend_Date $periodEnd
     * @param integer $isRealCron
     */
    private function _aggregateDate(Zend_Date $periodStart, Zend_Date $periodEnd, $isRealCron)
    {
        //check if data not already been aggregated
        if (!$this->resource->getIfDateAgrregated($periodStart->get('y-MM-dd HH:m:s'))) {

            //save cron data
            $this->resource->insertNewMaxDbTime($periodStart->get('y-MM-dd HH:m:s'), $isRealCron, $periodStart->getTimezone());

            //save data
            $this->aggregateCustomer($periodStart, $periodEnd);
            $this->aggregateShop($periodStart, $periodEnd);
            $this->aggregatePage($periodStart, $periodEnd);
            $this->aggregateProduct($periodStart, $periodEnd);
            $this->aggregateRefer($periodStart, $periodEnd);
            $this->aggregateVisitors($periodStart, $periodEnd);
        }
    }

    /**
     * @param Zend_Date $date
     * @return Zend_Date
     */
    private function getUtcDate(Zend_Date $date)
    {
        return new Zend_Date(Mage::getSingleton("core/date")->gmtDate(null, $date->toString("yyyy-MM-dd HH:m:s")), 'yyyy-MM-dd HH:m:s');
    }

    /**
     *  save visitors table data
     */
    private function aggregateVisitors($periodStart, $periodEnd)
    {
        $visitors = $this->resource->getVisitorsData($periodStart, $periodEnd);
        foreach ($visitors as $visitor) {
            $this->resource->saveVisitorsData(
                $visitor,
                $this->periodStart,
                $this->periodEnd
            );
        }
    }

    /**
     * Save customer table data
     */
    private function aggregateCustomer($periodStart, $periodEnd)
    {
        $customersInfo = $this->resource->getCustomerData($periodStart, $periodEnd);
        foreach ($customersInfo as $customer) {
            $this->resource->saveCustomerData(
                $customer,
                $this->periodStart,
                $this->periodEnd
            );
        }
    }

    /**
     * Save shop table data
     */
    private function aggregateShop($periodStart, $periodEnd)
    {
        $shopInfo = $this->resource->getShopData($periodStart, $periodEnd);
        foreach ($shopInfo as $shop) {
            $this->resource->saveShopData(
                $shop,
                $this->periodStart,
                $this->periodEnd
            );
        }
    }

    /**
     * Save page table data
     */
    private function aggregatePage($periodStart, $periodEnd)
    {
        $pageInfo = $this->resource->getPageData($periodStart, $periodEnd);
        foreach ($pageInfo as $page) {
            $this->resource->savePageData(
                $page,
                $this->periodStart,
                $this->periodEnd
            );
        }
    }

    /**
     * Save product table data
     */
    private function aggregateProduct($periodStart, $periodEnd)
    {
        $pageInfo = $this->resource->getProductData($periodStart, $periodEnd);
        foreach ($pageInfo as $page) {
            $this->resource->saveProductData(
                $page,
                $this->periodStart,
                $this->periodEnd
            );
        }
    }

    /**
     * Save refer table data
     */
    private function aggregateRefer($periodStart, $periodEnd)
    {
        $referInfo = $this->resource->getReferData($periodStart, $periodEnd);
        foreach ($referInfo as $refer) {
            $this->resource->saveReferData(
                $refer,
                $this->periodStart,
                $this->periodEnd
            );
        }
    }
}
