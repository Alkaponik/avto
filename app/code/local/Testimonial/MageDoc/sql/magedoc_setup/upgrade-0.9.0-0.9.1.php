<?php
/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$tableName = $installer->getTable('magedoc/retailer_data_import_session');
$connection = $installer->getConnection();

$table = $connection
    ->newTable($tableName)
    ->addColumn(
        'session_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
        ), 'Session ID'
    )
    ->addColumn(
        'retailer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'nullable' => false,
        ),
        'Session Retailer ID'
    )
    ->addColumn(
        'config_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'nullable' => false,
        ), 'Retailer Adapter Config Id'
    )
    ->addColumn(
        'total_records', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false,),
        'Number of records, inserted from price to base table'
    )
    ->addColumn(
        'valid_records', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false,),
        'Number of valid records, inserted from price to base table'
    )
    ->addColumn(
        'records_with_old_brands', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false,),
        'Price records linked to supplier map'
    )
    ->addColumn(
        'records_linked_to_directory', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false,),
        'Price records linked to directory'
    )
    ->addColumn(
        'total_brands', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false),
        'Total brands'
    )
    ->addColumn(
        'old_brands', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false,),
        'Brands linked to supplier map'
    )

    ->addColumn(
        'new_brands', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false,),
        'Brands not found in the supplier map'
    )
    ->addColumn(
        'imported_brands', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false,), 'Brands added to supplier map'
    )
    ->addColumn('price_filename', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Price filename')
    ->addColumn(
        'status_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 4,
        array(
            'default' => 1
        ), 'Session status'
    )
    ->addColumn(
        'created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
        array(
            'nullable' => false,
            'default'  => Varien_Db_Ddl_Table::TIMESTAMP_INIT,
        ), 'record created at'
    )
    ->addColumn(
        'updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
        array(
            'nullable' => true,
        ), 'record updated at'
    )
    ->addIndex(
        $installer->getIdxName($tableName, array('retailer_id')),
        array('retailer_id')
    )->addIndex(
        $installer->getIdxName($tableName, array('config_id')),
        array('config_id')
    )->addIndex(
        $installer->getIdxName($tableName, array('status_id')),
        array('status_id')
    );


$connection->createTable( $table );


$table = $connection->createTableByDdl(
    $installer->getTable('magedoc/import_retailer_data'),
    $installer->getTable('magedoc/import_retailer_data_preview')
);

$connection->createTable($table);

$installer->endSetup();