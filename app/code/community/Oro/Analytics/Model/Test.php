<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Analytics
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Analytics_Model_Test extends Mage_Core_Model_Abstract
{
    const PARTITION_INCREMENT_PERIOD = 'P7D';

    private $products = array();

    private $customerDailyActivityCount = 200;

    /**
     * @var Varien_Db_Adapter_Pdo_Mysql
     */
    private $readConnection;

    /**
     * @var Varien_Db_Adapter_Pdo_Mysql
     */
    private $writeConnection;

    private $coreResource;

    /**
     * @var DateTime
     */
    private $startData;

    public function __construct()
    {
        $this->coreResource = Mage::getSingleton('core/resource');
        $this->startData = new DateTime('2012-01-01 00:00');
        $this->writeConnection = $this->coreResource->getConnection('write');
        $this->readConnection = $this->coreResource->getConnection('read');
    }

    /**
     * generate data for aggregation
     *
     * @param string $dataStart
     */
    public function generateDataForLogging($dataStart = '2012-09-01 00:00:00')
    {
        $dataStartObject = new Zend_Date($dataStart);
        $this->writeConnection->exec('SET FOREIGN_KEY_CHECKS = 0');

        $this->generateVisitors($dataStartObject);
        $visitorsCount = $this->readConnection->fetchOne('select count(*) from log_visitor');

        for ($day = 1; $day <= 92; $day++) {
            for ($time = 0; $time <= 23; $time++) {
                $date = clone $dataStartObject;
                $date->addDay($day);
                $date->addHour($time);
                echo  $date->get("y-MM-dd H:00:00") . PHP_EOL;

                $this->generateWishlist($date);
                $this->generateOrders($date);
                for ($i = 1; $i<=150; $i++) {
                    $visitorId = rand(1, $visitorsCount);
                    $this->generateLogUrls($date, $visitorId);
                    $this->generateLogCustomer($date, $visitorId);
                }
            }
        }
        $this->writeConnection->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    private function generateOrders(Zend_Date $date)
    {
        for ($i = 1; $i < rand(0, 10); $i++) {
            $this->writeConnection->insert(
                $this->readConnection->getTableName('sales_flat_order'),
                array(
                     'state' => 'new',
                     'status' => 'pending',
                     'protect_code' => '33df7b',
                     'shipping_description' => 'Flat Rate - Fixed',
                     'is_virtual' => 0,
                     'store_id' =>1,
                     'customer_id' =>rand(1, 2000),
                     'base_discount_amount' => 0,
                     'base_grand_total' => rand(1, 1000),
                     'base_shipping_amount' => rand(1, 100),
                     'base_subtotal' => rand(1,1000),
                     'base_tax_amount' => 0,
                     'base_to_global_rate' => 1,
                     'base_to_order_rate' => 1,
                     'discount_amount' => 0,
                     'grand_total' => rand(1, 1000),
                     'shipping_amount' => rand(1, 100),
                     'shipping_tax_amount' => 0,
                     'store_to_base_rate' => 1,
                     'store_to_order_rate' => 1,
                     'subtotal' => rand(1, 100),
                     'tax_amount' => 0,
                     'total_qty_ordered' => rand(1, 10),
                     'customer_is_guest' => 0,
                     'customer_note_notify' => 1,
                     'customer_group_id' => 1,
                     'email_sent' => 1,
                     'weight' => rand(1, 30),
                     'customer_email' => 'test@test.com',
                     'created_at' => $date->get("y-MM-dd H:" . rand(0, 59) . ":00"),
                     'updated_at' => $date->get("y-MM-dd H:" . rand(0, 59) . ":00"),
                     'total_item_count' => rand(1, 10),
                )
            );

            $orderId = $this->writeConnection->lastInsertId();
            for ($j = 1; $j<=10; $j++) {
                $this->writeConnection->insert(
                    $this->readConnection->getTableName('sales_flat_order_item'),
                    array(
                         'order_id' => $orderId,
                         'quote_item_id' => rand(1, 50),
                         'store_id' => 1,
                         'created_at' => $date->get("y-MM-dd H:" . rand(0, 59) . ":00"),
                         'updated_at' => $date->get("y-MM-dd H:" . rand(0, 59) . ":00"),
                         'product_id' => rand(1, 100),
                         'product_type' => 'simple',
                         'price' => rand(1, 100),
                    )
                );
            }
        }
    }

    /**
     * Generate withlist items
     *
     * @param Zend_Date $date
     */
    private function generateWishlist(Zend_Date $date)
    {
        $this->writeConnection->insert(
            $this->readConnection->getTableName('wishlist'),
            array(
                 'customer_id'=> rand(1, 2000),
                 'updated_at' => $date->get("y-MM-dd H:" . rand(0, 59) . ":00"),
            )
        );
        $wishlistId = $this->writeConnection->lastInsertId();
        for ($i = 1; $i<=10; $i++) {
            $this->writeConnection->insert(
                $this->readConnection->getTableName('wishlist_item'),
                array(
                     'wishlist_id' => $wishlistId,
                     'product_id' => rand(1, 2000),
                     'store_id' => 1,
                     'added_at' => $date->get("y-MM-dd H:" . rand(0, 59) . ":00"),
                     'qty' =>1,
                )
            );
        }
    }

    /**
     * generate customers
     *
     * @param Zend_Date $date
     * @param int $visitorId
     */
    private function generateLogCustomer(Zend_Date $date, $visitorId)
    {
        $this->writeConnection->insert(
            $this->readConnection->getTableName('log_customer'),
            array(
                 'visitor_id' => $visitorId,
                 'customer_id'=> rand(1, 2000),
                 'login_at' => $date->get("y-MM-dd H:" . rand(0, 59) . ":00"),
                 'store_id' => 1
            )
        );
    }

    /**
     * generate visitors data
     *
     * @param Zend_Date $dataStartObject
     */
    private function generateVisitors(Zend_Date $dataStartObject)
    {
        for ($day = 1; $day <= 92; $day++) {
            for ($time = 0; $time <= 23; $time++) {
                $date = clone $dataStartObject;
                $date->addDay($day);
                $date->addHour($time);

                $this->writeConnection->insert(
                    $this->readConnection->getTableName('log_visitor'),
                    array(
                         'session_id' => md5(time()),
                         'first_visit_at' => $date->get("y-MM-dd H:" . rand(0, 59) . ":00"),
                         'last_visit_at' => $date->get("y-MM-dd H:00:00"),
                         'last_url_id' => rand(1,100),
                         'store_id' => 1
                    )
                );
            }
        }
    }

    /**
     * generate log_url data
     *
     * @param Zend_Date $date
     * @param int $visitorId
     */
    private function generateLogUrls(Zend_Date $date, $visitorId)
    {
        $tableName = $this->readConnection->getTableName('log_url');
        $maxId = (int)$this->readConnection->fetchOne('select max(url_id) from ' . $tableName);
        $maxId++;
        $this->writeConnection->insert(
            $tableName,
            array(
                 'url_id' => $maxId,
                 'visitor_id' => $visitorId,
                 'visit_time' => $date->get("y-MM-dd H:" . rand(0, 59) . ":" . rand(0, 59)),
            )
        );
    }

    /**
     * increment startData date time for $addDays days and $addTime hours
     *
     * @param  int      $addDays
     * @param  int      $addTime
     * @return DateTime
     */
    private function getWorkingDateTime($addDays, $addTime)
    {
        $dateObject = clone $this->startData;
        $intervalString = 'P';
        if ($addDays > 0) {
            $intervalString .= $addDays . 'D';
        }
        if ($addTime >= 0) {
            $intervalString .= 'T' . $addTime . 'H';
        }
        if ($addDays > 0 && $addTime >= 0) {
            $dateObject->add(new DateInterval($intervalString));
        }

        return $dateObject;
    }

    public function importTestProducts($productCount)
    {
        //echo 'import products' . PHP_EOL;
        $itemModel = Mage::getModel('catalog/product');
        $entityTypeId = $itemModel->getResource()->getTypeId();

        $default_attribute_set_id = $itemModel->getResource()->getEntityType()->getDefaultAttributeSetId();

        $product_table = $this->coreResource->getTableName('catalog/product');
        $product_website_table = $this->coreResource->getTableName('catalog/product_website');

        $default_website = $websiteCollection = Mage::getResourceModel('core/website_collection')
            ->addFieldToFilter('is_default', true)
            ->getFirstItem();

        $website_ids = array($default_website->getId());

        $visibilityId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'visibility');
        $statusId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'status');
        $nameId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'name');

        $this->writeConnection->beginTransaction();

        for ($i = 0; $i < $productCount; $i++) {

            $values = array();
            $values['entity_type_id'] = $entityTypeId;
            $values['attribute_set_id'] = $default_attribute_set_id;
            $values['type_id'] = "simple";
            $values['sku'] = "test_sku_" . $i;
            $values['created_at'] = '';
            $values['updated_at'] = '';

            $this->writeConnection->insert($product_table, $values);
            $product_id = $this->writeConnection->lastInsertId();
            $this->products[] = $product_id;

            foreach ($website_ids as $website_id) {
                $this->writeConnection->insert($product_website_table, array('product_id' => $product_id, 'website_id' => $website_id));
            }

            $table = "catalog_product_entity_int";

            $values = array(
                'entity_id' => $product_id,
                'attribute_id' => $visibilityId,
                'entity_type_id' => $entityTypeId,
                'store_id' => 0,
                'value' => '4' //Catalog, Search
            );

            $this->writeConnection->insert($table, $values);

            $values = array(
                'entity_id' => $product_id,
                'attribute_id' => $statusId,
                'entity_type_id' => $entityTypeId,
                'store_id' => 0,
                'value' => '1' //Enabled
            );

            $this->writeConnection->insert($table, $values);

            $table = "catalog_product_entity_varchar";

            $values = array(
                'entity_id' => $product_id,
                'attribute_id' => $nameId,
                'entity_type_id' => $entityTypeId,
                'store_id' => 0,
                'value' => 'Test Product ' . $i
            );

            $this->writeConnection->insert($table, $values);

            if ($i % 200 == 0) {

                $this->writeConnection->commit();
                $this->writeConnection->beginTransaction();
            }
        }

        $this->writeConnection->commit();
    }

    private function addDomains()
    {
        //echo 'add domains' . PHP_EOL;
        $this->writeConnection->beginTransaction();
        for ($i = 0; $i <= 100; $i++) {
            $this->writeConnection->insert('oro_ref_domain', array('domain_name' => 'www.test' . $i . '.com'));
        }
        $this->writeConnection->commit();
    }

    private function addSiteUrls()
    {
        //echo 'add urls' . PHP_EOL;
        $this->writeConnection->beginTransaction();
        for ($i = 0; $i <= 100; $i++) {
            $this->writeConnection->insert('oro_site_url', array('http_url' => 'http://test.loc/page/' . $i));
        }
        $this->writeConnection->commit();
    }

    private function addCustomers($customersCount)
    {
        //echo 'add customers' . PHP_EOL;
        $itemModel = Mage::getModel('customer/customer');
        $entityTypeId = $itemModel->getResource()->getTypeId();
        $addressModel = Mage::getModel('customer/address');
        $addressEntityTypeId = $addressModel->getResource()->getTypeId();
        $customerTable = $this->coreResource->getTableName('customer/entity');
        $customerAddressTable = $this->coreResource->getTableName('customer/address_entity');
        $dateStart = strtotime('2009-12-10');
        $dayStep = 86400;

        $defaultBilling = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('customer', 'default_billing');
        $defaultShipping = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('customer', 'default_shipping');
        $states = array("AZ", "CA", "CO");

        $customerIds = array("");
        $this->writeConnection->beginTransaction();

        for ($i = 0; $i < $customersCount; $i++) {
            $values = array();
            $values['entity_type_id'] = $entityTypeId;
            $values['attribute_set_id'] = 0;
            $values['website_id'] = 1;
            $values['email'] = "test@test.com";
            $values['group_id'] = 1;
            $values['store_id'] = 1;
            $values['created_at'] = date("Y-m-d", ($dateStart + rand(0, 500) * $dayStep));
            $values['updated_at'] = '';
            $values['is_active'] = 1;

            $this->writeConnection->insert($customerTable, $values);
            $customerId = $this->writeConnection->lastInsertId();
            $customerIds[] = $customerId;

            $values = array();
            $values['entity_type_id'] = $addressEntityTypeId;
            $values['attribute_set_id'] = 0;
            $values['parent_id'] = $customerId;
            $values['created_at'] = '';
            $values['updated_at'] = '';
            $values['is_active'] = 1;
            $this->writeConnection->insert($customerAddressTable, $values);
            $customer_address_id = $this->writeConnection->lastInsertId();

            foreach ($this->_attributeCollection as $attribute) {
                $table = $attribute->getBackendTable();
                $values = array(
                    'entity_id' => $customer_address_id,
                    'attribute_id' => $attribute->getId(),
                    'entity_type_id' => $addressEntityTypeId,
                    'value' => null
                );

                if ($attribute->getAttributeCode() == "country_id") {
                    $values['value'] = "US";
                } elseif ($attribute->getAttributeCode() == "region_id") {
                    $values['value'] = $states[rand(0, 2)];
                } elseif ($attribute->getAttributeCode() == "region") {
                    $values['value'] = $states[rand(0, 2)];
                } else {
                    $values['value'] = "test_" . rand(0, 100);
                }

                $this->writeConnection->insert($table, $values);
            }

            $table = "customer_entity_int";

            $values = array(
                'entity_id' => $customerId,
                'attribute_id' => $defaultBilling,
                'entity_type_id' => $entityTypeId,
                'value' => $customer_address_id
            );
            $this->writeConnection->insert($table, $values);

            $values = array(
                'entity_id' => $customerId,
                'attribute_id' => $defaultShipping,
                'entity_type_id' => $entityTypeId,
                'value' => $customer_address_id
            );
            $this->writeConnection->insert($table, $values);

            if ($i % 200 == 0) {
                $this->writeConnection->commit();
                $this->writeConnection->beginTransaction();
            }
        }
        $this->writeConnection->commit();

        return $customerIds;
    }

    /**
     * Directly insert test data for customers and customers activity
     *
     * @param int  $customersCount
     * @param bool $importProducts
     * @param bool $isAddDomain
     * @param bool $isAddUrls
     */
    public function run($customersCount = 0, $importProducts = false, $isAddDomain = false, $isAddUrls = false)
    {
        //echo 'start work' . PHP_EOL;
        $storeId = 1;
        $customerTable = $this->coreResource->getTableName('customer/entity');
        $this->_attributeCollection = Mage::getResourceModel('customer/address_attribute_collection')->setItemObjectClass('eav/entity_attribute');

        $customerIds = array();

        // add domains
        if ($isAddDomain) {
            $this->addDomains();
        }

        //add urls
        if ($isAddUrls) {
            $this->addSiteUrls();
        }

        // add customers
        if ($customersCount > 0) {
            $customerIds = $this->addCustomers($customersCount);
        } else {
            $customerIdsArray = $this->readConnection->fetchAll('select entity_id from ' . $customerTable);
            foreach ($customerIdsArray as $record) {
                $customerIds[] = $record['entity_id'];
            }
        }

        //work with products
        if ($importProducts) {
            $this->importTestProducts(1000);
        } else {
            $products = $this->readConnection->fetchAll('select entity_id from catalog_product_entity');
            foreach ($products as $product) {
                $this->products[] = $product['entity_id'];
            }
        }

        //generate user activity
        //echo 'start generate user activity' . PHP_EOL;
        $customerCount = count($customerIds);
        $productCount = count($this->products);

        $this->writeConnection->beginTransaction();

        $analyticsTable = "oro_analytics_data";
        $analyticsProductTable = "oro_analytics_product";

        for ($day = 1; $day <= 365; $day++) {
            for ($time = 0; $time <= 23; $time++) {
                $date = $this->getWorkingDateTime($day, $time);
                $this->checkDataPartition($date);
                for ($i = 0; $i < $this->customerDailyActivityCount; $i++) {
                    //store main analytic table data
                    $customerId = $customerIds[rand(0, $customerCount - 1)];
                    //echo '-- write user activity for user' . $customerId . ' for date ' . $date->format('Y-m-d H:i:s') . PHP_EOL;
                    $values = array(
                        'period' => $date->format('Y-m-d H:i:s'),
                        'store_id' => $storeId,
                        'customer_id' => $customerId,
                    );
                    $this->writeConnection->insert($analyticsTable, $values);

                    $analyticsId = $this->writeConnection->lastInsertId();
                    $this->addCustomerActivity($analyticsId);
                    $this->addShopActivity($analyticsId);
                    // add products activity
                    if ($productCount) {
                        $this->generateProductsActivity($productCount, $analyticsId, $analyticsProductTable);
                    }

                    $this->generateReferals($analyticsId);
                    $this->generateUserPageActivity($analyticsId);
                    $this->generateCategoriesViewsActivity($analyticsId);
                    $this->generateSearchesActivity($analyticsId);

                    //echo 'save data' . PHP_EOL . PHP_EOL;
                    $this->writeConnection->commit();
                    $this->writeConnection->beginTransaction();
                }
            }
        }
        $this->writeConnection->commit();
    }

    private function addCustomerActivity($analyticsId)
    {
        $this->writeConnection->insert('oro_analytics_customer', array(
            'analytics_id' => $analyticsId,
            'is_new_customer' => rand(0, 1),
            'newsletter_subscription' => rand(0, 1),
            'is_returned' => rand(0, 1),
            'is_registered' => rand(0, 1),
            'logins_count' => rand(0, 3),
        ));
    }

    private function addShopActivity($analyticsId)
    {
        $this->writeConnection->insert('oro_analytics_shop', array(
            'analytics_id' => $analyticsId,
            'orders_count' => rand(0, 2),
            'products_in_cart_count' => rand(0, 5),
            'checkout_count' => rand(0, 2),
            'wishlist_products_count' => rand(0, 10),
            'create_cart_count' => rand(0, 3),
        ));
    }

    /**
     * write user add products activity
     *
     * @param int    $productCount
     * @param int    $analyticsId
     * @param string $analyticsProductTable
     */
    private function generateProductsActivity($productCount, $analyticsId, $analyticsProductTable)
    {
        //echo '----- write user add products activity' . PHP_EOL;
        $inserts = '';
        for ($j = 0; $j < 4; $j++) {
            $product_id = $this->products[rand(0, $productCount - 1)];
            $values = array(
                'analytics_id' => $analyticsId,
                'products_id' => $product_id,
                'view_count' => rand(1, 4)
            );
            if (!$inserts) {
                $inserts = array(
                    'q' => sprintf('INSERT INTO `%s` (`%s`) VALUES ', $analyticsProductTable, implode('`,`', array_keys($values))),
                    'v' => array(sprintf('(%s)', implode(',', $values))),
                );
            } else {
                $inserts['v'][] = sprintf('(%s)', implode(',', $values));
            }

        }
        $inserts['v'] = implode(',', $inserts['v']);
        $inserts = implode(' ', $inserts);

        $this->writeConnection->query($inserts);
    }

    /**
     * add referal
     *
     * @param int $analyticsId
     */
    private function generateReferals($analyticsId)
    {
        //echo '----- add referal' . PHP_EOL;
        $this->writeConnection->insert('oro_analytics_refer', array(
            'analytics_id' => $analyticsId,
            'ref_id' => rand(1, 100),
            'ref_count' => 1
        ));
    }

    /**
     * add user page activity
     *
     * @param int $analyticsId
     */
    private function generateUserPageActivity($analyticsId)
    {
        //echo '----- add user page activity' . PHP_EOL;
        $this->writeConnection->insert('oro_analytics_page', array(
            'analytics_id' => $analyticsId,
            'view_count' => rand(1, 100)
        ));
        /*$maxPagesForSession = rand(1, 100);
        for ($i = 1; $i <= $maxPagesForSession; $i++) {
            $this->writeConnection->insert('oro_analytics_pages', array(
                'analytics_id' => $analyticsId,
                'url_id' => rand(1, 1000),
                'view_count' => rand(1, 100)
            ));
        }*/
    }

    /**
     * add user categories view activity
     *
     * @param int $analyticsId
     */
    private function generateCategoriesViewsActivity($analyticsId)
    {
        //echo '----- add user categories view activity' . PHP_EOL;
        $maxPagesForSession = rand(1, 10);
        for ($i = 1; $i <= $maxPagesForSession; $i++) {
            $this->writeConnection->insert('oro_analytics_category', array(
                'analytics_id' => $analyticsId,
                'category_id' => rand(1, 100),
                'count' => rand(1, 10)
            ));
        }
    }

    /**
     * add user searches
     *
     * @param int $analyticsId
     */
    private function generateSearchesActivity($analyticsId)
    {
        //echo '----- add user searches' . PHP_EOL;
        $maxPagesForSession = rand(1, 10);
        for ($i = 1; $i <= $maxPagesForSession; $i++) {
            $this->writeConnection->insert('oro_analytics_search', array(
                'analytics_id' => $analyticsId,
                'search_string' => 'search_string_' . $i,
                'count' => rand(1, 2)
            ));
        }
    }

    /**
     * check for partition of main analytics data table
     *
     * @param DateTime $currentDateTime
     */
    private function checkDataPartition($currentDateTime)
    {
        $maxDateTime = $this->readConnection->fetchOne('select MAX(date_time) from oro_date_partition');
        $maxDateObject = new DateTime($maxDateTime);
        if ($currentDateTime->diff($maxDateObject)->format('%R') != '+') {
            $maxDateObject->add(new DateInterval(self::PARTITION_INCREMENT_PERIOD));
            $this->writeConnection->insert('oro_date_partition', array('date_time' => $maxDateObject->format('Y-m-d H:i:s')));
            $this->writeConnection->exec('ALTER TABLE oro_analytics_data
                ADD PARTITION (
                    PARTITION p_' . $maxDateObject->format('Y_m_d') . '  VALUES LESS THAN(TO_DAYS("' . $maxDateObject->format('Y-m-d H:i:s') . '"))
                )');
        }
    }
}
