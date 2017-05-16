<?php
/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();
$connection = $installer->getConnection();

$gaTable = 'magedoc/generic_article_map';
$genericArticleTable = $connection
    ->newTable($installer->getTable($gaTable))
    ->addColumn('ga_map_id',
    Varien_Db_Ddl_Table::TYPE_INTEGER, null,
    array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
    ), 'Generic Article Map Id'
)
    ->addColumn('name',
    Varien_Db_Ddl_Table::TYPE_TEXT, 255,
    array(
        'nullable' => false,
    ), 'Name'
)
    ->addColumn('name_normalzied',
    Varien_Db_Ddl_Table::TYPE_TEXT, 255,
    array(
        'nullable' => false,
    ), 'Normalized Name'
)
    ->addColumn('retailer_id',
    Varien_Db_Ddl_Table::TYPE_INTEGER, null,
    array(
        'unsigned' => true,
        'default'  => '0'
    ), 'Retailer Id'
)
    ->addColumn('directory_code',
    Varien_Db_Ddl_Table::TYPE_TEXT, 10,
    array(
        'nullable' => false,
        'default'  => 'tecdoc'
    ), 'Direcotry Code'
)
    ->addColumn('generic_article_id',
    Varien_Db_Ddl_Table::TYPE_INTEGER, null,
    array(
        'unsigned' => true,
    ), 'Generic Article Id'
)
    ->addColumn('title',
    Varien_Db_Ddl_Table::TYPE_TEXT, 255,
    array(
    ), 'Generic Article Title'
)
    ->addColumn('frequency',
    Varien_Db_Ddl_Table::TYPE_INTEGER, null,
    array(
        'unsigned' => true,
    ), 'Frequency'
)
    ->addColumn('status',
    Varien_Db_Ddl_Table::TYPE_TINYINT, null,
    array(
        'unsigned' => true,
    ), 'status'
)
    ->addIndex(
    $installer->getIdxName($installer->getTable($gaTable), array('generic_article_id')),
    array('generic_article_id')
)
    ->addIndex(
    $installer->getIdxName($installer->getTable($gaTable), array('status')),
    array('status')
)
    ->addIndex(
    $installer->getIdxName($installer->getTable($gaTable), array('name', 'retailer_id', 'directory_code', 'generic_article_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
    array('name', 'retailer_id', 'directory_code', 'generic_article_id'),
    array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
);

$connection->createTable($genericArticleTable);

$importRetailerDataTable = $this->getTable('magedoc/import_retailer_data');
$connection->addIndex(
    $importRetailerDataTable,
    $installer->getIdxName($importRetailerDataTable, array('name' )),
    array('name' )
);

$installer->endSetup();