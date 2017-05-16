<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Analytics
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Analytics_Model_Resource_DataAggregation extends Oro_Analytics_Model_Resource_Base
{
    /**
     * Initialize resource model
     *
     */
    public function _construct()
    {
        $this->_init('oro_analytics/dataAggregation', 'id');
    }

    /**
     * aggregated
     *
     * @param string $periodStart
     * @return string
     */
    public function getIfDateAggregated($periodStart)
    {
        return $this->_getReadAdapter()->select()
            ->from($this->getTable('oro_analytics/cron_job'), 'id')
            ->where('date_time = ?', $periodStart)
            ->query()
            ->fetchColumn();
    }

    /**
     * Get aggregated columns count
     *
     * @param string $periodStart
     * @param string $periodEnd
     * @return string
     */
    public function getAggregatedColumnsCount($periodStart, $periodEnd)
    {
        return $this->_getReadAdapter()->select()
            ->from($this->getTable('oro_analytics/cron_job'), 'COUNT(*)')
            ->where('date_time >= ?', $periodStart)
            ->where('date_time <= ?', $periodEnd)
            ->query()
            ->fetchColumn();
    }

    /**
     * get info about visitors
     *
     * @param Zend_Date $periodStart
     * @param Zend_Date $periodEnd
     *
     * @return array
     */
    public function getVisitorsData(Zend_Date $periodStart, Zend_Date $periodEnd)
    {
        return $this->_getReadAdapter()->select()
            ->from(
                array('visitor' => $this->getTable("log/visitor")),
                array('count(DISTINCT visitor.visitor_id) as visitorCount', 'visitor.store_id')
            )
            ->joinLeft(
                array('customer' => $this->getTable("log/customer")),
                'customer.visitor_id = visitor.visitor_id',
                array('customer.customer_id')
            )
            ->where('first_visit_at >= "' . $periodStart->get('y-MM-dd HH:m:s') . '"')
            ->where('first_visit_at < "' . $periodEnd->get('y-MM-dd HH:m:s') . '"')
            ->group(array('customer.visitor_id', 'visitor.store_id'))
            ->query()
            ->fetchAll();
    }

    /**
     * get wishlist items by period
     *
     * @param  Zend_Date $periodStart
     * @param  Zend_Date $periodEnd
     * @return array
     */
    public function getWishlistData(Zend_Date $periodStart, Zend_Date $periodEnd)
    {
        return $this->_getReadAdapter()->select()
            ->from(
                array('item' => $this->getTable("wishlist/item")),
                'item.store_id'
            )
            ->join(
                array('wishlist' => $this->getTable("wishlist/wishlist")),
                'item.wishlist_id = wishlist.wishlist_id',
                array('wishlist.customer_id, SUM(item.qty) as whishlist_count')
            )
            ->where('item.added_at >= "' . $periodStart->get('y-MM-dd HH:m:s') . '"')
            ->where('item.added_at < "' . $periodEnd->get('y-MM-dd HH:m:s') . '"')
            ->group(array('wishlist.customer_id', 'item.store_id'))
            ->query()
            ->fetchAll();
    }

    /**
     * get products in cart by period
     *
     * @param  Zend_Date $periodStart
     * @param  Zend_Date $periodEnd
     * @return array
     */
    public function getProductsInCartData(Zend_Date $periodStart, Zend_Date $periodEnd)
    {
        return $this->_getReadAdapter()->select()
            ->from(
                array('quote_item' => $this->getTable("sales/quote_item")),
                'quote.customer_id, quote.store_id, SUM(quote_item.qty) as products_count, SUM(quote_item.row_total) as amount'
            )
            ->join(
                array('quote' => $this->getTable("sales/quote")),
                'quote_item.quote_id = quote.entity_id',
                array()
            )
            ->where('quote_item.created_at >= "' . $periodStart->get('y-MM-dd HH:m:s') . '"')
            ->where('quote_item.created_at < "' . $periodEnd->get('y-MM-dd HH:m:s') . '"')
            ->group(array('quote.customer_id', 'quote.store_id'))
            ->query()
            ->fetchAll();
    }

    /**
     * get carts by period
     *
     * @param  Zend_Date $periodStart
     * @param  Zend_Date $periodEnd
     * @return array
     */
    public function getCreatedCartsData(Zend_Date $periodStart, Zend_Date $periodEnd)
    {
        return $this->_getReadAdapter()->select()
            ->from(
                array('quote' => $this->getTable("sales/quote")),
                'quote.customer_id, quote.store_id, COUNT(*) AS cart_count, SUM(quote.grand_total) as amount'
            )
            ->where('quote.created_at >= "' . $periodStart->get('y-MM-dd HH:m:s') . '"')
            ->where('quote.created_at < "' . $periodEnd->get('y-MM-dd HH:m:s') . '"')
            ->group(array('quote.customer_id', 'quote.store_id'))
            ->query()
            ->fetchAll();
    }

    /**
     * get orders by period
     *
     * @param  Zend_Date $periodStart
     * @param  Zend_Date $periodEnd
     * @return array
     */
    public function getCreatedOrdersData(Zend_Date $periodStart, Zend_Date $periodEnd)
    {
        return $this->_getReadAdapter()->select()
            ->from(
                array('order' => $this->getTable("sales/order")),
                'order.customer_id, order.store_id, count(*) AS orders_count, SUM(order.grand_total) as amount'
            )
            ->where('order.created_at >= "' . $periodStart->get('y-MM-dd HH:m:s') . '"')
            ->where('order.created_at < "' . $periodEnd->get('y-MM-dd HH:m:s') . '"')
            ->group(array('order.customer_id', 'order.store_id'))
            ->query()
            ->fetchAll();
    }

    /**
     * get page viewes by period
     *
     * @param  Zend_Date $periodStart
     * @param  Zend_Date $periodEnd
     * @return array
     */
    public function getPageViewsData(Zend_Date $periodStart, Zend_Date $periodEnd)
    {
        return $this->_getReadAdapter()->select()
            ->from(
                array('url_info' => $this->getTable("log/url_info_table")),
                'COUNT(*) as view_count'
            )
            ->join(
                array('log_url' => $this->getTable("log/url_table")),
                'log_url.url_id = url_info.url_id',
                array()
            )
            ->joinLeft(
                array('customer' => $this->getTable("log/customer")),
                'customer.visitor_id = log_url.visitor_id',
                array('customer.customer_id')
            )
            ->joinLeft(
                array('visitor' => $this->getTable("log/visitor")),
                'visitor.visitor_id = log_url.visitor_id',
                array('visitor.store_id')
            )
            ->where('log_url.visit_time >= "' . $periodStart->get('y-MM-dd HH:m:s') . '"')
            ->where('log_url.visit_time < "' . $periodEnd->get('y-MM-dd HH:m:s') . '"')
            ->group(array('customer.customer_id'))
            ->query()
            ->fetchAll();
    }

    /**
     * get refers by period
     *
     * @param  Zend_Date $periodStart
     * @param  Zend_Date $periodEnd
     * @return array
     */
    public function getRefersData(Zend_Date $periodStart, Zend_Date $periodEnd)
    {
        return $this->_getReadAdapter()->select()
            ->from(
                array('info' => $this->getTable("log/visitor_info")),
                'SUBSTRING_INDEX(REPLACE(REPLACE(info.http_referer, "http://", ""), "www.", ""), "/", 1) AS domain, COUNT(*) AS refer_count'
            )
            ->join(
                array('visitor' => $this->getTable("log/visitor")),
                'info.visitor_id = visitor.visitor_id',
                array('visitor.store_id')
            )
            ->joinLeft(
                array('customer' => $this->getTable("log/customer")),
                'customer.visitor_id = info.visitor_id',
                array('customer.customer_id')
            )
            ->where('visitor.first_visit_at >= "' . $periodStart->get('y-MM-dd HH:m:s') . '"')
            ->where('visitor.first_visit_at < "' . $periodEnd->get('y-MM-dd HH:m:s') . '"')
            ->where('info.http_referer IS NOT NULL')
            ->group(array('customer.customer_id', 'domain'))
            ->query()
            ->fetchAll();
    }

    /**
     * get customers registrations by period
     *
     * @param  Zend_Date $periodStart
     * @param  Zend_Date $periodEnd
     * @return array
     */
    public function getRegistrationsData(Zend_Date $periodStart, Zend_Date $periodEnd)
    {
        return $this->_getReadAdapter()->select()
            ->from(
                array('customer' => $this->getTable("customer/entity")),
                array('customer.entity_id', 'customer.store_id AS store_id')
            )
            ->where('customer.created_at >= "' . $periodStart->get('y-MM-dd HH:m:s') . '"')
            ->where('customer.created_at < "' . $periodEnd->get('y-MM-dd HH:m:s') . '"')
            ->query()
            ->fetchAll();
    }

    /**
     * get logins info by period
     *
     * @param  Zend_Date $periodStart
     * @param  Zend_Date $periodEnd
     * @return array
     */
    public function getLoginsData(Zend_Date $periodStart, Zend_Date $periodEnd)
    {
        return $this->_getReadAdapter()->select()
            ->from(
                array('customer' => $this->getTable("log/customer")),
                array('customer.customer_id', 'customer.store_id', 'count(customer.log_id) AS logins')
            )
            ->where('customer.login_at >= "' . $periodStart->get('y-MM-dd HH:m:s') . '"')
            ->where('customer.login_at < "' . $periodEnd->get('y-MM-dd HH:m:s') . '"')
            ->group(array('customer.customer_id', 'store_id'))
            ->query()
            ->fetchAll();
    }

    /**
     * get products info by period
     *
     * @param  Zend_Date $periodStart
     * @param  Zend_Date $periodEnd
     * @return array
     */
    public function getProductsData(Zend_Date $periodStart, Zend_Date $periodEnd)
    {
        return $this->_getReadAdapter()->select()
            ->from(
                array('event' => $this->getTable("reports/event")),
                array('event.subject_id AS customer_id', 'event.store_id', 'event.object_id AS product_id', 'COUNT(*) AS view_count')
            )
            ->where('event.event_type_id = ' . Mage_Reports_Model_Event::EVENT_PRODUCT_VIEW)
            ->where('event.logged_at >= "' . $periodStart->get('y-MM-dd HH:m:s') . '"')
            ->where('event.logged_at < "' . $periodEnd->get('y-MM-dd HH:m:s') . '"')
            ->group(array('customer_id', 'store_id', 'product_id'))
            ->query()
            ->fetchAll();
    }

    /**
     * Returns minimum/maximum data time based on cron
     *
     * @param bool $isGoDown
     * @return string
     */
    public function getDbTime($isGoDown = false)
    {
        $adapter = $this->_getReadAdapter();
        if ($isGoDown) {
            $column = 'MIN(date_time)';
        } else {
            $column = 'MAX(date_time)';
        }

        $select = $adapter->select()
            ->from($this->getTable('oro_analytics/cron_job'), $column)
            ->where('is_real_cron = 1');

        return $adapter->fetchOne($select);
    }

    /**
     * @param string $dateTime
     * @param boolean $isRealCron
     */
    public function insertNewMaxDbTime($dateTime, $isRealCron)
    {
        $this->_getWriteAdapter()->insert($this->getTable("oro_analytics/cron_job"), array(
            'is_real_cron' => $isRealCron,
            'date_time' => $dateTime
        ));
    }

    /**
     * get minimal date from visitor table
     *
     * @return string
     */
    public function getStartDbTime()
    {
        return $this->_getReadAdapter()->select()
            ->from(
                array('log' => $this->getTable("log/visitor")),
                array('MIN(log.first_visit_at)')
            )
            ->query()
            ->fetchColumn();
    }

    /**
     * @param array $userOrder
     * @param  Zend_Date $periodStart
     * @param  Zend_Date $periodEnd
     */
    public function saveCreatedOrdersData($userOrder, $periodStart, $periodEnd)
    {
        $analyticsId = $this->getAnalyticId(
            $userOrder['customer_id'],
            $userOrder['store_id'],
            $periodStart,
            $periodEnd
        );
        $modelRecord = $this->getRecordFromDb(
            'oro_analytics/shop',
            array(array('name' => 'analytics_id', 'filter' => array('eq' => $analyticsId))),
            array('analytics_id' => $analyticsId)
        );
        $modelRecord->setOrdersCount($userOrder['orders_count'])
            ->setOrdersAmount($userOrder['amount']);

        // check if we have new customer
        if (!$userOrder['customer_id'] || count($this->getNewUserInfo(
            $periodStart,
            $periodEnd,
            $userOrder['customer_id'],
            $userOrder['store_id']
        ))) {
            // save new customer orders
            $modelRecord->setNewCustomersOrdersCount($userOrder['orders_count'])
                ->setNewCustomersOrdersAmount($userOrder['amount']);
        } else {
            // save returned customer orders
            $modelRecord->setReturnedCustomersOrdersCount($userOrder['orders_count'])
                ->setReturnedCustomersOrdersAmount($userOrder['amount']);
        }

        $modelRecord->save();
    }

    /**
     * check if customer is new customer
     *
     * @param  Zend_Date $periodStart
     * @param  Zend_Date $periodEnd
     * @param  int      $customerId
     * @param  int      $storeId
     * @return array
     */
    public function getNewUserInfo(Zend_Date $periodStart, Zend_Date $periodEnd, $customerId, $storeId)
    {
        return $this->_getReadAdapter()->select()
            ->from(
                array('customer' => $this->getTable("customer/entity")),
                'customer.store_id'
            )
            ->where('customer.created_at >= "' . $periodStart->get('y-MM-dd HH:m:s') . '"')
            ->where('customer.created_at < "' . $periodEnd->get('y-MM-dd HH:m:s') . '"')
            ->where('customer.entity_id = "' . $customerId . '"')
            ->where('customer.store_id = "' . $storeId . '"')
            ->query()
            ->fetchAll();
    }

    /**
     * @param array $userCart
     * @param  Zend_Date $periodStart
     * @param  Zend_Date $periodEnd
     */
    public function saveCreatedCartsData($userCart, $periodStart, $periodEnd)
    {
        $this->_getWriteAdapter()->insertOnDuplicate(
            $this->getTable('oro_analytics/shop'),
            array(
                'analytics_id' => $this->getAnalyticId(
                    $userCart['customer_id'],
                    $userCart['store_id'],
                    $periodStart,
                    $periodEnd
                ),
                'create_cart_count' => $userCart['cart_count'],
                'create_cart_amount' => $userCart['amount']
            ),
            array('create_cart_count', 'create_cart_amount')
        );
    }

    /**
     * @param array $cartProduct
     * @param  Zend_Date $periodStart
     * @param  Zend_Date $periodEnd
     */
    public function saveProductsInCartData($cartProduct, $periodStart, $periodEnd)
    {
        $this->_getWriteAdapter()->insertOnDuplicate(
            $this->getTable('oro_analytics/shop'),
            array(
                'analytics_id' => $this->getAnalyticId(
                    $cartProduct['customer_id'],
                    $cartProduct['store_id'],
                    $periodStart,
                    $periodEnd
                ),
                'products_in_cart_count' => $cartProduct['products_count'],
                'products_in_cart_amount' => $cartProduct['amount'],
            ),
            array('products_in_cart_count', 'products_in_cart_amount')
        );
    }

    /**
     * @param array $wishlistItem
     * @param  Zend_Date $periodStart
     * @param  Zend_Date $periodEnd
     */
    public function saveWishlistItemsData($wishlistItem, $periodStart, $periodEnd)
    {
        $this->_getWriteAdapter()->insertOnDuplicate(
            $this->getTable('oro_analytics/shop'),
            array(
                'analytics_id' => $this->getAnalyticId(
                    $wishlistItem['customer_id'],
                    $wishlistItem['store_id'],
                    $periodStart,
                    $periodEnd
                ),
                'wishlist_products_count' => $wishlistItem['whishlist_count'],
            ),
            array('wishlist_products_count')
        );
    }

    /**
     * @param array $product
     * @param  Zend_Date $periodStart
     * @param  Zend_Date $periodEnd
     */
    public function saveProductViewItemsData($product, $periodStart, $periodEnd)
    {
        $this->_getWriteAdapter()->insert($this->getTable('oro_analytics/product'), array(
            'analytics_id' => $this->getAnalyticId(
                $product['customer_id'],
                $product['store_id'],
                $periodStart,
                $periodEnd
            ),
            'view_count' => $product['view_count'],
            'products_id' => $product['product_id']
        ));
    }

    /**
     * @param array $userPage
     * @param  Zend_Date $periodStart
     * @param  Zend_Date $periodEnd
     */
    public function savePageViewsData($userPage, $periodStart, $periodEnd)
    {
        $this->_getWriteAdapter()->insert($this->getTable('oro_analytics/page'), array(
            'analytics_id' => $this->getAnalyticId(
                $userPage['customer_id'],
                $userPage['store_id'],
                $periodStart,
                $periodEnd
            ),
            'view_count' => $userPage['view_count'],
        ));
    }

    /**
     * get domain id from domain name for refers
     *
     * @param  string $domainName
     * @return int
     */
    private function getDomainId($domainName)
    {
        return (int) $this->getRecordFromDb(
                'oro_analytics/refDomain',
                array(
                    array('name' => 'domain_name', 'filter' => array('eq' => $domainName)),
                ),
                array(
                    'domain_name' => $domainName
                )
            )
            ->getId();
    }

    /**
     * @param array $refer
     * @param  Zend_Date $periodStart
     * @param  Zend_Date $periodEnd
     */
    public function saveRefersData($refer, $periodStart, $periodEnd)
    {
        $this->_getWriteAdapter()->insert($this->getTable('oro_analytics/refer'), array(
            'analytics_id' => $this->getAnalyticId(
                $refer['customer_id'],
                $refer['store_id'],
                $periodStart,
                $periodEnd
            ),
            'ref_id' => $this->getDomainId($refer['domain']),
            'ref_count' => $refer['refer_count'],
        ));
    }

    /**
     * @param array $customerLogin
     * @param  Zend_Date $periodStart
     * @param  Zend_Date $periodEnd
     */
    public function saveLoginsData($customerLogin, $periodStart, $periodEnd)
    {
        $this->_getWriteAdapter()->insertOnDuplicate(
            $this->getTable('oro_analytics/customer'),
            array(
                'analytics_id' => $this->getAnalyticId(
                    $customerLogin['customer_id'],
                    $customerLogin['store_id'],
                    $periodStart,
                    $periodEnd
                ),
                'logins_count' => $customerLogin['logins']
            ),
            array('logins_count')
        );
    }

    /**
     * @param array $user
     * @param  Zend_Date $periodStart
     * @param  Zend_Date $periodEnd
     */
    public function saveCustomerRegistrationsData($user, $periodStart, $periodEnd)
    {
        $this->_getWriteAdapter()->insertOnDuplicate(
            $this->getTable('oro_analytics/customer'),
            array(
                'analytics_id' => $this->getAnalyticId(
                    $user['entity_id'],
                    $user['store_id'],
                    $periodStart,
                    $periodEnd
                ),
                'is_new_customer' => 1
            ),
            array('is_new_customer')
        );
    }

    /**
     * @param array $visitor
     * @param  Zend_Date $periodStart
     * @param  Zend_Date $periodEnd
     */
    public function saveVisitorsData($visitor, $periodStart, $periodEnd)
    {
        $this->_getWriteAdapter()->insertOnDuplicate(
            $this->getTable('oro_analytics/visitor'),
            array(
                 'analytics_id' => $this->getAnalyticId(
                     $visitor['customer_id'],
                     $visitor['store_id'],
                     $periodStart,
                     $periodEnd
                 ),
                 'visit_count' => $visitor['customer_id'] === null ? $visitor['visitorCount'] : 1
            ),
            array('visit_count')
        );
    }

    /**
     * Check for analytics data record and
     * create new one if where is no record for this date, customer and store
     *
     * @param  int $customerId
     * @param  int $storeId
     * @param  Zend_Date $periodStart
     * @param  Zend_Date $periodEnd
     * @return int
     */
    public function getAnalyticId($customerId, $storeId, $periodStart, $periodEnd)
    {
        if (!is_numeric($storeId)) {
            $storeId = Mage::app()
                ->getWebsite()
                ->getDefaultGroup()
                ->getDefaultStoreId();
        }

        $tableName = $this->getTable('oro_analytics/data');
        $recordSearch = $this->_getReadAdapter()->select()
            ->from(
            array('entity' => $tableName),
                array('entity.id')
            )
            ->where('period = "' . $periodStart->get('y-MM-dd HH:m:s') . '"')
            ->where('store_id = ' . $storeId);
        if ($customerId != null) {
            $recordSearch->where('customer_id = ' . $customerId);
        } else {
            $recordSearch->where('customer_id IS NULL');
        }

        $recordId = $recordSearch->query()->fetchColumn();

        if (!(int) $recordId) {
            $this->checkDataPartition($periodStart);
            $this->_getWriteAdapter()->insert(
                $tableName,
                array(
                    'period' => $periodStart->get('y-MM-dd HH:m:s'),
                    'store_id' => $storeId,
                    'customer_id' => $customerId
                )
            );
            $recordId = $this->_getWriteAdapter()->lastInsertId();
        }
        return (int) $recordId;
    }

    /**
     * Check if down aggregation was finished
     */
    public function isDownAggregationFinished()
    {
        $startDate = new Zend_Date($this->getStartDbTime(), 'yyyy-MM-dd HH:m:s');
        $cronFinishDate = new Zend_Date($this->getDbTime(true), 'yyyy-MM-dd HH:m:s');
        if ($cronFinishDate->sub($startDate)->toValue() <= 3600 ) {
            return true;
        }

        return false;
    }
}
