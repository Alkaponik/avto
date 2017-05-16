<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$table = $installer->getTable('sales/order_aggregated_created');
$connection->dropIndex($table, 'UNQ_SALES_ORDER_AGGREGATED_CREATED_PERIOD_STORE_ID_ORDER_STATUS');

$connection->addIndex(
    $table,
    $installer->getIdxName(
        'sales/order_aggregated_created',
        array('period', 'store_id', 'order_status', 'manager_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('period', 'store_id', 'order_status', 'manager_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE);

$table = $installer->getTable('sales/order_aggregated_updated');
$connection->dropIndex($table, 'UNQ_SALES_ORDER_AGGREGATED_UPDATED_PERIOD_STORE_ID_ORDER_STATUS');

$connection->addIndex(
    $table,
    $installer->getIdxName(
        'sales/order_aggregated_updated',
        array('period', 'store_id', 'order_status', 'manager_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('period', 'store_id', 'order_status', 'manager_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE);

$salesSetup = new Mage_Sales_Model_Resource_Setup('sales_setup');

$connection->addColumn($this->getTable('sales/order_grid'), 'shipping_carrier',
    array(
        'type'      =>  Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    =>  255,
        'comment'   => 'Shipping Carrier'
    ));

$connection->addIndex(
    $installer->getTable('sales/order_grid'),
    $installer->getIdxName(
        'sales/order_grid',
        array('shipping_carrier')
    ),
    array('shipping_carrier'));

$connection->addColumn($this->getTable('sales/order_grid'), 'shipping_method',
    array(
        'type'      =>  Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    =>  255,
        'comment'   => 'Shipping Method'
    ));

$connection->addIndex(
    $installer->getTable('sales/order_grid'),
    $installer->getIdxName(
        'sales/order_grid',
        array('shipping_method')
    ),
    array('shipping_method'));

$connection->addColumn($this->getTable('sales/order_grid'), 'payment_method',
    array(
        'type'      =>  Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    =>  255,
        'comment'   => 'Payment Method'
    ));
;

$connection->addIndex(
    $installer->getTable('sales/order_grid'),
    $installer->getIdxName(
        'sales/order_grid',
        array('payment_method')
    ),
    array('payment_method'));

$installer->endSetup();