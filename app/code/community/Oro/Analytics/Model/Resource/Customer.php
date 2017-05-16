<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Analytics
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Analytics_Model_Resource_Customer extends Oro_Analytics_Model_Resource_Base
{
    /**
     * Initialize resource model
     *
     */
    public function _construct()
    {
        $this->_init('oro_analytics/customer', 'id');
    }

    /**
     * get grouped data of new customers by period
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
    public function getNewCustomersGroupData($dateFrom, $dateTo, $groupType, Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $storeId = null, $limit = null, $whereValues = array())
    {
        $customerAddressAttribute = $this->getCustomerAddressAttribute($groupType);
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from(
                array('customer' => $this->getPeriodTable('oro_analytics/customer')),
                'addr.value AS group_value, COUNT(*) as count'
            )
            ->join(
                array('analytics' => $this->getPeriodTable('oro_analytics/data')),
                'analytics.id = customer.analytics_id',
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
            ->where('customer.is_new_customer = true')
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
     * get data of new customers by period
     *
     * @param  string  $dateFrom
     * @param  string  $dateTo
     * @param  int     $storeId
     * @param  int     $limit
     * @param  boolean $sort
     * @param  array   $whereValues
     * @return array
     */
    public function getNewCustomersData($dateFrom, $dateTo, $storeId = null, $limit = null, $sort = null, $whereValues = array())
    {
        $this->_prepareDataSelect('*', $dateFrom, $dateTo, $storeId, $limit, $sort, $whereValues, self::COUNT_AGGREGATOR);

        $this->_select->where('is_new_customer = 1');

        return $this->_fetchData();
    }
    
    /**
     * get count of new customers by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return int
     */
    public function getNewCustomersCount($dateFrom, $dateTo, $storeId = null)
    {
        $this->_prepareCountSelect('*', $dateFrom, $dateTo, $storeId, self::COUNT_AGGREGATOR);

        $this->_select->where('is_new_customer = 1');

        return $this->_fetchCount();
    }

    /**
     * get avg count of new customers by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return float
     */
    public function getNewCustomersAvgCount($dateFrom, $dateTo, $storeId = null)
    {
        return $this->getAvgCount(
            $this->getNewCustomersCount($dateFrom, $dateTo, $storeId),
            $dateFrom,
            $dateTo
        );
    }

    /**
     * get grouped new visitors by period
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
    public function getNewVisitorsGroupData($dateFrom, $dateTo, $groupType, Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $storeId = null, $limit = null, $whereValues = array())
    {
        $customerAddressAttribute = $this->getCustomerAddressAttribute($groupType);

        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from(
            array('customer' => $this->getPeriodTable('oro_analytics/customer')),
            'addr.value AS group_value, COUNT(*) as count'
        )
            ->join(
            array('analytics' => $this->getPeriodTable('oro_analytics/data')),
            'analytics.id = customer.analytics_id',
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
            ->where('(customer.is_new_customer = 1 OR analytics.customer_id is NULL)')
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
     * get new visitors by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @param  int    $limit
     * @return array
     */
    public function getNewVisitorsData($dateFrom, $dateTo, $storeId = null, $limit = null, $sort = null, $whereValues = array())
    {
        $this->_prepareDataSelect('*', $dateFrom, $dateTo, $storeId, $limit, $sort, $whereValues, self::COUNT_AGGREGATOR);

        $this->_select->where('(is_new_customer = 1 OR analytics.customer_id is NULL)');

        return $this->_fetchData();
    }

    /**
     * get count of new visitors by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return int
     */
    public function getNewVisitorsCount($dateFrom, $dateTo, $storeId = null)
    {
        $this->_prepareCountSelect('*', $dateFrom, $dateTo, $storeId, self::COUNT_AGGREGATOR);

        $this->_select->where('(is_new_customer = 1 OR analytics.customer_id is NULL)');

        return $this->_fetchCount();
    }

    /**
     * get avg count of new visitors by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return float
     */
    public function getNewVisitorsAvgCount($dateFrom, $dateTo, $storeId = null)
    {
        return $this->getAvgCount(
            $this->getNewVisitorsCount($dateFrom, $dateTo, $storeId),
            $dateFrom,
            $dateTo
        );
    }

    /**
     * get grouped Customers by period
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
    public function getCustomersGroupData($dateFrom, $dateTo, $groupType, Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $storeId = null, $limit = null, $whereValues = array())
    {
        $customerAddressAttribute = $this->getCustomerAddressAttribute($groupType);

        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from(
            array('customer' => $this->getPeriodTable('oro_analytics/customer')),
            'addr.value AS group_value, COUNT(*) as count'
        )
            ->join(
            array('analytics' => $this->getPeriodTable('oro_analytics/data')),
            'analytics.id = customer.analytics_id',
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
     * get Customers by period
     *
     * @param  string  $dateFrom
     * @param  string  $dateTo
     * @param  int     $storeId
     * @param  int     $limit
     * @param  boolean $sort
     * @param  array   $whereValues
     * @return array
     */
    public function getCustomersData($dateFrom, $dateTo, $storeId = null, $limit = null, $sort = null, $whereValues = array())
    {
        $this->_prepareDataSelect('*', $dateFrom, $dateTo, $storeId, $limit, $sort, $whereValues, self::COUNT_AGGREGATOR);

        return $this->_fetchData();
    }

    /**
     * get count of Customers by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return int
     */
    public function getCustomersCount($dateFrom, $dateTo, $storeId = null)
    {
        $this->_prepareCountSelect('*', $dateFrom, $dateTo, $storeId, self::COUNT_AGGREGATOR);

        return $this->_fetchCount();
    }

    /**
     * get avg count of Customers by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return float
     */
    public function getCustomersAvgCount($dateFrom, $dateTo, $storeId = null)
    {
        return $this->getAvgCount(
            $this->getCustomersCount($dateFrom, $dateTo, $storeId),
            $dateFrom,
            $dateTo
        );
    }

    /**
     * get grouped logins data by period
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
    public function getLoginsGroupData($dateFrom, $dateTo, $groupType, Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $storeId = null, $limit = null, $whereValues = array())
    {
        $customerAddressAttribute = $this->getCustomerAddressAttribute($groupType);

        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from(
            array('customer' => $this->getPeriodTable('oro_analytics/customer')),
            'addr.value AS group_value, SUM(customer.logins_count) as count'
        )
            ->join(
            array('analytics' => $this->getPeriodTable('oro_analytics/data')),
            'analytics.id = customer.analytics_id',
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
     * get logins data by period
     *
     * @param  string  $dateFrom
     * @param  string  $dateTo
     * @param  int     $storeId
     * @param  int     $limit
     * @param  boolean $sort
     * @param  array   $whereValues
     * @return array
     */
    public function getLoginsData($dateFrom, $dateTo, $storeId = null, $limit = null, $sort = null, $whereValues = array())
    {
        $this->_prepareDataSelect('logins_count', $dateFrom, $dateTo, $storeId, $limit, $sort, $whereValues, self::SUM_AGGREGATOR);

        return $this->_fetchData();
    }

    /**
     * get logins count by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return int
     */
    public function getLoginsCount($dateFrom, $dateTo, $storeId = null)
    {
        $this->_prepareCountSelect('logins_count', $dateFrom, $dateTo, $storeId, self::SUM_AGGREGATOR);

        return $this->_fetchCount();
    }

    /**
     * get avg logins by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return float
     */
    public function getLoginsAvgCount($dateFrom, $dateTo, $storeId = null)
    {
        return $this->getAvgCount(
            $this->getLoginsCount($dateFrom, $dateTo, $storeId),
            $dateFrom,
            $dateTo
        );
    }
}
