<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Helper_Data extends Mage_Core_Helper_Abstract
{
    const DAYS_COUNT_FOR_HOURLY_PLOTS = 1;
    const DAYS_COUNT_FOR_DAILY_PLOTS = 64;
    const DAYS_COUNT_FOR_MONTHLY_PLOTS = 365;
    const DATE_FORMAT_FOR_HOURLY_PLOT = '%#H';
    const DATE_FORMAT_FOR_MONTHLY_PLOT = '%B';
    const DAYS_COUNT_FOR_DAILY_BAR_PLOTS = 32;
    const MAX_DAYS_PERIOD = 31;
    const XML_CURRENT_STORE_TIMEZONE = 'oro_dashboard/settings/current_timezone';

    const XML_PATH_MIN_DATE = 'oro_dashboard/general/min_date';
    const XML_PATH_PARTITIONING = 'oro_dashboard/general/partitioning';

    public function getLayoutOptions()
    {
        return array(array("label" => "2 columns", "value" => "2-columns"), array("label" => "3 columns", "value" => "3-columns"));
    }

    /**
     * Convert items array to array for select options
     *
     * return items array
     * array(
     *      $index => array(
     *          'value' => mixed
     *          'label' => mixed
     *      )
     * )
     *
     * @param  string $valueField
     * @param  string $labelField
     * @param  array $additional
     * @param  Mage_Eav_Model_Entity_Collection_Abstract $collection
     * @return array
     */
    public function toOptionArray($valueField = 'id', $labelField = 'name', $additional = array(), $collection)
    {
        $res = array();
        $additional['value'] = $valueField;
        $additional['label'] = $labelField;

        foreach ($collection as $item) {
            foreach ($additional as $code => $field) {
                $data[$code] = $item->getData($field);
            }
            $res[] = $data;
        }

        return $res;
    }

    /**
     * Check if current admin user has permissions for dashboard section
     * @param  string  $section
     * @return boolean
     * @throws Exception
     */
    public function isSectionAllowed($section)
    {
        try {
            $session = Mage::getSingleton('admin/session');
            $resourceLookup = "admin/system/dashboards/{$section}";
            $resourceId = $session->getData('acl')->get($resourceLookup)->getResourceId();
            if (!$session->isAllowed($resourceId)) {
                throw new Exception('');
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if current admin user has manage dashboards permissions
     *
     * @return boolean
     */
    public function canManageDashboards()
    {
        return $this->isSectionAllowed("dashboards_manage");
    }

    /**
     * Get current user's default dashboard id
     *
     * @return int
     */
    public function getDefaultDashboardId()
    {
        $userId = Mage::getSingleton('admin/session')->getUser()->getId();
        $dashboardId = Mage::getModel("oro_dashboard/user")->getDefaultDashboardId($userId);

        return $dashboardId;
    }

    /**
     * Get available metrics
     * @param  boolean   $onlyTitles
     * @return array
     */
    public function getMetrics($onlyTitles = false)
    {
        $metrics = Mage::getConfig()->getNode('metrics')->asArray();
        if ($onlyTitles) {
            return array_keys($metrics);
        }

        return $metrics;
    }

    /**
     * Get metric's class
     *
     * @param  string $metric
     * @return string
     */
    public function getMetricsClass($metric)
    {
        return Mage::getConfig()->getNode('metrics/' . $metric . '/class')->asArray();
    }

    /**
     * Check if metric is a money metric
     *
     * @param  string $metric
     * @return boolean
     */
    public function isMoneyMetric($metric)
    {
        $node = Mage::getConfig()->getNode('metrics/' . $metric . '/is_money');
        if ($node) {
            return true;
        }

        return false;
    }

    /**
     * Get metric's label
     * @param  string $metric
     * @return string
     */
    public function getMetricLabel($metric)
    {
        return Mage::getConfig()->getNode('metrics/' . $metric . '/label')->asArray();
    }

    /**
     * Get metric's avg label
     * @param  string $metric
     * @return string
     */
    public function getMetricLabelAvg($metric)
    {
        return Mage::getConfig()->getNode('metrics/' . $metric . '/label_avg')->asArray();
    }

    /**
     * Get available group by attributes
     * @param  boolean   $onlyCodes
     * @return array
     */
    public function getGroupByAttributes($onlyCodes = false)
    {
        $attributes = Mage::getConfig()->getNode('grouped_by_attributes')->asArray();
        if ($onlyCodes) {
            return array_keys($attributes);
        }
        $codes = array_keys($attributes);
        $attributes = array();
        foreach ($codes as $code) {
            $attributes[] = Mage::getModel('eav/entity_attribute')->loadByCode('customer_address', $code);
        }

        return $attributes;
    }

    /**
     * Get grouped attribute data
     * @param  string        $groupedAttribute
     * @return Varien_Object
     */
    public function getAttributeData($groupedAttribute)
    {
        $data = new Varien_Object();
        if ($groupedAttribute) {
            list($addressType, $attributeCode) = explode("-", $groupedAttribute);
            $attribute = Mage::getModel('eav/entity_attribute')->loadByCode('customer_address', $attributeCode);
            $data->setAttribute($attribute);
            $data->setAddressType($addressType);
        }

        return $data;
    }

    /**
     * Get Locale Date Format
     *
     * @return string
     */
    public function getLocaleFormat()
    {
        return Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    }

    /**
     * Get number format for plot
     * @param  string $metric
     * @return string
     */
    public function getNumberFormat($metric)
    {
        $format = '%d';
        if ($this->isMoneyMetric($metric)) {
            $format = Mage::app()->getLocale()->getJsPriceFormat();
            $format = $format['pattern'];
        }

        return $format;
    }

    /**
     * Get Locale Date Format for JsDate
     *
     * @param  string $type
     * @return string
     */
    public function getLocaleFormatForJsDate($type = "date")
    {
        $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        if ($type === "time") {
            $format = Mage::app()->getLocale()->getTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        }
        $format = str_replace(array("MM", "mm", "m", "dd", "yyyy", "yy", "HH"), array("M", "", "", "d", "y", "y", "H"), $format);
        $format = str_ireplace(array("m", "d", "y"), array("%m", "%d", "%y"), $format);
        $format = str_replace(array("H:", "H.", "h:", "h.", "a"), array("%#H", "%#H", "%#I", "%#I", "%p"), $format);

        return $format;
    }

    /**
     * Get Locale Date Format for JsDate
     *
     * @return string
     */
    public function getLocaleFormatForJqueryDatepicker()
    {
        $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $format = strtolower($format);
        $format = str_ireplace(array("mm", "dd", "yy"), array("m", "d", "y"), $format);

        return $format;
    }

    /**
     * Get day between period from and to dates
     *
     * @param  string $type
     * @param  Zend_Date $dateStart
     * @param  Zend_Date $dateEnd
     * @return int
     */
    public function getPeriodDiff($type = "days", $dateStart = null, $dateEnd = null)
    {
        if (!$dateStart) {
            $dateObjectStart = new Zend_Date($this->getDateFrom(false, true), 'yyyy-MM-dd HH:m:s');
        } else {
            $dateObjectStart = clone $dateStart;
        }
        if (!$dateEnd) {
            $dateObjectEnd = new Zend_Date($this->getDateTo(false, true), 'yyyy-MM-dd HH:m:s');
        } else {
            $dateObjectEnd = clone $dateEnd;
        }
        $value = $dateObjectEnd->sub($dateObjectStart)->toValue() / 60 / 60;
        if ($type == "days") {
            $value = $value / 24;
        }

        return (int)$value;
    }

    /**
     * Check if date period is valid
     *
     * @param  Zend_Date $from
     * @param  Zend_Date $to
     * @return int
     */
    public function isValidPeriod($from, $to)
    {
        $days = $this->getPeriodDiff("days", $from, $to);
        if ($days < 0) {
            return false;
        }
        $minDate = new Zend_Date($this->getPossibleMinDate(), Varien_Date::DATETIME_INTERNAL_FORMAT);
        $days = $this->getPeriodDiff("days", $minDate, $from);
        if ($days < 0) {
            return false;
        }

        return true;
    }

    /**
     * Check if selected range is for hourly plot
     *
     * @return boolean
     */
    public function isHourlyPlot()
    {
        if ($this->getPeriodDiff() > self::DAYS_COUNT_FOR_HOURLY_PLOTS) {
            return false;
        }

        return true;
    }

    /**
     * Get Date Format for Plot
     *
     * @return string
     */
    public function getDateFormatForPlot()
    {
        if ($this->isHourlyPlot()) {
            return $this->getLocaleFormatForJsDate("time");
        }

        return $this->getLocaleFormatForJsDate();
    }

    /**
     * Get Data Type
     *
     * @return string
     */
    public function getAggregationType()
    {
        $type = "hourly"; //hourly plots
        $days = $this->getPeriodDiff();
        if ($days >= self::DAYS_COUNT_FOR_DAILY_PLOTS) {
            $type = 'daily';
        }

        return $type;
    }

    /**
     * Get Plot Type
     *
     * @return string
     */
    public function getPlotType()
    {
        $type = "hourly"; //hourly plots
        $days = $this->getPeriodDiff();
        if ($days >= self::DAYS_COUNT_FOR_MONTHLY_PLOTS) {
            $type = 'monthly';
        } elseif ($days > self::DAYS_COUNT_FOR_HOURLY_PLOTS) {
            $type = 'daily';
        }

        return $type;
    }

    /**
     * Get Plot Type for Bar
     * Show monthly range for big date period
     *
     * @return string
     */
    public function getBarPlotType()
    {
        $type = "hourly"; //hourly plots
        $days = $this->getPeriodDiff();
        if ($days >= self::DAYS_COUNT_FOR_DAILY_BAR_PLOTS) {
            $type = 'monthly';
        } elseif ($days > self::DAYS_COUNT_FOR_HOURLY_PLOTS) {
            $type = 'daily';
        }

        return $type;
    }


    /**
     * Get Date Format for Plot
     *
     * @return string
     */
    public function getTickInterval()
    {
        $format = "1 hour"; //hourly plots
        $days = $this->getPeriodDiff();
        if ($days > self::DAYS_COUNT_FOR_HOURLY_PLOTS) {
            $format = '1 day';
        } elseif ($days >= self::DAYS_COUNT_FOR_DAILY_PLOTS) {
            $format = '1 month';
        } elseif ($days >= self::DAYS_COUNT_FOR_MONTHLY_PLOTS) {
            $format = '1 year';
        }

        return $format;
    }

    /**
     * Get date filter from value
     *
     * @param  boolean $forDb
     * @param  boolean $forPlot
     * @param  boolean $setDelta
     * @return string
     */
    public function getDateFrom($forDb = false, $forPlot = false, $setDelta = false)
    {
        if (Mage::getSingleton('adminhtml/session')->getDashboardDateFrom()) {
            $fromDate = Mage::getSingleton('adminhtml/session')->getDashboardDateFrom();
        } else {
            $fromDate = Mage::app()->getLocale()->date(strtotime('-7 days'), Zend_Date::TIMESTAMP, null, false);
        }
        if ($forDb) {
            if ($this->isHourlyPlot()) {
                $newFromDate = clone $fromDate;
                $newFromDate->subDay(1);
                return Mage::getSingleton("core/date")->gmtDate(null, $newFromDate->toString("yyyy-MM-dd 23:00"));
            }
            return Mage::getSingleton("core/date")->gmtDate(null, $fromDate->toString("yyyy-MM-dd 00:00"));
        }
        if ($forPlot) {
            if ($setDelta) { //delta fix for Bar Plots
                $newFromDate = clone $fromDate;
                $newFromDate->subDay(1);
                return $newFromDate->toString("yyyy-MM-dd 23:00");
            }
            return $fromDate->toString("yyyy-MM-dd 00:00");
        }

        return $fromDate->toString($this->getLocaleFormat());
    }

    /**
     * Get date filter from value
     *
     * @param  boolean $forDb
     * @param  boolean $forPlot
     * @param  boolean $setDelta
     * @return string
     */
    public function getDateTo($forDb = false, $forPlot = false, $setDelta = false)
    {
        if (Mage::getSingleton('adminhtml/session')->getDashboardDateTo()) {
            $toDate = Mage::getSingleton('adminhtml/session')->getDashboardDateTo();
        } else {
            $toDate = Mage::app()->getLocale()->date(strtotime("now"), Zend_Date::TIMESTAMP, null, false);
        }
        if ($forDb) {
            /*if ($this->isHourlyPlot()) {
                return Mage::getSingleton("core/date")->gmtDate(null, $toDate->toString("yyyy-MM-dd 23:00"));
            }
            return $toDate->toString("yyyy-MM-dd 23:00");*/
            return Mage::getSingleton("core/date")->gmtDate(null, $toDate->toString("yyyy-MM-dd 23:00"));
        }
        if ($forPlot) {
            if ($setDelta) {
                $newFromDate = clone $toDate;
                $newFromDate->addDay(1);
                if ($this->isHourlyPlot()) { //delta fix for Bar Plots
                    return $newFromDate->toString("yyyy-MM-dd 0:30");
                } else {
                    return $newFromDate->toString("yyyy-MM-dd 0:0");
                }
            }
            return $toDate->toString("yyyy-MM-dd 23:00");
        }

        return $toDate->toString($this->getLocaleFormat());
    }

    /**
     * Convert date to Zend_Date
     *
     * @param  string    $date
     * @return Zend_Date
     */
    public function processDate($date)
    {
        return Mage::app()->getLocale()->date($date, Zend_Date::DATETIME_SHORT, null, false);
    }

    /**
     * Get locale month for bar plot
     *
     * @param  string    $date
     * @return string
     */
    public function getLocaleMonth($date)
    {
        return Mage::app()->getLocale()->date(strtotime($date), Zend_Date::TIMESTAMP, null, false)->toString("MMMM");
    }

    /**
     * Convert date to Store date
     *
     * @param  string $date
     * @param  boolean $isLocale
     * @return string
     */
    public function getStoreDate($date, $isLocale = false)
    {
        $date = Mage::getSingleton("core/date")->date(null, $date);
        if ($isLocale) {
            $date = $this->getLocaleDate($date);
        }

        return $date;
    }

    /**
     * Get Store Timezone Offset
     *
     * @return int
     */
    public function getStoreTimezoneOffset()
    {
        return Mage::getSingleton("core/date")->getGmtOffset();
    }

    /**
     * Convert date to locale date
     *
     * @param  string $date
     * @return string
     */
    public function getLocaleDate($date)
    {
        if ($this->isHourlyPlot()) {
            return Mage::app()->getLocale()->date($date, Varien_Date::DATETIME_INTERNAL_FORMAT, null, false)
                ->toString(Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
        }

        return Mage::app()->getLocale()->date($date, Varien_Date::DATE_INTERNAL_FORMAT, null, false)
            ->toString(Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
    }

    /**
     * Get locale date period string
     *
     * @param  string $period
     * @return string
     */
    public function getDateRange($period)
    {
        $format = $this->getLocaleFormat();
        $str = '';
        switch ($period) {
            case "today":
                $str = Mage::app()->getLocale()->date(strtotime("now"), Zend_Date::TIMESTAMP, null, false)->toString($format) .
                    "#" . Mage::app()->getLocale()->date(strtotime("now"), Zend_Date::TIMESTAMP, null, false)->toString($format);
                break;
            case "yesterday":
                $str = Mage::app()->getLocale()->date(strtotime('-1 day'), Zend_Date::TIMESTAMP, null, false)->toString($format)
                    . "#" . Mage::app()->getLocale()->date(strtotime('-1 day'), Zend_Date::TIMESTAMP, null, false)->toString($format);
                break;
            case "last week":
                $str = Mage::app()->getLocale()->date(strtotime('-7 days'), Zend_Date::TIMESTAMP, null, false)->toString($format)
                    . "#" . Mage::app()->getLocale()->date(strtotime("now"), Zend_Date::TIMESTAMP, null, false)->toString($format);
                break;
            case "last month":
                $str = Mage::app()->getLocale()->date(strtotime('-1 month'), Zend_Date::TIMESTAMP, null, false)->toString($format) .
                    "#" . Mage::app()->getLocale()->date(strtotime("now"), Zend_Date::TIMESTAMP, null, false)->toString($format);
                break;
        }

        return $str;
    }

    /**
     * Get current store
     *
     * @return int
     */
    public function getStore()
    {
        $store = Mage::app()->getRequest()->getParam("store", null);
        if ($store > 0) {
            return $store;
        }

        return null;
    }

    /**
     * Convert number to locale number
     *
     * @param  string $number
     * @return string
     */
    public function toNumber($number)
    {
        return Zend_Locale_Format::toNumber($number, array("locale" => Mage::app()->getLocale()->getLocaleCode()));
    }

    /**
     * Convert value to money value
     *
     * @param  string $number
     * @return string
     */
    public function toMoney($number)
    {
        return Mage::helper('core')->currency($number, true, false);
    }

    /**
     * Get possible min date for manual aggregation
     *
     * @return string
     */
    public function getPossibleMinDate()
    {
        $currentDate = new Zend_Date();
        $configDate = Mage::getStoreConfig(self::XML_PATH_MIN_DATE);
        if ($configDate) {
            $minDate = new Zend_Date($configDate, Varien_Date::DATETIME_INTERNAL_FORMAT);
        } else {
            $minDate = clone $currentDate;
            $minDate->subYear(2);
            Mage::getConfig()
                ->saveConfig(self::XML_PATH_MIN_DATE, $minDate->toString(Varien_Date::DATE_INTERNAL_FORMAT));
        }

        if ($minDate->compare($currentDate) > 0) {
            $minDate = $currentDate;
        }

        return $minDate->toString(Varien_Date::DATE_INTERNAL_FORMAT);
    }

    /**
     * Get from date for manual aggregation
     *
     * @return string
     */
    public function getDateFromForAggregation()
    {
        $from = $this->getDateFrom(true);
        $minDate = $this->getPossibleMinDate();
        if (strtotime($from) < strtotime($minDate)) {
            $from = $minDate;
        }

        return $from;
    }

    /**
     * Get from date for manual aggregation
     *
     * @return string
     */
    public function getDateToForAggregation()
    {
        $to = new Zend_Date($this->getDateTo(true),'yyyy-MM-dd HH:m:s');
        $to->addHour(1);
        return $to->toString('yyyy-MM-dd HH:m:s');
    }
    
    /**
     * Get Possible Min Date For Jquery Datepicker
     *
     * @return string
     */
    public function getPossibleMinDateForJqueryDatepicker()
    {
        return date('n/j/y', strtotime($this->getPossibleMinDate()));
    }

    /**
     * Check if any analytics data is collected
     *
     * @return boolean
     */
    public function hasAnalyticsDataForPeriod()
    {
        /* @var $resource Oro_Analytics_Model_Resource_DataAggregation */
        $resource   = Mage::getResourceModel("oro_analytics/dataAggregation");

        $from   = $this->getDateFromForAggregation();
        $to     = new Zend_Date($this->getDateTo(true), Varien_Date::DATETIME_INTERNAL_FORMAT);
        $time   = new Zend_Date();
        if ($time->compare($to) === -1) {
            $to = $time->subHour(1);
        }
        $to->setMinute(0)->setSecond(0);

        return $resource->getIfDateAggregated($from) &&
            $resource->getIfDateAggregated($to->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
    }

    /**
     * Check if any analytics data is collected
     *
     * @return boolean
     */
    public function hasAnalyticsData()
    {
        return Mage::getModel("oro_analytics/cronJob")->getCollection()->count() > 0;
    }

    /**
     * Save current timezone config
     *
     */
    public function saveTimezoneConfig()
    {
        Mage::getModel('core/config')->saveConfig(self::XML_CURRENT_STORE_TIMEZONE,
            Mage::getStoreConfig('general/locale/timezone'));
    }

    /**
     * Check timezone change
     *
     */
    public function checkTimezoneChange()
    {
        $savedTimezone = Mage::getStoreConfig('general/locale/timezone');
        $lastTimezone = Mage::getStoreConfig(self::XML_CURRENT_STORE_TIMEZONE);
        if ($this->getAggregationType() === 'daily') {
            if (($savedTimezone !== $lastTimezone) &&
                !Mage::getResourceModel("oro_analytics/dailyAggregation")->checkIfCronComplete()) {
                if (!Mage::getResourceModel("oro_analytics/dailyAggregation")
                    ->getIfDateAgrregated($this->getDateFrom(true), $savedTimezone) ||
                    !Mage::getResourceModel("oro_analytics/dailyAggregation")
                    ->getIfDateAgrregated($this->getDateTo(true), $savedTimezone)) {
                    if (Mage::getStoreConfig(Oro_Analytics_Model_DataAggregation::XML_DOWN_CRON_FINISH_FLAG)) {
                        $canManage = $this->canManageDashboards();
                        Mage::getSingleton('adminhtml/session')->addNotice(
                            $this->__("Store timezone has been changed, it's recommended to" .
                            ($canManage ? "<a target='_blank' href='%s'>" : '') .
                            " run analytics data aggregation" .
                            ($canManage ? "</a>" : ''),
                        Mage::helper('adminhtml')->getUrl("*/userdashboard/dailyAggregationStart")));
                    } else {
                        Mage::getSingleton('adminhtml/session')->addNotice(
                            $this->__('New timezone settings not applied yet to dashboard data. The data aggregation process is in progress.')
                        );
                    }
                }
            }
        }
    }

    /**
     * Get processed records for daily aggregation
     *
     * @return int
     */
    public function getProcessedDailyRecordsCount()
    {
        return Mage::getModel("oro_analytics/cronJobDaily")->getCollection()->count();
    }

    /**
     * Get start date for daily aggregation
     *
     * @return Zend_Date
     */
    public function getStartDbTime()
    {
        return new Zend_Date(Mage::getResourceModel("oro_analytics/dailyAggregation")
            ->getStartDbTime(false),
            'yyyy-MM-dd HH:m:s');
    }

    /**
     * Get config flag is partitioning enabled
     *
     * @return boolean
     */
    public function canUsePartitions()
    {
        return Mage::getStoreConfig(self::XML_PATH_PARTITIONING);
    }

    /**
     * Get is partitioning feature available in MySQL server
     *
     * @return boolean
     */
    public function isPartitionsAvailable()
    {
        return Mage::getResourceModel("oro_analytics/dataAggregation")->isPartitioningAvailable();
    }
}
