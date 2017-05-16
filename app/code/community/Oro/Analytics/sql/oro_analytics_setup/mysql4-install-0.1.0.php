<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Analytics
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

/* @var $installer Oro_Analytics_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
/* @var $table Varien_Db_Ddl_Table */

foreach (array('', '_daily') as $postfix) {
    /**
     * Create table 'oro_analytics/data'
     */
    $table = $installer->getConnection()
        ->newTable($installer->getTable('oro_analytics/data' . $postfix))
        ->addColumn(
            'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                                                                'identity' => true,
                                                                'unsigned' => true,
                                                                'nullable' => false,
                                                           )
        )
        ->addColumn('period', Varien_Db_Ddl_Table::TYPE_DATETIME)
        ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addIndex(
            $installer->getIdxName(
                'oro_analytics/data' . $postfix, array('id', 'period'), Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY
            ),
            array('id', 'period'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY)
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/data' . $postfix, array('id')),
            array('id')
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/data' . $postfix, array('customer_id', 'period')),
            array('customer_id', 'period')
        )
        ->setOption('type', 'MyISAM');
    $installer->getConnection()->createTable($table);

    /**
     * Create table 'oro_analytics/category'
     */
    $table = $installer->getConnection()
        ->newTable($installer->getTable('oro_analytics/category' . $postfix))
        ->addColumn(
        'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                                                            'identity' => true,
                                                            'unsigned' => true,
                                                            'nullable' => false,
                                                            'primary'  => true,
                                                       )
        )
        ->addColumn('analytics_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('filter_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('option_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('category_count', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addIndex(
            $installer->getIdxName('oro_analytics/category' . $postfix, array('analytics_id')),
            array('analytics_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/category' . $postfix, array('category_id')),
            array('category_id')
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/category' . $postfix, array('analytics_id', 'category_id')),
            array('analytics_id', 'category_id')
        )
        ->setOption('type', 'MyISAM');
    $installer->getConnection()->createTable($table);

    /**
     * Create table 'oro_analytics/search'
     */
    $table = $installer->getConnection()
        ->newTable($installer->getTable('oro_analytics/search' . $postfix))
        ->addColumn(
        'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                                                            'identity' => true,
                                                            'unsigned' => true,
                                                            'nullable' => false,
                                                            'primary'  => true,
                                                       )
        )
        ->addColumn('analytics_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('search_string', Varien_Db_Ddl_Table::TYPE_VARCHAR, 250)
        ->addColumn('search_count', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addIndex(
            $installer->getIdxName('oro_analytics/search' . $postfix, array('analytics_id')),
            array('analytics_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/search' . $postfix, array('search_string')),
            array('search_string')
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/search' . $postfix, array('analytics_id', 'search_string')),
            array('analytics_id', 'search_string')
        )
        ->setOption('type', 'MyISAM');
    $installer->getConnection()->createTable($table);

    /**
     * Create table 'oro_analytics/product'
     */
    $table = $installer->getConnection()
        ->newTable($installer->getTable('oro_analytics/product' . $postfix))
        ->addColumn(
            'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                                                                'identity' => true,
                                                                'unsigned' => true,
                                                                'nullable' => false,
                                                                'primary'  => true,
                                                           )
        )
        ->addColumn('analytics_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('products_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('view_count', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addIndex(
        $installer->getIdxName('oro_analytics/product' . $postfix, array('products_id')),
            array('products_id')
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/product' . $postfix, array('analytics_id')),
            array('analytics_id')
        )
        ->setOption('type', 'MyISAM');
    $installer->getConnection()->createTable($table);

    /**
     * Create table 'oro_analytics/tag'
     */
    $table = $installer->getConnection()
        ->newTable($installer->getTable('oro_analytics/tag' . $postfix))
        ->addColumn(
            'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                                                                'identity' => true,
                                                                'unsigned' => true,
                                                                'nullable' => false,
                                                                'primary'  => true,
                                                           )
        )
        ->addColumn('analytics_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('tag_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addIndex(
            $installer->getIdxName('oro_analytics/tag' . $postfix, array('analytics_id')),
            array('analytics_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/tag' . $postfix, array('tag_id')),
            array('tag_id')
        )
        ->setOption('type', 'MyISAM');
    $installer->getConnection()->createTable($table);

    /**
     * Create table 'oro_analytics/review'
     */
    $table = $installer->getConnection()
        ->newTable($installer->getTable('oro_analytics/review' . $postfix))
        ->addColumn(
            'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                                                                'identity' => true,
                                                                'unsigned' => true,
                                                                'nullable' => false,
                                                                'primary'  => true,
                                                           )
        )
        ->addColumn('analytics_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('review_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addIndex(
        $installer->getIdxName('oro_analytics/review' . $postfix, array('analytics_id')),
            array('analytics_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/review' . $postfix, array('review_id')),
            array('review_id')
        )
        ->setOption('type', 'MyISAM');
    $installer->getConnection()->createTable($table);

    /**
     * Create table 'oro_analytics/refer'
     */
    $table = $installer->getConnection()
        ->newTable($installer->getTable('oro_analytics/refer' . $postfix))
        ->addColumn(
            'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                                                                'identity' => true,
                                                                'unsigned' => true,
                                                                'nullable' => false,
                                                                'primary'  => true,
                                                           )
        )
        ->addColumn('analytics_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('ref_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('ref_count', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addIndex(
        $installer->getIdxName('oro_analytics/refer' . $postfix, array('analytics_id')),
            array('analytics_id')
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/refer' . $postfix, array('ref_id')),
            array('ref_id')
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/refer' . $postfix, array('analytics_id', 'ref_id')),
            array('analytics_id', 'ref_id')
        )
        ->setOption('type', 'MyISAM');
    $installer->getConnection()->createTable($table);

    /**
     * Create table 'oro_analytics/page'
     */
    $table = $installer->getConnection()
        ->newTable($installer->getTable('oro_analytics/page' . $postfix))
        ->addColumn(
            'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                                                                'identity' => true,
                                                                'unsigned' => true,
                                                                'nullable' => false,
                                                                'primary'  => true,
                                                           )
        )
        ->addColumn('analytics_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('view_count', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addIndex(
            $installer->getIdxName('oro_analytics/page' . $postfix, array('analytics_id')),
            array('analytics_id')
        )
        ->setOption('type', 'MyISAM');
    $installer->getConnection()->createTable($table);

    /**
     * Create table 'oro_analytics/visitor'
     */
    $table = $installer->getConnection()
        ->newTable($installer->getTable('oro_analytics/visitor' . $postfix))
        ->addColumn(
            'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                                                                'identity' => true,
                                                                'unsigned' => true,
                                                                'nullable' => false,
                                                                'primary'  => true,
                                                           )
        )
        ->addColumn('analytics_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('visit_count', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addIndex(
            $installer->getIdxName('oro_analytics/visitor' . $postfix, array('analytics_id')),
            array('analytics_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        )
        ->setOption('type', 'MyISAM');
    $installer->getConnection()->createTable($table);

    /**
     * Create table 'oro_analytics/customer'
     */
    $table = $installer->getConnection()
        ->newTable($installer->getTable('oro_analytics/customer' . $postfix))
        ->addColumn(
            'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                                                                'identity' => true,
                                                                'unsigned' => true,
                                                                'nullable' => false,
                                                                'primary'  => true,
                                                           )
        )
        ->addColumn('analytics_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('is_new_customer', Varien_Db_Ddl_Table::TYPE_BOOLEAN)
        ->addColumn('newsletter_subscription', Varien_Db_Ddl_Table::TYPE_BOOLEAN)
        ->addColumn('is_returned', Varien_Db_Ddl_Table::TYPE_BOOLEAN)
        ->addColumn('is_registered', Varien_Db_Ddl_Table::TYPE_BOOLEAN)
        ->addColumn('logins_count', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addIndex(
            $installer->getIdxName('oro_analytics/customer' . $postfix, array('analytics_id')),
            array('analytics_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/customer' . $postfix, array('analytics_id', 'is_new_customer')),
            array('analytics_id', 'is_new_customer')
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/customer' . $postfix, array('analytics_id', 'newsletter_subscription')),
            array('analytics_id', 'newsletter_subscription')
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/customer' . $postfix, array('analytics_id', 'is_returned')),
            array('analytics_id', 'is_returned')
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/customer' . $postfix, array('analytics_id', 'is_registered')),
            array('analytics_id', 'is_registered')
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/customer' . $postfix, array('is_new_customer')),
            array('is_new_customer')
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/customer' . $postfix, array('newsletter_subscription')),
            array('newsletter_subscription')
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/customer' . $postfix, array('is_returned')),
            array('is_returned')
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/customer' . $postfix, array('is_registered')),
            array('is_registered')
        )
        ->setOption('type', 'MyISAM');
    $installer->getConnection()->createTable($table);

    /**
     * Create table 'oro_analytics/category_filter'
     */
    $table = $installer->getConnection()
        ->newTable($installer->getTable('oro_analytics/category_filter' . $postfix))
        ->addColumn(
            'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                                                                'identity' => true,
                                                                'unsigned' => true,
                                                                'nullable' => false,
                                                                'primary'  => true,
                                                           )
        )
        ->addColumn('analytics_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('filter_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('option_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('filter_count', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addIndex(
            $installer->getIdxName('oro_analytics/page' . $postfix, array('analytics_id')),
            array('analytics_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/page' . $postfix, array('option_id')),
            array('option_id')
        )
        ->setOption('type', 'MyISAM');
    $installer->getConnection()->createTable($table);

    /**
     * Create table 'oro_analytics/shop'
     */
    $table = $installer->getConnection()
        ->newTable($installer->getTable('oro_analytics/shop' . $postfix))
        ->addColumn(
            'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                                                                'identity' => true,
                                                                'unsigned' => true,
                                                                'nullable' => false,
                                                                'primary'  => true,
                                                           )
        )
        ->addColumn('analytics_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('orders_count', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('products_in_cart_count', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('checkout_count', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('wishlist_products_count', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('create_cart_count', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('new_customers_orders_count', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('returned_customers_orders_count', Varien_Db_Ddl_Table::TYPE_INTEGER)
        ->addColumn('orders_amount', Varien_Db_Ddl_Table::TYPE_DOUBLE)
        ->addColumn('products_in_cart_amount', Varien_Db_Ddl_Table::TYPE_DOUBLE)
        ->addColumn('checkout_amount', Varien_Db_Ddl_Table::TYPE_DOUBLE)
        ->addColumn('wishlist_products_amount', Varien_Db_Ddl_Table::TYPE_DOUBLE)
        ->addColumn('create_cart_amount', Varien_Db_Ddl_Table::TYPE_DOUBLE)
        ->addColumn('new_customers_orders_amount', Varien_Db_Ddl_Table::TYPE_DOUBLE)
        ->addColumn('returned_customers_orders_amount', Varien_Db_Ddl_Table::TYPE_DOUBLE)
        ->addIndex(
            $installer->getIdxName('oro_analytics/shop' . $postfix, array('analytics_id')),
            array('analytics_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/shop' . $postfix, array('analytics_id', 'orders_count')),
            array('analytics_id', 'orders_count')
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/shop' . $postfix, array('analytics_id', 'products_in_cart_count')),
            array('analytics_id', 'products_in_cart_count')
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/shop' . $postfix, array('analytics_id', 'checkout_count')),
            array('analytics_id', 'checkout_count')
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/shop' . $postfix, array('analytics_id', 'wishlist_products_count')),
            array('analytics_id', 'wishlist_products_count')
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/shop' . $postfix, array('analytics_id', 'create_cart_count')),
            array('analytics_id', 'create_cart_count')
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/shop' . $postfix, array('orders_count')),
            array('orders_count')
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/shop' . $postfix, array('products_in_cart_count')),
            array('products_in_cart_count')
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/shop' . $postfix, array('checkout_count')),
            array('checkout_count')
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/shop' . $postfix, array('wishlist_products_count')),
            array('wishlist_products_count')
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/shop' . $postfix, array('create_cart_count')),
            array('create_cart_count')
        )
        ->addIndex(
            $installer->getIdxName('oro_analytics/shop' . $postfix, array('analytics_id', 'new_customers_orders_count')),
            array('analytics_id', 'new_customers_orders_count')
        )
        ->addIndex(
            $installer->getIdxName(
                'oro_analytics/shop' . $postfix, array('analytics_id', 'returned_customers_orders_count')
            ),
            array('analytics_id', 'returned_customers_orders_count')
        )
        ->setOption('type', 'MyISAM');
    $installer->getConnection()->createTable($table);

    /**
     * Create table 'oro_analytics/date_partition'
     */
    $table = $installer->getConnection()
        ->newTable($installer->getTable('oro_analytics/date_partition' . $postfix))
        ->addColumn(
            'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                                                                'identity' => true,
                                                                'unsigned' => true,
                                                                'nullable' => false,
                                                                'primary'  => true,
                                                           )
        )
        ->addColumn('date_time', Varien_Db_Ddl_Table::TYPE_DATETIME);
    $installer->getConnection()->createTable($table);

    /**
     * Create table 'oro_analytics/cron_job'
     */
    $table = $installer->getConnection()
        ->newTable($installer->getTable('oro_analytics/cron_job' . $postfix))
        ->addColumn(
            'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                                                                'identity' => true,
                                                                'unsigned' => true,
                                                                'nullable' => false,
                                                                'primary'  => true,
                                                           )
        )
        ->addColumn('date_time', Varien_Db_Ddl_Table::TYPE_DATETIME)
        ->addColumn('is_real_cron', Varien_Db_Ddl_Table::TYPE_BOOLEAN);
    if ($postfix == '_daily') {
        $table->addColumn('timeZone', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100);
    }
    $installer->getConnection()->createTable($table);
}

/**
 * Create table 'oro_analytics/ref_domain'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('oro_analytics/ref_domain'))
    ->addColumn(
        'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                                                            'identity' => true,
                                                            'unsigned' => true,
                                                            'nullable' => false,
                                                            'primary'  => true,
                                                       )
    )
    ->addColumn('domain_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 125)
    ->addIndex(
        $installer->getIdxName('oro_analytics/ref_domain', array('domain_name')),
        array('domain_name')
    )
    ->setOption('type', 'MyISAM');
$installer->getConnection()->createTable($table);

/*saving current timezone*/
$currentTimeZone = $installer->getStoreTimezone();
$installer->setConfigData(Oro_Analytics_Helper_Data::XML_CURRENT_STORE_TIMEZONE, $currentTimeZone);

/* add indexes to log tables */
$tableIndexes = $installer->getConnection()->getIndexList($installer->getTable('log/customer'));
if(!isset($tableIndexes[$installer->getIdxName('log/customer', array('login_at'))])) {
    $installer->getConnection()->addIndex(
        $installer->getTable('log/customer'),
        $installer->getIdxName('log/customer', array('login_at')),
        array('login_at')
    );
}
$tableIndexes = $installer->getConnection()->getIndexList($installer->getTable('log/customer'));
if(!isset($tableIndexes[$installer->getIdxName('log/customer', array('login_at'))])) {
    $installer->getConnection()->addIndex(
        $installer->getTable('customer/entity'),
        $installer->getIdxName('customer/entity', array('created_at')),
        array('created_at')
    );
}
$tableIndexes = $installer->getConnection()->getIndexList($installer->getTable('log/customer'));
if(!isset($tableIndexes[$installer->getIdxName('log/customer', array('login_at'))])) {
    $installer->getConnection()->addIndex(
        $installer->getTable('wishlist/item'),
        $installer->getIdxName('wishlist/item', array('added_at')),
        array('added_at')
    );
}
$tableIndexes = $installer->getConnection()->getIndexList($installer->getTable('log/customer'));
if(!isset($tableIndexes[$installer->getIdxName('log/customer', array('login_at'))])) {
    $installer->getConnection()->addIndex(
        $installer->getTable('log/visitor'),
        $installer->getIdxName('log/visitor', array('store_id')),
        array('store_id')
    );
}
if(!isset($tableIndexes[$installer->getIdxName('log/customer', array('first_visit_at'))])) {
    $installer->getConnection()->addIndex(
        $installer->getTable('log/visitor'),
        $installer->getIdxName('log/visitor', array('first_visit_at')),
        array('first_visit_at')
    );
}

$installer->endSetup();
