<?php
/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();
$connection = $installer->getConnection();

$table = $installer->getTable('admin/role');

$connection->addColumn(
    $table,
    'visible_order_statuses',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 255,
        'comment' => 'Visible Order Statuses'
    )
);

$connection->addColumn(
    $table,
    'visible_order_supply_statuses',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 255,
        'comment' => 'Visible Order Supply Satuses'
    )
);

$installer->endSetup();