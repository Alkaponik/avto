<?php
/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order'), 'shipping_date', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        'nullable' => true,
        'default' => null,
        'comment' => 'Shipping Date',
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_grid'), 'shipping_date', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        'nullable' => true,
        'default' => null,
        'comment' => 'Shipping Date',
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/quote'), 'shipping_date', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        'nullable' => true,
        'default' => null,
        'comment' => 'Shipping Date',
    ));
$installer->endSetup();
