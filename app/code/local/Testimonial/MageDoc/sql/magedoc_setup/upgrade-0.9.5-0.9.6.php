<?php
/* @var Mage_Core_Model_Resource_Setup $installer */

$installer = $this;

$installer->startSetup();

$tableName = $installer->getTable('magedoc/retailer_data_import_session_source');

$table = $installer->getConnection()
    ->newTable($tableName)
    ->addColumn(
        'source_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
             'identity' => true,
             'unsigned' => true,
             'nullable' => false,
             'primary'  => true,
        ), 'Source Id'
    )
    ->addColumn('source_path', Varien_Db_Ddl_Table::TYPE_TEXT, null,
        array(), 'Source Path'
    )
    ->addColumn('session_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
             'unsigned' => true,
             'nullable' => false,
        ), 'Session Id'
    )
    ->addColumn('config_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
             'unsigned' => true,
             'nullable' => false,
        ), 'Config Id'
    )
    ->addIndex( $installer->getIdxName($tableName, array('session_id')), array('session_id') )
    ->addIndex( $installer->getIdxName($tableName, array('config_id')), array('config_id') );

$installer->getConnection()->createTable($table);

// adding source_id field to few tables in the loop
$importRetailerDataTables =
    array('magedoc/import_retailer_data', 'magedoc/import_retailer_data_base', 'magedoc/import_retailer_data_preview' );

$connection = $installer->getConnection();
foreach($importRetailerDataTables as $tableConfigName) {
    $tableName = $installer->getTable($tableConfigName);
    $connection->addColumn(
        $tableName, 'source_id',
        array(
             'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
             'unsigned' => true,
             'comment'  => 'Source Id',
        )
    );
    $connection->addIndex($tableName,$installer->getIdxName($tableName, array('source_id')), array('source_id'));
}

$installer->endSetup();