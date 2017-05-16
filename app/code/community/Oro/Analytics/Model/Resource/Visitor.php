<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Analytics
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Analytics_Model_Resource_Visitor extends Oro_Analytics_Model_Resource_Base
{
    /**
     * Initialize resource model
     *
     */
    public function _construct()
    {
        $this->_init('oro_analytics/visitor', 'id');
    }

    /**
     * get visitors by period
     *
     * @param  string                                   $dateFrom
     * @param  string                                   $dateTo
     * @param  string                                   $groupType
     * @param  Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param  int                                      $storeId
     * @param  int                                      $limit
     * @param  array                                    $whereValues
     * @return array
     */
    public function getVisitorsGroupData($dateFrom, $dateTo, $groupType, Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $storeId = null, $limit = null, $whereValues = array())
    {
        $customerAddressAttribute = $this->getCustomerAddressAttribute($groupType);
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from(
            array('analytics_visitors' => $this->getPeriodTable('oro_analytics/visitor')),
            'addr.value AS group_value, SUM(analytics_visitors.visit_count) as count'
        )
            ->join(
            array('analytics' => $this->getPeriodTable('oro_analytics/data')),
            'analytics.id = analytics_visitors.analytics_id',
            array()
        )
            ->join(
            array('int_val' =>  $customerAddressAttribute->getBackendTable()),
            'int_val.entity_id = analytics.customer_id AND int_val.attribute_id = :groupTypeId',
            array()
        )
            ->join(
            array('addr' =>  $attribute->getBackendTable()),
            'addr.entity_id = int_val.value AND addr.attribute_id = :groupId',
            array()
        )
            ->where('analytics.period > :dateFrom')
            ->where('analytics.period <= :dateTo');

        $binds = array(
            ":dateFrom" => $dateFrom,
            ":dateTo" => $dateTo,
            ":groupTypeId" => $customerAddressAttribute->getId(),
            ":groupId" => $attribute->getId()
        );

        if ($storeId !== null) {
            $select->where('analytics.store_id = :storeId');
            $binds[':storeId'] = $storeId;
        }
        if ($whereValues) {
            $values = implode(',',$whereValues);
            $select->where("addr.value IN ($values)");
        }
        if ($limit !== null) {
            $select->limit($limit);
        }
        $select->group('group_value');
        $select->order('count DESC');

        return $adapter->fetchAll($select, $binds);
    }

    /**
     * get visitors by period
     *
     * @param  string  $dateFrom
     * @param  string  $dateTo
     * @param  int     $storeId
     * @param  int     $limit
     * @param  boolean $sort
     * @param  array   $whereValues
     * @return array
     */
    public function getVisitorsData($dateFrom, $dateTo, $storeId = null, $limit = null, $sort = null, $whereValues = array())
    {
        $this->_prepareDataSelect('visit_count', $dateFrom, $dateTo, $storeId, $limit, $sort, $whereValues, self::SUM_AGGREGATOR);

        return $this->_fetchData();
    }

    /**
     * get visitors by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return int
     */
    public function getVisitorsCount($dateFrom, $dateTo, $storeId = null)
    {
        $this->_prepareCountSelect('visit_count', $dateFrom, $dateTo, $storeId, self::SUM_AGGREGATOR);
        return $this->_fetchCount();
    }

    /**
     * get visitors count by period
     *
     * @param  string    $dateFrom
     * @param  string    $dateTo
     * @param  int       $storeId
     * @return float|int
     */
    public function getVisitorsAvgCount($dateFrom, $dateTo, $storeId = null)
    {
        return $this->getAvgCount(
            $this->getVisitorsCount($dateFrom, $dateTo, $storeId),
            $dateFrom,
            $dateTo
        );
    }
}
