<?php
/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();
$connection =  $installer->getConnection();

$directoryOfferLinkTables = array('magedoc/directory_offer_link','magedoc/directory_offer_link_preview');
foreach($directoryOfferLinkTables as $offerTable) {
    $offerLinkTable = $connection
        ->newTable($installer->getTable($offerTable))
        ->addColumn('link_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER, null,
            array(
                 'identity' => true,
                 'unsigned' => true,
                 'nullable' => false,
                 'primary'  => true,
            ), 'Link Id'
        )
        ->addColumn('data_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER, null,
            array(
                'nullable' => false,
            ), 'Data Id'
        )
        ->addColumn('directory_code',
            Varien_Db_Ddl_Table::TYPE_TEXT, 10,
            array(
                 'unsigned' => true,
                 'nullable' => false,
            ), 'Direcotry Id'
        )
        ->addColumn('supplier_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER, null,
            array(
                 'unsigned' => true,
            ), 'Directory supplier id'
        )
        ->addColumn('directory_entity_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER, null,
            array(
                'unsigned' => true,
            ), 'Directory article id'
        )
        ->addColumn('generic_article_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER, null,
            array(
                'unsigned' => true,
            ), 'Directory Generic Article Id'
        )
        ->addIndex(
            $installer->getIdxName($installer->getTable($offerTable), array('data_id')),
            array('data_id')
        )
        ->addIndex(
            $installer->getIdxName($installer->getTable($offerTable), array('directory_code', 'supplier_id'))
            ,array('directory_code', 'supplier_id')
        )
        ->addIndex(
            $installer->getIdxName($installer->getTable($offerTable), array('directory_code', 'generic_article_id'))
            ,array('directory_code', 'generic_article_id')
        )
        ->addIndex(
            $installer->getIdxName($installer->getTable($offerTable), array('directory_code', 'data_id', 'supplier_id')),
            array('directory_code', 'data_id', 'supplier_id')
        )
        ->addIndex(
            $installer->getIdxName($installer->getTable($offerTable), array('directory_code', 'data_id', 'directory_entity_id')),
            array('directory_code', 'data_id', 'directory_entity_id')
        )
        ->addIndex(
            $installer->getIdxName($installer->getTable($offerTable), array('directory_code', 'data_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
            array('directory_code', 'data_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        );

    $connection->createTable($offerLinkTable);
}

$connection->addColumn(
    $installer->getTable('magedoc/supplier_map'),
    'directory_code',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => false,
        'default' => 'tecdoc',
        'length' => 10,
        'comment' => 'Directory code'
    )
);

$connection->update(
    $this->getTable('magedoc/import_retailer_data_base'), array('code_raw' => new Zend_Db_Expr('code'))
);

$connection->addIndex(
    $this->getTable('magedoc/import_retailer_data_base'),
    $connection->getIndexName(
        $this->getTable('magedoc/import_retailer_data_base'),
        array('code_raw', 'manufacturer', 'retailer_id')
    ),
    array('code_raw', 'manufacturer', 'retailer_id')
);

$connection->update(
    $this->getTable('magedoc/retailer_data_import_adapter_config'),
    array( 'source_fields_map' => new Zend_Db_Expr("REPLACE(source_fields_map, 's:4:\"code\"', 's:8:\"code_raw\"')") )
);

$installer->endSetup();