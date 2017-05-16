<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$salesSetup = new Mage_Sales_Model_Resource_Setup('sales_setup');

$salesSetup->addAttribute('order', 'last_supply_status', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'length'    => 40,
    'default'   => NULL,
    'grid'      => true));

$salesSetup->addAttribute('order', 'supply_date', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable'  => 'true',
    'default'   => 'NULL',
    'grid'      => true));

$salesSetup->addAttribute('order', 'shipping_date', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable'  => 'true',
    'default'   => 'NULL',
    'grid'      => true));

$salesSetup->addAttribute('order', 'last_status_history_comment', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'default'   => '',
    'grid'      => true));

$connection->addIndex(
    $installer->getTable('sales/order_grid'),
    $installer->getIdxName(
        'sales/order_grid',
        array('supply_date')
    ),
    array('supply_date'));

$connection->addIndex(
    $installer->getTable('sales/order_grid'),
    $installer->getIdxName(
        'sales/order_grid',
        array('shipping_date')
    ),
    array('shipping_date'));

$installer->endSetup();