<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();

$table = $installer->getTable('catalog/product');

$setup = new Mage_Customer_Model_Resource_Setup('catalog_setup');

$setup->addAttribute('catalog_product', 'code', array(
    'type'              => 'static',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Code',
    'input'             => 'text',
    'class'             => '',
    'source'            => '',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'used_in_product_listing' => false,
    'group'             => 'MageDoc',
));

$connection->addColumn($table, 'code', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 255,
        'comment' => 'Code',
        'nullable'=> false
    ));

$productIndexTableAlias = 'directory_catalog/product_index';
$productIndexTable = $connection
    ->newTable($installer->getTable($productIndexTableAlias))
    ->addColumn('product_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Product Id'
    )
    ->addColumn('store_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'unsigned' => true,
        ), 'Store Id'
    )
    ->addColumn('manufacturer_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'unsigned' => true,
        ), 'Manufacturer Id'
    )
    ->addColumn('generic_article_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'unsigned' => true,
        ), 'Generic Article Id'
    )
    ->addColumn('code_normalized',
        Varien_Db_Ddl_Table::TYPE_TEXT, 255,
        array(
        ), 'Code Normalized'
    )
    ->addColumn('model_normalized',
        Varien_Db_Ddl_Table::TYPE_TEXT, 255,
        array(
        ), 'Model Normalized'
    )
    ->addIndex(
        $installer->getIdxName($installer->getTable($productIndexTableAlias), array('generic_article_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX),
        array('generic_article_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
    )
    ->addIndex(
        $installer->getIdxName($installer->getTable($productIndexTableAlias), array('manufacturer_id', 'code_normalized', 'product_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX),
        array('manufacturer_id', 'code_normalized', 'product_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
    )
    ->addIndex(
        $installer->getIdxName($installer->getTable($productIndexTableAlias), array('manufacturer_id', 'model_normalized', 'product_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX),
        array('manufacturer_id', 'model_normalized', 'product_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
    );

$connection->createTable($productIndexTable);

$installer->endSetup();