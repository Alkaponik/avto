<?php
/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$tableName = $installer->getTable('magedoc/retailer_data_import_adapter_config');

$installer->getConnection()->addColumn(
    $tableName, 'update_by_key',
    array(
         'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
         'length'   => '2',
         'nullable' => false,
         'default'  => 0,
         'comment'  => 'Update By Key',
    )
);

$installer->getConnection()->addIndex(
    $tableName,
    $installer->getIdxName($tableName, array('update_by_key')),
    array('update_by_key'));

$retailerTable = $installer->getTable('magedoc/retailer');
$installer->getConnection()->addColumn(
    $retailerTable, 'show_on_frontend',
    array(
         'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
         'length'   => '1',
         'nullable' => false,
         'default'  => 0,
         'comment'  => 'Display on frontend',
    )
);


$installer->endSetup();