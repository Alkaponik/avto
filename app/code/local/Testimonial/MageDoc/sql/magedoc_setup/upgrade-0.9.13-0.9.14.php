<?php
/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('magedoc/customer_vehicle'))
    ->addColumn('vehicle_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Vehicle Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
    ), 'Customer Id')
    ->addColumn('manufacturer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Manufacturer Id')
    ->addColumn('model_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Model Id')
    ->addColumn('type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Type Id')
    ->addColumn('production_start_year', Varien_Db_Ddl_Table::TYPE_TEXT, 4, array(
        'nullable'  => false,
    ), 'Production Start Year')
    ->addColumn('manufacturer', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
        'nullable'  => false,
    ), 'Manufacturer Name')
    ->addColumn('model', Varien_Db_Ddl_Table::TYPE_TEXT, 80, array(
        'nullable'  => false,
    ), 'Model Name')
    ->addColumn('type', Varien_Db_Ddl_Table::TYPE_TEXT, 120, array(
        'nullable'  => false,
    ), 'Type Text')
    ->addColumn('mileage', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Mileage')
    ->addColumn('vin', Varien_Db_Ddl_Table::TYPE_TEXT, 17, array(
        'nullable'  => false,
    ), 'VIN CODE')
    ->addIndex($installer->getIdxName('magedoc/quote_vehicle', array('customer_id')),
        array('customer_id'))
    ->addForeignKey($installer->getFkName('magedoc/customer_vehicle', 'customer_id', 'customer/entity', 'entity_id'),
        'customer_id', $installer->getTable('customer/entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Magedoc Customer Vehicle');

if (!$installer->getConnection()->isTableExists($installer->getTable('magedoc/customer_vehicle'))) {
    $installer->getConnection()->createTable($table);
}

$installer->endSetup();


$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('magedoc/quote_vehicle'), 'mileage', array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => false,
        'comment' => 'Mileage',
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('magedoc/quote_vehicle'), 'vin', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    =>  17,
        'nullable' => false,
        'comment' => 'VIN CODE',
    ));

$installer->getConnection()
    ->addColumn($installer->getTable('magedoc/order_vehicle'), 'customer_vehicle_id', array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'comment' => 'Customer Vehicle Id',
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('magedoc/order_vehicle'), 'mileage', array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => false,
        'comment' => 'Mileage',
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('magedoc/order_vehicle'), 'vin', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    =>  17,
        'nullable' => false,
        'comment' => 'VIN CODE',
    ));


$installer->endSetup();
