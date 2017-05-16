<?php
/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$tableName = $installer->getTable('magedoc/retailer_data_import_source_config');
$connection = $installer->getConnection();

$table = $connection
    ->newTable($tableName)
    ->addColumn(
        'source_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
        ), 'Source primary key'
    )
    ->addColumn(
        'name', Varien_Db_Ddl_Table::TYPE_TEXT, 255,
        array(
            'nullable' => false,
        ), 'Source name'
    )
    ->addColumn(
        'retailer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'unsigned' => true,
            'nullable' => false,
        ), 'Retailer Id'
    )
    ->addColumn(
        'source_type', Varien_Db_Ddl_Table::TYPE_TEXT, 255,
        array(
            'nullable' => false,
        ), 'Source type'
    )
    ->addColumn(
        'source_settings', Varien_Db_Ddl_Table::TYPE_TEXT, null,
        array(
            'nullable' => false,
        ), 'Source Settings'
    )

    ->addIndex(
        $installer->getIdxName($tableName, array('retailer_id')),
        array('retailer_id')
    );

$connection->createTable($table);
$installer->endSetup();


$installer->startSetup();
$tableName = $installer->getTable('magedoc/retailer_data_import_session');

$connection->addColumn(
    $tableName,
    'messages',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_BLOB,
        'length' => '64K',
        'nullable' => true,
        'default' => null,
        'comment' => 'Messages'
    )
);
$installer->endSetup();

$tableName = $installer->getTable('magedoc/retailer_data_import_settings_rule');
$connection = $installer->getConnection();

$table = $connection
    ->newTable($tableName)
    ->addColumn(
        'rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
        ), 'Rule primary key'
    )
    ->addColumn(
        'retailer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'unsigned' => true,
            'nullable' => false,
        ), 'Retailer Id'
    )
    ->addColumn(
        'conditions_serialized', Varien_Db_Ddl_Table::TYPE_TEXT, '2M',
        array(), 'Conditions Serialized'
    )
    ->addIndex(
        $installer->getIdxName($tableName, array('retailer_id')),
        array('retailer_id')
    );

$connection->createTable($table);
$installer->endSetup();
