<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Analytics
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Analytics_Model_Resource_Shop extends Oro_Analytics_Model_Resource_Base
{
    /**
     * Initialize resource model
     *
     */
    public function _construct()
    {
        $this->_init('oro_analytics/shop', 'id');
    }

    /**
     * get new customers products amount data by period
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
    public function getNewCustomersOrdersAmountGroupData($dateFrom, $dateTo, $groupType, Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $storeId = null, $limit = null, $whereValues = array())
    {
        return $this->getGroupData(
            'new_customers_orders_amount',
            $dateFrom,
            $dateTo,
            $groupType,
            $attribute,
            $storeId,
            $limit,
            $whereValues
        );
    }

    /**
     * get new customers products amount data by period
     *
     * @param  string  $dateFrom
     * @param  string  $dateTo
     * @param  int     $storeId
     * @param  int     $limit
     * @param  boolean $sort
     * @param  array   $whereValues
     * @return array
     */
    public function getNewCustomersOrdersAmountData($dateFrom, $dateTo, $storeId = null, $limit = null, $sort = null, $whereValues = array())
    {
        return $this->getData(
            'new_customers_orders_amount',
            $dateFrom,
            $dateTo,
            $storeId,
            $limit,
            $sort,
            $whereValues
        );
    }

    /**
     * get new customers products amount by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return int
     */
    public function getNewCustomersOrdersAmountCount($dateFrom, $dateTo, $storeId = null)
    {
        $this->_prepareCountSelect('new_customers_orders_amount', $dateFrom, $dateTo, $storeId, self::SUM_AGGREGATOR);
        return $this->_fetchCount();
    }

    /**
     * get avg new customers products amount by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return float
     */
    public function getNewCustomersOrdersAmountAvgCount($dateFrom, $dateTo, $storeId = null)
    {
        return $this->getAvgCount(
            $this->getNewCustomersOrdersAmountCount($dateFrom, $dateTo, $storeId),
            $dateFrom,
            $dateTo
        );
    }

    /**
     * get returned customers products amount data by period
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
    public function getReturnedCustomersOrdersAmountGroupData($dateFrom, $dateTo, $groupType, Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $storeId = null, $limit = null, $whereValues = array())
    {
        return $this->getGroupData(
            'returned_customers_orders_amount',
            $dateFrom,
            $dateTo,
            $groupType,
            $attribute,
            $storeId,
            $limit,
            $whereValues
        );
    }

    /**
     * get returned customers products amount data by period
     *
     * @param  string  $dateFrom
     * @param  string  $dateTo
     * @param  int     $storeId
     * @param  int     $limit
     * @param  boolean $sort
     * @param  array   $whereValues
     * @return array
     */
    public function getReturnedCustomersOrdersAmountData($dateFrom, $dateTo, $storeId = null, $limit = null, $sort = null, $whereValues = array())
    {
        return $this->getData(
            'returned_customers_orders_amount',
            $dateFrom,
            $dateTo,
            $storeId,
            $limit,
            $sort,
            $whereValues
        );
    }

    /**
     * get returned customers products amount by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return int
     */
    public function getReturnedCustomersOrdersAmountCount($dateFrom, $dateTo, $storeId = null)
    {
        $this->_prepareCountSelect('returned_customers_orders_amount', $dateFrom, $dateTo, $storeId, self::SUM_AGGREGATOR);
        return $this->_fetchCount();
    }

    /**
     * get avg returned customers products amount by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return float
     */
    public function getReturnedCustomersOrdersAmountAvgCount($dateFrom, $dateTo, $storeId = null)
    {
        return $this->getAvgCount(
            $this->getReturnedCustomersOrdersAmountCount($dateFrom, $dateTo, $storeId),
            $dateFrom,
            $dateTo
        );
    }

    /**
     * get grouped products in cart amount data by period
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
    public function getCartProductsAmountGroupData($dateFrom, $dateTo, $groupType, Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $storeId = null, $limit = null, $whereValues = array())
    {
        return $this->getGroupData(
            'products_in_cart_amount',
            $dateFrom,
            $dateTo,
            $groupType,
            $attribute,
            $storeId,
            $limit,
            $whereValues
        );
    }

    /**
     * get products in cart amount data by period
     *
     * @param  string  $dateFrom
     * @param  string  $dateTo
     * @param  int     $storeId
     * @param  int     $limit
     * @param  boolean $sort
     * @param  array   $whereValues
     * @return array
     */
    public function getCartProductsAmountData($dateFrom, $dateTo, $storeId = null, $limit = null, $sort = null, $whereValues = array())
    {
        return $this->getData(
            'products_in_cart_amount',
            $dateFrom,
            $dateTo,
            $storeId,
            $limit,
            $sort,
            $whereValues
        );
    }

    /**
     * get products in cart amount by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return int
     */
    public function getCartProductsAmountCount($dateFrom, $dateTo, $storeId = null)
    {
        $this->_prepareCountSelect('products_in_cart_amount', $dateFrom, $dateTo, $storeId, self::SUM_AGGREGATOR);
        return $this->_fetchCount();
    }

    /**
     * get avg products in cart amount by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return float
     */
    public function getCartProductsAmountAvgCount($dateFrom, $dateTo, $storeId = null)
    {
        return $this->getAvgCount(
            $this->getCartProductsAmountCount($dateFrom, $dateTo, $storeId),
            $dateFrom,
            $dateTo
        );
    }

    /**
     * get grouped orders amount data by period
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
    public function getOrdersAmountGroupData($dateFrom, $dateTo, $groupType, Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $storeId = null, $limit = null, $whereValues = array())
    {
        return $this->getGroupData(
            'orders_amount',
            $dateFrom,
            $dateTo,
            $groupType,
            $attribute,
            $storeId,
            $limit,
            $whereValues
        );
    }

    /**
     * get orders amount data by period
     *
     * @param  string  $dateFrom
     * @param  string  $dateTo
     * @param  int     $storeId
     * @param  int     $limit
     * @param  boolean $sort
     * @param  array   $whereValues
     * @return array
     */
    public function getOrdersAmountData($dateFrom, $dateTo, $storeId = null, $limit = null, $sort = null, $whereValues = array())
    {
        return $this->getData(
            'orders_amount',
            $dateFrom,
            $dateTo,
            $storeId,
            $limit,
            $sort,
            $whereValues
        );

    }

    /**
     * get orders amount by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return int
     */
    public function getOrdersAmountCount($dateFrom, $dateTo, $storeId = null)
    {
        $this->_prepareCountSelect('orders_amount', $dateFrom, $dateTo, $storeId, self::SUM_AGGREGATOR);
        return $this->_fetchCount();
    }

    /**
     * get avg orders amount by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return float
     */
    public function getOrdersAmountAvgCount($dateFrom, $dateTo, $storeId = null)
    {
        return $this->getAvgCount(
            $this->getOrdersAmountCount($dateFrom, $dateTo, $storeId),
            $dateFrom,
            $dateTo
        );
    }

    /**
     * get grouped orders data by period
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
    public function getOrdersGroupData($dateFrom, $dateTo, $groupType, Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $storeId = null, $limit = null, $whereValues = array())
    {
        return $this->getGroupData(
            'orders_count',
            $dateFrom,
            $dateTo,
            $groupType,
            $attribute,
            $storeId,
            $limit,
            $whereValues
        );
    }

    /**
     * get orders data by period
     *
     * @param  string  $dateFrom
     * @param  string  $dateTo
     * @param  int     $storeId
     * @param  int     $limit
     * @param  boolean $sort
     * @param  array   $whereValues
     * @return array
     */
    public function getOrdersData($dateFrom, $dateTo, $storeId = null, $limit = null, $sort = null, $whereValues = array())
    {
        return $this->getData(
            'orders_count',
            $dateFrom,
            $dateTo,
            $storeId,
            $limit,
            $sort,
            $whereValues
        );
    }

    /**
     * get orders count by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return int
     */
    public function getOrdersCount($dateFrom, $dateTo, $storeId = null)
    {
        $this->_prepareCountSelect('orders_count', $dateFrom, $dateTo, $storeId, self::SUM_AGGREGATOR);
        return $this->_fetchCount();
    }

    /**
     * get avg orders count by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return float
     */
    public function getOrdersAvgCount($dateFrom, $dateTo, $storeId = null)
    {
        return $this->getAvgCount(
            $this->getOrdersCount($dateFrom, $dateTo, $storeId),
            $dateFrom,
            $dateTo
        );
    }

    /**
     * get grouped checkouts data by period
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
    public function getCheckoutsGroupData($dateFrom, $dateTo, $groupType, Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $storeId = null, $limit = null, $whereValues = array())
    {
        return $this->getGroupData(
            'checkout_count',
            $dateFrom,
            $dateTo,
            $groupType,
            $attribute,
            $storeId,
            $limit,
            $whereValues
        );
    }

    /**
     * get checkouts data by period
     *
     * @param  string  $dateFrom
     * @param  string  $dateTo
     * @param  int     $storeId
     * @param  int     $limit
     * @param  boolean $sort
     * @param  array   $whereValues
     * @return array
     */
    public function getCheckoutsData($dateFrom, $dateTo, $storeId = null, $limit = null, $sort = null, $whereValues = array())
    {
        return $this->getData(
            'checkout_count',
            $dateFrom,
            $dateTo,
            $storeId,
            $limit,
            $sort,
            $whereValues
        );
    }

    /**
     * get checkouts count by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return int
     */
    public function getCheckoutsCount($dateFrom, $dateTo, $storeId = null)
    {
        $this->_prepareCountSelect('checkout_count', $dateFrom, $dateTo, $storeId, self::SUM_AGGREGATOR);
        return $this->_fetchCount();
    }

    /**
     * get avg checkouts count by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return float
     */
    public function getCheckoutsAvgCount($dateFrom, $dateTo, $storeId = null)
    {
        return $this->getAvgCount(
            $this->getCheckoutsCount($dateFrom, $dateTo, $storeId),
            $dateFrom,
            $dateTo
        );
    }

    /**
     * get grouped carts data by period
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
    public function getCartsGroupData($dateFrom, $dateTo, $groupType, Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $storeId = null, $limit = null, $whereValues = array())
    {
        return $this->getGroupData(
            'create_cart_count',
            $dateFrom,
            $dateTo,
            $groupType,
            $attribute,
            $storeId,
            $limit,
            $whereValues
        );
    }

    /**
     * get carts data by period
     *
     * @param  string  $dateFrom
     * @param  string  $dateTo
     * @param  int     $storeId
     * @param  int     $limit
     * @param  boolean $sort
     * @param  array   $whereValues
     * @return array
     */
    public function getCartsData($dateFrom, $dateTo, $storeId = null, $limit = null, $sort = null, $whereValues = array())
    {
        return $this->getData(
            'create_cart_count',
            $dateFrom,
            $dateTo,
            $storeId,
            $limit,
            $sort,
            $whereValues
        );
    }

    /**
     * get carts count by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return int
     */
    public function getCartsCount($dateFrom, $dateTo, $storeId = null)
    {
        $this->_prepareCountSelect('create_cart_count', $dateFrom, $dateTo, $storeId, self::SUM_AGGREGATOR);
        return $this->_fetchCount();
    }

    /**
     * get avg carts count of new customers by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return float
     */
    public function getCartsAvgCount($dateFrom, $dateTo, $storeId = null)
    {
        return $this->getAvgCount(
            $this->getCartsCount($dateFrom, $dateTo, $storeId),
            $dateFrom,
            $dateTo
        );
    }

    /**
     * get grouped orders count of new customers by period
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
    public function getNewCustomersOrdersGroupData($dateFrom, $dateTo, $groupType, Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $storeId = null, $limit = null, $whereValues = array())
    {
        return $this->getGroupData(
            'new_customers_orders_count',
            $dateFrom,
            $dateTo,
            $groupType,
            $attribute,
            $storeId,
            $limit,
            $whereValues
        );
    }

    /**
     * get orders count of new customers by period
     *
     * @param  string  $dateFrom
     * @param  string  $dateTo
     * @param  int     $storeId
     * @param  int     $limit
     * @param  boolean $sort
     * @param  array   $whereValues
     * @return array
     */
    public function getNewCustomersOrdersData($dateFrom, $dateTo, $storeId = null, $limit = null, $sort = null, $whereValues = array())
    {
        return $this->getData(
            'new_customers_orders_count',
            $dateFrom,
            $dateTo,
            $storeId,
            $limit,
            $sort,
            $whereValues
        );
    }

    /**
     * get orders count of new customers by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return int
     */
    public function getNewCustomersOrdersCount($dateFrom, $dateTo, $storeId = null)
    {
        $this->_prepareCountSelect('new_customers_orders_count', $dateFrom, $dateTo, $storeId, self::SUM_AGGREGATOR);
        return $this->_fetchCount();
    }

    /**
     * get avg orders count by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return float
     */
    public function getNewCustomersOrdersAvgCount($dateFrom, $dateTo, $storeId = null)
    {
        return $this->getAvgCount(
            $this->getNewCustomersOrdersCount($dateFrom, $dateTo, $storeId),
            $dateFrom,
            $dateTo
        );
    }

    /**
     * get grouped data of products in cart by period
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
    public function getProductsInCartGroupData($dateFrom, $dateTo, $groupType, Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $storeId = null, $limit = null, $whereValues = array())
    {
        return $this->getGroupData(
            'products_in_cart_count',
            $dateFrom,
            $dateTo,
            $groupType,
            $attribute,
            $storeId,
            $limit,
            $whereValues
        );
    }

    /**
     * get data of products in cart by period
     *
     * @param  string  $dateFrom
     * @param  string  $dateTo
     * @param  int     $storeId
     * @param  int     $limit
     * @param  boolean $sort
     * @param  array   $whereValues
     * @return array
     */
    public function getProductsInCartData($dateFrom, $dateTo, $storeId = null, $limit = null, $sort = null, $whereValues = array())
    {
        return $this->getData(
            'products_in_cart_count',
            $dateFrom,
            $dateTo,
            $storeId,
            $limit,
            $sort,
            $whereValues
        );
    }

    /**
     * get count of products in cart by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return int
     */
    public function getProductsInCartCount($dateFrom, $dateTo, $storeId = null)
    {
        $this->_prepareCountSelect('products_in_cart_count', $dateFrom, $dateTo, $storeId, self::SUM_AGGREGATOR);
        return $this->_fetchCount();
    }

    /**
     * get avg products in cart count by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return float
     */
    public function getProductsInCartAvgCount($dateFrom, $dateTo, $storeId = null)
    {
        return $this->getAvgCount(
            $this->getProductsInCartCount($dateFrom, $dateTo, $storeId),
            $dateFrom,
            $dateTo
        );
    }

    /**
     * get grouped data of wishlist by period
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
    public function getWishlistGroupData($dateFrom, $dateTo, $groupType, Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $storeId = null, $limit = null, $whereValues = array())
    {
        return $this->getGroupData(
            'wishlist_products_count',
            $dateFrom,
            $dateTo,
            $groupType,
            $attribute,
            $storeId,
            $limit,
            $whereValues
        );
    }

    /**
     * get data of wishlist by period
     *
     * @param  string  $dateFrom
     * @param  string  $dateTo
     * @param  int     $storeId
     * @param  int     $limit
     * @param  boolean $sort
     * @param  array   $whereValues
     * @return array
     */
    public function getWishlistData($dateFrom, $dateTo, $storeId = null, $limit = null, $sort = null, $whereValues = array())
    {
        return $this->getData(
            'wishlist_products_count',
            $dateFrom,
            $dateTo,
            $storeId,
            $limit,
            $sort,
            $whereValues
        );
    }

    /**
     * get count ofwithlist by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return int
     */
    public function getWishlistCount($dateFrom, $dateTo, $storeId = null)
    {
        $this->_prepareCountSelect('wishlist_products_count', $dateFrom, $dateTo, $storeId, self::SUM_AGGREGATOR);
        return $this->_fetchCount();
    }

    /**
     * get avg wishlist count by period
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $storeId
     * @return float
     */
    public function getWishlistAvgCount($dateFrom, $dateTo, $storeId = null)
    {
        return $this->getAvgCount(
            $this->getWishlistCount($dateFrom, $dateTo, $storeId),
            $dateFrom,
            $dateTo
        );
    }

    /**
     * get grouped data by period
     *
     * @param  string                                   $fieldName
     * @param  string                                   $dateFrom
     * @param  string                                   $dateTo
     * @param  string                                   $groupType
     * @param  Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param  int                                      $storeId
     * @param  int                                      $limit
     * @param  array                                    $whereValues
     * @return array
     */
    private function getGroupData($fieldName, $dateFrom, $dateTo, $groupType, Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $storeId = null, $limit = null, $whereValues = array())
    {
        $customerAddressAttribute = $this->getCustomerAddressAttribute($groupType);
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from(
            array('analytics_shop' => $this->getPeriodTable('oro_analytics/shop')),
            'addr.value AS group_value, SUM(analytics_shop.' . $fieldName . ') as count'
        )
            ->join(
            array('analytics' => $this->getPeriodTable('oro_analytics/data')),
            'analytics.id = analytics_shop.analytics_id',
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
     * Returns data by period
     *
     * @param  string  $fieldName
     * @param  string  $dateFrom
     * @param  string  $dateTo
     * @param  int     $storeId
     * @param  int     $limit
     * @param  boolean $sort
     * @param  array   $whereValues
     * @return array
     */
    public function getData($fieldName, $dateFrom, $dateTo, $storeId = null, $limit = null, $sort = null, $whereValues = array())
    {
        $this->_prepareDataSelect($fieldName, $dateFrom, $dateTo, $storeId, $limit, $sort, $whereValues, self::SUM_AGGREGATOR);

        return $this->_fetchData();
    }
}
