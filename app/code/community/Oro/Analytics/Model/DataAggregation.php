<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Analytics
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

/**
 * Oro Analytics Data Aggregation Model
 *
 * @property Oro_Analytics_Model_Resource_DataAggregation $_resource
 * @method Oro_Analytics_Model_Resource_DataAggregation getResource()
 * @method Oro_Analytics_Model_Resource_DataAggregation _getResource()
 */
class Oro_Analytics_Model_DataAggregation extends Mage_Core_Model_Abstract
{
    const PARTITION_INCREMENT_PERIOD        = 1;
    const PARTITION_INCREMENT_PART          = Zend_Date::MONTH;

    const CRON_PERIOD                       = 1;
    const CRON_PERIOD_PART                  = Zend_Date::HOUR;

    const MAX_ITERATION_PER_RUN_CRON        = 100;
    const MAX_ITERATION_PER_RUN_MANUALLY    = 5;

    const XML_DOWN_CRON_FINISH_FLAG         = 'oro_analytics/down_cron_finished';

    /**
     * Aggregate period from date
     *
     * @var Zend_Date
     */
    protected $_datePeriodFrom;

    /**
     * Aggregate period to date
     *
     * @var Zend_Date
     */
    protected $_datePeriodTo;

    /**
     * Is job run by cron
     *
     * @var bool
     */
    protected $_isCron          = true;

    /**
     * Index data from (start) datetime
     *
     * @var Zend_Date
     */
    protected $_jobDateFrom;

    /**
     * Index data to (end) datetime
     *
     * @var Zend_Date
     */
    protected $_jobDateTo;

    /**
     * Index direction (to down - true, to up - false)
     *
     * @var bool
     */
    protected $_jobDirection;

    /**
     * Count of ajax iterations
     *
     * @var int
     */
    protected $_countIteration = 1;

    /**
     * Current ajax iteration number
     *
     * @var int
     */
    protected $_currentIteration = 0;

    /**
     * Current percent of work
     *
     * @var int
     */
    protected $_currentPercentDoneWork = 0;

    /**
     * Init connections, save local time
     *
     */
    public function __construct()
    {
        $this->_init('oro_analytics/dataAggregation');
        $this->_resource = $this->_getResource();
    }

    /**
     * Run reverse cron job
     */
    public function reverseParseDataCronJob()
    {
        $this->parseDataCronJob(array(
            'goToDown'  => true
        ));
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
     * Defines job period and direction
     *
     * @param array $config
     */
    protected function _initCronJob(array &$config)
    {
        $dbTime     = $this->_resource->getDbTime($config['goToDown']);
        $maxTime    = new Zend_Date(null, Varien_Date::DATETIME_INTERNAL_FORMAT);
        $maxTime->setMinute(0)->setSecond(0);

        $this->_isCron       = true;
        $this->_jobDirection = false;

        if ($config['goToDown']) {
            //reverse direction aggregation
            $start  = new Zend_Date($dbTime, Varien_Date::DATETIME_INTERNAL_FORMAT);
            $start->setMinute(0)->setSecond(0);
            $finish = new Zend_Date($this->_resource->getStartDbTime(), Varien_Date::DATETIME_INTERNAL_FORMAT);
            $finish->setMinute(0)->setSecond(0);

            $this->_jobDirection = true;
        } else if (isset($config['dateStart']) && isset($config['dateEnd'])) {
            // aggregation by period from user run or then extension installation is run
            if (empty($config['isRealCron'])) {
                $this->_isCron = false;
            }

            $finish  = new Zend_Date($config['dateEnd'], Varien_Date::DATETIME_INTERNAL_FORMAT);
            $finish->setMinute(0)->setSecond(0);
            if ($finish->compare($maxTime) == 1) {
                $finish = clone $maxTime;
            }

            $start  = new Zend_Date($config['dateStart'], Varien_Date::DATETIME_INTERNAL_FORMAT);
            $start->setMinute(0)->setSecond(0);
        } else {
            //normal cron aggregation
            if ($dbTime === null) {
                $start  = clone $maxTime;
                $start->subMonth(1);
            } else {
                $start  = new Zend_Date($dbTime, Varien_Date::DATETIME_INTERNAL_FORMAT);
                $start->setMinute(0)->setSecond(0);
            }
            $finish = clone $maxTime;
        }

        $this->_jobDateFrom  = $start;
        $this->_jobDateTo    = $finish;

        if (!$this->_isCron) {
            $this->_calculateIterationsOfAjaxCalls($config);
            $this->_calculatePercentForProgressBar();
        }
    }

    /**
     * Calculate number of iterations
     *
     * @param array $config
     */
    protected function _calculateIterationsOfAjaxCalls(array $config)
    {
        if (!isset($config['countIteration']) || $config['countIteration'] == null) {
            $maxCount = $this->_isCron ? self::MAX_ITERATION_PER_RUN_CRON : self::MAX_ITERATION_PER_RUN_MANUALLY;

            $datePeriodFrom    = clone $this->_jobDateFrom;
            $datePeriodTo      = clone $this->_jobDateTo;

            $fromDate = $datePeriodFrom->get(Varien_Date::DATETIME_INTERNAL_FORMAT);
            $toDate = $datePeriodTo->get(Varien_Date::DATETIME_INTERNAL_FORMAT);
            $alreadyAggregatedCount = $this->_resource->getAggregatedColumnsCount($fromDate, $toDate);

            $difference = $datePeriodTo->sub($datePeriodFrom);
            $measure = new Zend_Measure_Time($difference->toValue(), Zend_Measure_Time::SECOND);
            $measure->convertTo(Zend_Measure_Time::HOUR);
            $differenceInHour = $measure->getValue();
            $this->_countIteration = ceil(($differenceInHour-$alreadyAggregatedCount) / $maxCount);
            $this->_currentIteration=1;
        } else {
            $this->_countIteration = (int) $config['countIteration'];
            $this->_currentIteration = (int) $config['currentIteration'] + 1;
        }
    }

    /**
     * Calculate percent of working
     */
    protected function _calculatePercentForProgressBar()
    {
        $this->_currentPercentDoneWork = min(100, ceil(($this->_currentIteration / $this->_countIteration) * 100));
    }

    /**
     * Saves data for period
     *
     */
    protected function _runCronJob()
    {
        // save cron data
        $fromDate = $this->_datePeriodFrom->get(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $this->_resource->insertNewMaxDbTime($fromDate, $this->_isCron);

        $this->_parseLogins();
        $this->_parseCustomerRegistrations();
        $this->_parseRefers();
        $this->_parsePageViews();
        $this->_parseCreatedCarts();
        $this->_parseCreatedOrders();
        $this->_parseProductsInCart();
        $this->_parseWishlistItems();
        $this->_parseProductViewItems();
        $this->_parseVisitors();
    }

    /**
     * Finalizes cron job and returns data
     *
     * @param int $runCount
     * @return array
     */
    protected function _finishCronJob($runCount)
    {
        //set cron finish flag
        if ($this->_isCron && $this->_jobDirection && !$runCount) {
            if (!Mage::getStoreConfig(self::XML_DOWN_CRON_FINISH_FLAG)) {
                Mage::getConfig()->saveConfig(self::XML_DOWN_CRON_FINISH_FLAG, 1);
            }
        }

        // 'from' incremented on 1 hour
        return array(
            'count' => $runCount,
            'from'  => $this->_datePeriodFrom->get(Varien_Date::DATETIME_INTERNAL_FORMAT),
            'to'    => $this->_jobDateTo->get(Varien_Date::DATETIME_INTERNAL_FORMAT),
            'currentIteration' => $this->_currentIteration,
            'countIteration' => $this->_countIteration,
            'currentPercentDoneWork' => $this->_currentPercentDoneWork
        );
    }

    /**
     * Main function to parse data
     *
     * @param array $config
     * @return array|int
     */
    public function parseDataCronJob($config = array('goToDown' => false))
    {
        if ($this->_resource->checkIfAggregationRun()) {
            return 0;
        }

        try {
            $this->_initCronJob($config);
            $this->_resource->setAggregationState(true);

            $runCount = 0;
            $maxCount = $this->_isCron ? self::MAX_ITERATION_PER_RUN_CRON : self::MAX_ITERATION_PER_RUN_MANUALLY;

            $this->_datePeriodFrom    = $this->_jobDateFrom;
            $this->_datePeriodTo      = clone $this->_datePeriodFrom;

            if ($this->_jobDirection) {
                // $this->_datePeriodTo->sub(self::CRON_PERIOD, self::CRON_PERIOD_PART);
                $this->_datePeriodFrom->sub(self::CRON_PERIOD, self::CRON_PERIOD_PART);
            } else {
                $this->_datePeriodTo->add(self::CRON_PERIOD, self::CRON_PERIOD_PART);
            }

            while ($runCount < $maxCount && (
                ($this->_jobDirection === false && $this->_jobDateTo->compare($this->_datePeriodFrom) > 0)
                || ($this->_jobDirection === true && $this->_jobDateTo->compare($this->_datePeriodFrom) < 0)
            )) {
                //check if data not already been aggregated
                $fromDate = $this->_datePeriodFrom->get(Varien_Date::DATETIME_INTERNAL_FORMAT);

                if (!$this->_resource->getIfDateAggregated($fromDate)) {
                    $runCount ++;
                    $this->_runCronJob();
                }

                if ($this->_jobDirection) {
                    $this->_datePeriodFrom->sub(self::CRON_PERIOD, self::CRON_PERIOD_PART);
                    $this->_datePeriodTo->sub(self::CRON_PERIOD, self::CRON_PERIOD_PART);
                } else {
                    $this->_datePeriodFrom->add(self::CRON_PERIOD, self::CRON_PERIOD_PART);
                    $this->_datePeriodTo->add(self::CRON_PERIOD, self::CRON_PERIOD_PART);
                }
            }

            $this->_resource->setAggregationState(false);

            // run daily aggregation if user run hourly manually or if we go down
            if ((isset($config['dateStart']) && isset($config['dateEnd'])) || $config['goToDown']) {
                /* @var $processor Oro_Analytics_Model_DailyAggregation */
                $processor = Mage::getModel('oro_analytics/dailyAggregation');
                $processor->parseDataCronJob($config);
            }

            return $this->_finishCronJob($runCount);
        } catch(Exception $e) {
            $this->_resource->setAggregationState(false);
            return 0;
        }
    }

    /**
     * Saves count of visitors data for period
     *
     * @return $this
     */
    protected function _parseVisitors()
    {
        $visitors = $this->_resource->getVisitorsData($this->_datePeriodFrom, $this->_datePeriodTo);
        foreach ($visitors as $visitor) {
            $this->_resource->saveVisitorsData($visitor, $this->_datePeriodFrom, $this->_datePeriodTo);
        }

        return $this;
    }

    /**
     * Saves count of product views data for period
     *
     * @return $this
     */
    protected function _parseProductViewItems()
    {
        $productsInfo = $this->_resource->getProductsData($this->_datePeriodFrom, $this->_datePeriodTo);
        foreach ($productsInfo as $product) {
            $this->_resource->saveProductViewItemsData($product, $this->_datePeriodFrom, $this->_datePeriodTo);
        }

        return $this;
    }

    /**
     * Saves count of products in wishlist data for period
     *
     * @return $this
     */
    protected function _parseWishlistItems()
    {
        $wishlistItems = $this->_resource->getWishlistData($this->_datePeriodFrom, $this->_datePeriodTo);
        foreach ($wishlistItems as $wishlistItem) {
            $this->_resource->saveWishlistItemsData($wishlistItem, $this->_datePeriodFrom, $this->_datePeriodTo);
        }

        return $this;
    }

    /**
     * Saves count of products in Shopping cart data for period
     *
     * @return $this
     */
    protected function _parseProductsInCart()
    {
        $cartProducts = $this->_resource->getProductsInCartData($this->_datePeriodFrom, $this->_datePeriodTo);
        foreach ($cartProducts as $cartProduct) {
            $this->_resource->saveProductsInCartData($cartProduct, $this->_datePeriodFrom, $this->_datePeriodTo);
        }

        return $this;
    }

    /**
     * Saves count of Shopping carts data for period
     *
     * @return $this
     */
    protected function _parseCreatedCarts()
    {
        $userCarts = $this->_resource->getCreatedCartsData($this->_datePeriodFrom, $this->_datePeriodTo);
        foreach ($userCarts as $userCart) {
            $this->_resource->saveCreatedCartsData($userCart, $this->_datePeriodFrom, $this->_datePeriodTo);
        }

        return $this;
    }

    /**
     * Saves count of orders data for period
     *
     * @return $this
     */
    protected function _parseCreatedOrders()
    {
        $userOrders = $this->_resource->getCreatedOrdersData($this->_datePeriodFrom, $this->_datePeriodTo);
        foreach ($userOrders as $userOrder) {
            $this->_resource->saveCreatedOrdersData($userOrder, $this->_datePeriodFrom, $this->_datePeriodTo);
        }

        return $this;
    }

    /**
     * Save count of page views data for period
     *
     * @return $this
     */
    protected function _parsePageViews()
    {
        $pagesInfo = $this->_resource->getPageViewsData($this->_datePeriodFrom, $this->_datePeriodTo);
        foreach ($pagesInfo as $userPage) {
            $this->_resource->savePageViewsData($userPage, $this->_datePeriodFrom, $this->_datePeriodTo);
        }

        return $this;
    }

    /**
     * Save refers data for reriod
     *
     * @return $this
     */
    protected function _parseRefers()
    {
        $refersInfo = $this->_resource->getRefersData($this->_datePeriodFrom, $this->_datePeriodTo);
        foreach ($refersInfo as $refer) {
            $this->_resource->saveRefersData($refer, $this->_datePeriodFrom, $this->_datePeriodTo);
        }

        return $this;
    }

    /**
     * Save count of new customers data for period
     *
     * @return $this
     */
    protected function _parseCustomerRegistrations()
    {
        $userRegistrations = $this->_resource->getRegistrationsData($this->_datePeriodFrom, $this->_datePeriodTo);
        foreach ($userRegistrations as $user) {
            $this->_resource->saveCustomerRegistrationsData($user, $this->_datePeriodFrom, $this->_datePeriodTo);
        }

        return $this;
    }

    /**
     * Saves count of returned customers for period
     *
     * @return $this
     */
    protected function _parseLogins()
    {
        $loginsData = $this->_resource->getLoginsData($this->_datePeriodFrom, $this->_datePeriodTo);
        foreach ($loginsData as $customerLogin) {
            $this->_resource->saveLoginsData($customerLogin, $this->_datePeriodFrom, $this->_datePeriodTo);
        }

        return $this;
    }
}
