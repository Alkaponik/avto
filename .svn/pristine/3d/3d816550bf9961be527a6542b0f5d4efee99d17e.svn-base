<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$connection = $installer->getConnection();

$searchTreeTableName = $installer->getTable('magedoc/searchTree');

$installer->getConnection()
    ->addColumn($searchTreeTableName, 'category_id', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable'  => true,
        'length'    => 11,
        'comment'   => 'Category Id'
    ));

$installer->getConnection()
    ->addColumn($searchTreeTableName, 'name', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable'  => false,
        'length'    => 255,
        'comment'   => 'Name',
    ));

$installer->getConnection()
    ->addColumn($searchTreeTableName, 'is_enabled', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable'  => false,
        'length'    => 1,
        'comment'   => 'Is enabled',
        'default'   => 0,
    ));

$installer->getConnection()
    ->addColumn($searchTreeTableName, 'is_import_enabled', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable'  => true,
        'length'    => 1,
        'comment'   => 'Is import enabled',
        'default'   => 0,
    ));

$connection->addIndex($searchTreeTableName, $connection->getIndexName($searchTreeTableName, 'path', Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX), 'path', Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX);
$connection->addIndex($searchTreeTableName, $connection->getIndexName($searchTreeTableName, 'category_id', Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX), 'category_id', Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX);
$connection->addIndex($searchTreeTableName, $connection->getIndexName($searchTreeTableName, 'is_enabled', Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX), 'is_enabled', Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX);
$connection->addIndex($searchTreeTableName, $connection->getIndexName($searchTreeTableName, 'is_import_enabled', Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX), 'is_import_enabled', Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX);

$installer->endSetup();