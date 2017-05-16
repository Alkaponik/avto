<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_status_history'), 'is_visible_on_printables', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'unsigned'  => true,
    'nullable'  => false,
    'default'   => '0',
    'comment'   => 'Is Visible On Printables'
));

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_status_history'), 'supply_status', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'  => 40,
    'nullable'  => false,
    'default'   => 'pending',
    'comment'   => 'Supply Status'
));

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_status_history'), 'status_change_reason', array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 40,
        'nullable'  => true,
        'default'   => null,
        'comment'   => 'Order Status Change Reason'
    ));

$installer->endSetup();