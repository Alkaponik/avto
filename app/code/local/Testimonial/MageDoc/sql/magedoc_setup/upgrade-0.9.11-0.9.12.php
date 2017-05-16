<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$columns = array(
    'supply_status' => 'Supply Status',
    'shipping_method' => 'Shipping Method',
    'payment_method' => 'Payment Method');

foreach ($columns as $column => $comment) {

    $installer->getConnection()->addColumn($installer->getTable('sales/order_aggregated_created'), $column, array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 255,
        'comment' => $comment,
        'nullable'=> false
    ));

    $installer->getConnection()->addColumn($installer->getTable('sales/order_aggregated_updated'), $column, array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 255,
        'comment' => $comment,
        'nullable'=> false
    ));

    $table = $installer->getTable('sales/order_aggregated_created');

    $connection->addIndex(
        $table,
        $installer->getIdxName(
            'sales/order_aggregated_created',
            array($column),
            Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
        ),
        array($column), Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX);

    $table = $installer->getTable('sales/order_aggregated_updated');

    $connection->addIndex(
        $table,
        $installer->getIdxName(
            'sales/order_aggregated_updated',
            array($column),
            Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
        ),
        array($column), Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX);
}

$table = $installer->getTable('sales/order_aggregated_created');
$connection->dropIndex($table, $installer->getIdxName(
    'sales/order_aggregated_created',
    array('period', 'store_id', 'order_status', 'manager_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
));

$connection->addIndex(
    $table,
    $installer->getIdxName(
        'sales/order_aggregated_created',
        array('period', 'store_id', 'order_status', 'manager_id', 'supply_status', 'shipping_method', 'payment_method'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('period', 'store_id', 'order_status', 'manager_id', 'supply_status', 'shipping_method', 'payment_method'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE);

$table = $installer->getTable('sales/order_aggregated_updated');
$connection->dropIndex($table, $installer->getIdxName(
    'sales/order_aggregated_updated',
    array('period', 'store_id', 'order_status', 'manager_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
));

$connection->addIndex(
    $table,
    $installer->getIdxName(
        'sales/order_aggregated_updated',
        array('period', 'store_id', 'order_status', 'manager_id', 'supply_status', 'shipping_method', 'payment_method'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('period', 'store_id', 'order_status', 'manager_id', 'supply_status', 'shipping_method', 'payment_method'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE);

$installer->endSetup();