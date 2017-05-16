<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Analytics
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

abstract class Oro_Analytics_Model_Resource_Base extends Mage_Reports_Model_Resource_Report_Abstract
{

    const COUNT_AGGREGATOR = 'COUNT';
    const SUM_AGGREGATOR = 'SUM';


    /**
     * @var string
     */
    protected $_plotType;

    /**
     * @var string
     */
    protected $_aggregationType;

    /**
     * @var Zend_Db_Select
     */
    protected $_select;

    /**
     * @var array
     */
    protected $_bind;

    /**
     * return table name with period postfix
     *
     * @param string $tableName
     * @param string $postfix
     * @return string
     */
    protected function getPeriodTable($tableName, $postfix = '_daily')
    {
        $tableName = $this->getTable($tableName);
        if (($this->getAggregationType() === "daily")) {
            $tableName .= $postfix;
        }

        return $tableName;
    }

    /**
     * format data for request
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @return array
     */
    protected function getGroupPeriod($dateFrom, $dateTo)
    {
        $adapter = $this->_getWriteAdapter();
        $periodExpr = $adapter->getDatePartSql(
            $this->getStoreTZOffsetQuery(
                array('analytics' => $this->getTable('oro_analytics/data')),
                'analytics.period', $dateFrom, $dateTo
            )
        );
        $groupByPeriod = $periodExpr->__toString();
        if ($this->getPlotType() === "monthly") {
            $dateGroup = 'CONCAT_WS("-", EXTRACT(YEAR FROM analytics.period), EXTRACT(MONTH FROM analytics.period), "01")';
            $groupBy = 'EXTRACT(YEAR_MONTH FROM period_tz)';
        } elseif ($this->getPlotType() === "daily") {
            $dateGroup = $groupByPeriod;
            $groupBy = 'EXTRACT(YEAR_MONTH FROM ' . $groupByPeriod . '), EXTRACT(DAY FROM ' . $groupByPeriod . ')';
        } else {
            $dateGroup = 'analytics.period + INTERVAL 1 HOUR';
            $groupBy = 'analytics.period';
        }

        return array(
            'dateGroup' => $dateGroup,
            'groupBy' => $groupBy
        );
    }

    /**
     * Set Plot Type
     *
     * @param  string $plotType
     */
    public function setPlotType($plotType){
        $this->_plotType = $plotType;

        return $this;
    }

    /**
     * Get Plot Type
     *
     * @return string
     */
    protected function getPlotType(){
        return $this->_plotType;
    }

    /**
     * Set Aggregation Type
     *
     * @param  string $aggregationType
     */
    public function setAggregationType($aggregationType){
        $this->_aggregationType = $aggregationType;

        return $this;
    }

    /**
     * Get Aggregation Type
     *
     * @return string
     */
    protected function getAggregationType(){
        return $this->_aggregationType;
    }

    /**
     * get avg count
     *
     * @param  int    $count
     * @param  string $dateFrom
     * @param  string $dateTo
     * @return float
     */
    protected function getAvgCount($count, $dateFrom, $dateTo)
    {
        $dateObjectStart = new Zend_Date($dateFrom, 'yyyy-MM-dd HH:m:s');
        $dateObjectEnd = new Zend_Date($dateTo, 'yyyy-MM-dd HH:m:s');
        $dateObjectEnd->addHour(1);
        $diff = $dateObjectEnd->sub($dateObjectStart)->toValue();

        $days = (int)($diff / 60 / 60 / 24);
        $hours = (int)($diff / 60 / 60);
        if ($days > 0) {
            if ($days == 1) {
                return $count / 24;
            }

            return $count / $days;
        } elseif ($hours > 0) {
            return $count / $hours;
        }

        return $count;
    }

    /**
     * @param  string $attributeType
     * @return mixed
     */
    protected function getCustomerAddressAttribute($attributeType)
    {
        if ($attributeType == 'billing') {
            return Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'default_billing');
        } else {
            return Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'default_shipping');
        }
    }

    /**
     * Check for record and
     * create new one if where is no record this data
     *
     * @param  string                   $modelName
     * @param  array                    $filterArray
     * @param  array                    $dataToStore
     * @param  Zend_Date                $checkPartitionalDate
     * @return Mage_Core_Model_Abstract
     */
    public function getRecordFromDb($modelName, $filterArray, $dataToStore, Zend_Date $checkPartitionalDate = null)
    {
        $modelRecord = Mage::getModel($modelName)
            ->getCollection();
        foreach ($filterArray as $filter) {
            $modelRecord->addFieldToFilter($filter['name'], $filter['filter']);
        }
        if ($modelRecord->count()) {
            return $modelRecord->getFirstItem();
        } else {
            if ($checkPartitionalDate) {
                $this->checkDataPartition($checkPartitionalDate);
            }
            $modelRecord = Mage::getModel($modelName);
            $modelRecord->addData($dataToStore)
                ->save();

            return $modelRecord;
        }
    }

    /**
     * check for partition of main analytics data table
     *
     * @param Zend_Date $currentDateTime
     */
    public function checkDataPartition(Zend_Date $currentDateTime)
    {
        if (Mage::helper('oro_dashboard')->canUsePartitions() && $this->isPartitioningAvailable()) {
            $writeAdapter = $this->_getWriteAdapter();
            $readAdapter = $this->_getReadAdapter();

            $select = $readAdapter->select()
                ->from($this->getTable('oro_analytics/date_partition'), array(
                    'max_date_time' => 'MAX(date_time)',
                    'min_date_time' => 'MIN(date_time)'
                ));
            $result = $readAdapter->fetchRow($select);
            if ($result && $result['max_date_time']) {
                $maxDateObject = new Zend_Date($result['max_date_time'], 'yyyy-MM-dd HH:m:s');
                $minDateObject = new Zend_Date($result['min_date_time'], 'yyyy-MM-dd HH:m:s');
            } else {
                $maxDateObject = new Zend_Date($currentDateTime->get('y-MM-01 00:00:00'));
                $query = "ALTER TABLE `{$this->getTable('oro_analytics/data')}`"
                    . " PARTITION BY RANGE(TO_DAYS(period)) ("
                    . " PARTITION p_{$maxDateObject->get('y_MM_dd')}"
                    . " VALUES LESS THAN(TO_DAYS('{$maxDateObject->get('y-MM-dd 00:00:00')}'))"
                    . ")";
                $writeAdapter->exec($query);
                $writeAdapter->insert(
                    $this->getTable('oro_analytics/date_partition'),
                    array('date_time' => $maxDateObject->get('y-MM-dd 00:00:00'))
                );
                $minDateObject = clone $maxDateObject;
            }

            $currentDateSub = clone $currentDateTime;
            $datesDiff = $currentDateSub->sub($maxDateObject)->toValue();
            if ($datesDiff >= 0) {
                $monthCount = (int)($datesDiff / 60 / 60 / 24 / 31);
                for ($i = 0; $i <= $monthCount; $i++) {
                    $maxDateObject->add(
                        Oro_Analytics_Model_DataAggregation::PARTITION_INCREMENT_PERIOD,
                        Oro_Analytics_Model_DataAggregation::PARTITION_INCREMENT_PART
                    );
                    $writeAdapter->insert(
                        $this->getTable('oro_analytics/date_partition'),
                        array('date_time' => $maxDateObject->get('y-MM-dd 00:00:00'))
                    );
                    $query = "ALTER TABLE `{$this->getTable('oro_analytics/data')}`"
                        . " ADD PARTITION ("
                        . " PARTITION p_{$maxDateObject->get('y_MM_dd')}"
                        . " VALUES LESS THAN(TO_DAYS('{$maxDateObject->get('y-MM-dd 00:00:00')}'))"
                        . ")";
                    $writeAdapter->exec($query);
                }
            }

            $minDateObjectSub = clone $minDateObject;
            $datesDiff = $minDateObjectSub->sub($currentDateTime);
            $measure = new Zend_Measure_Time($datesDiff->toValue(), Zend_Measure_Time::SECOND);
            $measure->convertTo(Zend_Measure_Time::MONTH);
            $monthCount = $measure->getValue();
            if ($monthCount >= 1) {
                for ($i = 1; $i < $monthCount; $i++) {
                    $minDateObjectBase = clone $minDateObject;
                    $minDateObject->sub(
                        Oro_Analytics_Model_DataAggregation::PARTITION_INCREMENT_PERIOD,
                        Oro_Analytics_Model_DataAggregation::PARTITION_INCREMENT_PART
                    );
                    $writeAdapter->insert(
                        $this->getTable('oro_analytics/date_partition'),
                        array('date_time' => $minDateObject->get('y-MM-dd 00:00:00'))
                    );
                    $query = "ALTER TABLE `{$this->getTable('oro_analytics/data')}`"
                        . " REORGANIZE PARTITION p_{$minDateObjectBase->get('y_MM_dd')} INTO ("
                        . " PARTITION p_{$minDateObject->get('y_MM_dd')}"
                        . " VALUES LESS THAN(TO_DAYS('{$minDateObject->get('y-MM-dd 00:00:00')}')),"
                        . " PARTITION p_{$minDateObjectBase->get('y_MM_dd')}"
                        . " VALUES LESS THAN(TO_DAYS('{$minDateObjectBase->get('y-MM-dd 00:00:00')}'))"
                        . ")";
                    $writeAdapter->exec($query);
                }
            }
        }
    }

    /**
     * check for partition of main daily analytics data table
     *
     * @param Zend_Date $currentDateTime
     */
    public function checkDailyDataPartition(Zend_Date $currentDateTime)
    {
        if (Mage::helper('oro_dashboard')->canUsePartitions() && $this->isPartitioningAvailable()) {
            $writeAdapter = $this->_getWriteAdapter();
            $readAdapter = $this->_getReadAdapter();

            $select = $readAdapter->select()
                ->from($this->getTable('oro_analytics/date_partition_daily'), array('max_date_time' => 'MAX(date_time)'));
            $maxDateTime = $readAdapter->fetchOne($select);
            if($maxDateTime) {
                $maxDateObject = new Zend_Date($maxDateTime);
            } else {
                $maxDate = $currentDateTime->get('y');
                $query = "ALTER TABLE `{$this->getTable('oro_analytics/data_daily')}`"
                    . " PARTITION BY RANGE(TO_DAYS(period)) ("
                    . " PARTITION p_{$maxDate} VALUES LESS THAN(TO_DAYS('{$maxDate}-12-31 23:59:59'))"
                    . ")";
                $writeAdapter->exec($query);
                $writeAdapter->insert(
                    $this->getTable('oro_analytics/date_partition_daily'),
                    array('date_time' => $maxDate . '-12-31 23:59:59')
                );
                $maxDateObject = new Zend_Date($maxDate. '-12-31 23:59:59');
            }

            $years = $currentDateTime->get('y') - $maxDateObject->get('y');
            if ($years > 0) {
                for ($i = 1; $i <= $years; $i++) {
                    $maxDateObject->add(
                        Oro_Analytics_Model_DailyAggregation::PARTITION_INCREMENT_PERIOD,
                        Oro_Analytics_Model_DailyAggregation::PARTITION_INCREMENT_PART
                    );
                    $writeAdapter->insert(
                        $this->getTable('oro_analytics/date_partition_daily'),
                        array('date_time' => $maxDateObject->get('y-MM-dd 23:59:59'))
                    );
                    $query = "ALTER TABLE `{$this->getTable('oro_analytics/data_daily')}`"
                        . " ADD PARTITION ("
                        . " PARTITION p_{$maxDateObject->get('y')}"
                        . " VALUES LESS THAN(TO_DAYS('{$maxDateObject->get('y-MM-dd 23:59:59')}'))"
                        . ")";
                    $writeAdapter->exec($query);
                }
            }
        }
    }

    /**
     * Get aggregation flag state
     *
     * @return string
     */
    public function checkIfAggregationRun()
    {
        return $this->_getReadAdapter()->select()
            ->from(
                array('flag' => $this->getTable("core/flag")),
                array('flag.flag_data')
            )
            ->where('flag.flag_code = "oro_analytics_aggregation"')
            ->query()->fetchColumn();
    }

    /**
     * Set aggregation flag state
     *
     * @param bool $state
     */
    public function setAggregationState($state = false)
    {
        $flagId = $this->_getReadAdapter()->select()
            ->from(
                array('flag' => $this->getTable("core/flag")),
                array('flag.flag_id')
            )
            ->where('flag.flag_code = "oro_analytics_aggregation"')
            ->query()->fetchColumn();

        if ((int)$flagId) {
            $this->_getWriteAdapter()->update(
                $this->getTable('core/flag'),
                array('flag_data' => (int)$state),
                'flag_id = ' . $flagId
            );
        } else {
            $this->_getWriteAdapter()->insert(
                $this->getTable('core/flag'),
                array(
                     'flag_data' => (int)$state,
                     'flag_code' => 'oro_analytics_aggregation'
                )
            );
        }
    }

    /**
     * Get is partitioning feature available in MySQL server
     *
     * @return boolean
     */
    public function isPartitioningAvailable()
    {
        $adapter = $this->_getReadAdapter();
        // Check have_partitioning variable value in MySQL 5.1.6 - 5.6.1
        $data = $adapter->fetchRow("SHOW VARIABLES LIKE 'have_partitioning'");
        if ($data && !empty($data['Value'])) {
            if (strtolower($data['Value']) == 'yes') {
                return true;
            } else {
                return false;
            }
        }

        // Check value of have_partition_engine variable for MySQL less than 5.1.6
        $data = $adapter->fetchRow("SHOW VARIABLES LIKE 'have_partition_engine'");
        if ($data && !empty($data['Value'])) {
            if (strtolower($data['Value']) == 'yes') {
                return true;
            } else {
                return false;
            }
        }

        // Check for MySQL 5.6.1 and greater. The have_partitioning variable is deprecated, and removed in MySQL 5.6.1.
        foreach ($adapter->fetchAll('SHOW PLUGINS') as $plugin) {
            if (strtolower($plugin['Name']) == 'partition' && strtolower($plugin['Status']) == 'active') {
                return true;
            }
        }

        return false;
    }

    /**
     * Prepares data by period select
     *
     * @param  string $fieldName
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int $storeId
     * @param  int $limit
     * @param  boolean $sort
     * @param  array $whereValues
     * @param  string $aggregator
     * @return array
     */
    protected function _prepareDataSelect($fieldName, $dateFrom, $dateTo, $storeId = null, $limit = null, $sort = null, $whereValues = array(), $aggregator = self::SUM_AGGREGATOR)
    {
        $adapter = $this->_getReadAdapter();
        $dateFormat = $this->getGroupPeriod($dateFrom, $dateTo);

        $timezone = sprintf('%d:00', Mage::app()->getLocale()->date()->get(Zend_Date::TIMEZONE_SECS) / 3600);

        $select = $adapter->select()
            ->from(
                array('analytics_metric' => $this->getPeriodTable($this->_mainTable)),
                array(
                    'period_grouped' => new Zend_Db_Expr($dateFormat['dateGroup']),
                    'count' => new Zend_Db_Expr("$aggregator($fieldName)"),
                    'period_tz' => new Zend_Db_Expr("CONVERT_TZ(`analytics`.`period`,'+0:00','{$timezone}')"),
                )
            )
            ->join(
                array('analytics' => $this->getPeriodTable('oro_analytics/data')),
                'analytics.id = analytics_metric.analytics_id',
                array()
            )
            ->where('analytics.period > :dateFrom')
            ->where('analytics.period <= :dateTo');

        $binds = array(
            ":dateFrom" => $dateFrom,
            ":dateTo" => $dateTo
        );

        if ($storeId !== null) {
            $select->where('analytics.store_id = :storeId');
            $binds[':storeId'] = $storeId;
        }
        if ($whereValues) {
            $values = implode('|',$whereValues);
            $select->having("period_grouped REGEXP :vals");
            $binds[':vals'] = $values;
        }
        if ($limit !== null) {
            $select->limit($limit);
        }
        if ($sort) {
            $select->order('count DESC');
        }
        $select->group($dateFormat['groupBy']);

        $this->_select = $select;
        $this->_bind = $binds;

        return $this;
    }

    /**
     * Prepares count by period select
     *
     * @param  string  $fieldName
     * @param  string  $dateFrom
     * @param  string  $dateTo
     * @param  int     $storeId
     * @param  string  $aggregator
     * @return $this
     */
    protected function _prepareCountSelect($fieldName, $dateFrom, $dateTo, $storeId = null, $aggregator = self::SUM_AGGREGATOR)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from(
                array('analytics' => $this->getPeriodTable('oro_analytics/data')),
                "$aggregator($fieldName) as count"
            )
            ->join(
                array('analytics_metric' => $this->getPeriodTable($this->_mainTable)),
                'analytics_metric.analytics_id = analytics.id',
                array()
            )
            ->where('analytics.period > :dateFrom')
            ->where('analytics.period <= :dateTo');

        $binds = array(
            ":dateFrom" => $dateFrom,
            ":dateTo" => $dateTo
        );

        if ($storeId !== null) {
            $select->where('analytics.store_id = :storeId');
            $binds[':storeId'] = $storeId;
        }

        $this->_select = $select;
        $this->_bind = $binds;

        return $this;
    }

    /**
     * Fetches data from DB
     *
     * @return array
     */
    protected function _fetchData()
    {
        return $this->_getReadAdapter()->fetchAll($this->_select, $this->_bind);
    }

    /**
     * Fetches count from DB
     *
     * @return int
     */
    protected function _fetchCount()
    {
        return $this->_getReadAdapter()->fetchOne($this->_select, $this->_bind);
    }
}
