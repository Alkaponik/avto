<?php
/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();
$connection =  $installer->getConnection();

$table = $connection
    ->newTable($this->getTable('magedoc/import_retailer_data_extended'))
    ->addColumn('data_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
             'identity' => true,
             'unsigned' => true,
             'nullable' => false,
             'primary'  => true,
        ), 'Link Id'
    )->addColumn('data',
        Varien_Db_Ddl_Table::TYPE_TEXT, null,
        array(
             'unsigned' => true,
             'nullable' => false,
        ), 'Direcotry Id'
    );

$connection->createTable($table);

$table = $connection->createTableByDdl(
    $this->getTable('magedoc/import_retailer_data_extended'),
    $this->getTable('magedoc/import_retailer_data_extended_base')
);
$connection->createTable($table);

$table = $connection->createTableByDdl(
    $this->getTable('magedoc/import_retailer_data_extended'),
    $this->getTable('magedoc/import_retailer_data_extended_preview')
);
$connection->createTable($table);



$supplierMapTable = $this->getTable('magedoc/supplier_map');
$oldIdxName =
    $installer->getIdxName(
        $supplierMapTable,
        array('retailer_id', 'manufacturer')
    );

$installer->getConnection()->dropIndex($supplierMapTable, $oldIdxName);

$newIdxName =
    $installer->getIdxName(
        $supplierMapTable,
        array('retailer_id', 'manufacturer', 'directory_code'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    );

$installer->getConnection()->addIndex(
    $supplierMapTable,
    $newIdxName,
    array('retailer_id', 'manufacturer', 'directory_code'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);
$installer->endSetup();