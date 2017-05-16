<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$entityTables = array(
    'magedoc/quote_inquiry',
    'magedoc/order_inquiry',
    'magedoc/invoice_inquiry',
    'magedoc/shipment_inquiry'
);

foreach ($entityTables as $tableName){
    $installer->getConnection()
        ->addColumn($installer->getTable($tableName), 'code', array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 70,
        'comment' => 'Inquiry Code',
    ));
}

$entityTables = array(
    'magedoc/quote_inquiry',
    'sales/quote_item'
);

foreach ($entityTables as $tableName){
    $installer->getConnection()
        ->addColumn($installer->getTable($tableName), 'supply_status', array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 40,
        'comment' => 'Supply Status',
        'nullable'  => false,
        'default'   => 'unreserved',
    ));

    $installer->getConnection()
        ->addColumn($installer->getTable($tableName), 'qty_supplied', array(
        'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'  => '12,4',
        'comment' => 'Supplied qty',
        'nullable'  => false,
        'default'   => 0,
    ));

    $installer->getConnection()
        ->addColumn($installer->getTable($tableName), 'qty_reserved', array(
        'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'  => '12,4',
        'comment' => 'Reserved qty',
        'nullable'  => false,
        'default'   => 0,
    ));

    $installer->getConnection()
        ->addColumn($installer->getTable($tableName), 'supply_date', array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        'comment' => 'Supply Date',
        'nullable'  => true,
        'default'   => 'NULL',
    ));
}

$installer->endSetup();