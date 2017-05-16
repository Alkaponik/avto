<?php
/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$tableName = $installer->getTable('magedoc/import_retailer_data_base');
$connection = $installer->getConnection();

$table = $connection
    ->newTable($tableName)
    ->addColumn(
        'data_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
        ), 'Raw data primary key'
    )->addColumn('td_art_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Tecdoc Id')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Product Id')
    ->addColumn('card', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Product card')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Product name')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Product code')
    ->addColumn('code_normalized', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Product code normalized')
    ->addColumn('code_raw', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Price product code')
    ->addColumn('model', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Product model')
    ->addColumn('model_normalized', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Product model')
    ->addColumn('cost', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(), 'Product cost')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(), 'Product price')
    ->addColumn('msrp', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(), 'Minimal retailer price')
    ->addColumn('final_price', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(), 'Final price')
    ->addColumn('delivery_days', Varien_Db_Ddl_Table::TYPE_INTEGER, 3, array(), 'Delivery days')
    ->addColumn('currency', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(), 'Currenncy')
    ->addColumn('supplier_id', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(), 'Supplier id')
    ->addColumn(
        'retailer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false,),
        'Retailer_id'
    )
    ->addColumn(
        'manufacturer', Varien_Db_Ddl_Table::TYPE_TEXT, 40, array('nullable' => false,),
        'Product brand'
    )
    ->addColumn('manufacturer_id', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(), 'Product brand id')
    ->addColumn(
        'domestic_stock_qty', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array('nullable' => false,),
        'Domestic stock quantity'
    )
    ->addColumn(
        'general_stock_qty', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array('nullable' => false,),
        'General stock quantity'
    )
    ->addColumn(
        'other_stock_qty', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array('nullable' => false,),
        'Other stock quantity'
    )
    ->addColumn(
        'distant_stock_qty', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array('nullable' => false,),
        'Distant stock quantity'
    )
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array('nullable' => false,), 'Quantity')

    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(), 'Product description')
    ->addColumn(
        'created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
        array(
             'nullable' => false,
             'default'  => Varien_Db_Ddl_Table::TIMESTAMP_INIT,
        ),
        'record created at'
    )
    ->addColumn(
        'updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
        array(
            'nullable' => false,
        ), 'record updated_at'
    )
    ->addIndex($installer->getIdxName($tableName, array('product_id')), array('product_id'))
    ->addIndex($installer->getIdxName($tableName, array('td_art_id')), array('td_art_id'))
    ->addIndex($installer->getIdxName($tableName, array('card')), array('card'))
    ->addIndex($installer->getIdxName($tableName, array('code')), array('code'))
    ->addIndex($installer->getIdxName($tableName, array('code_normalized')), array('code_normalized'))
    ->addIndex($installer->getIdxName($tableName, array('model')), array('model'))
    ->addIndex($installer->getIdxName($tableName, array('model_normalized')), array('model_normalized'))
    ->addIndex($installer->getIdxName($tableName, array('supplier_id')), array('supplier_id'))
    ->addIndex($installer->getIdxName($tableName, array('retailer_id', 'manufacturer')), array('retailer_id', 'manufacturer'))
    ->addIndex($installer->getIdxName($tableName, array('manufacturer')), array('manufacturer'))
    ->addIndex($installer->getIdxName($tableName, array('manufacturer')), array('manufacturer'))
    ->addIndex($installer->getIdxName($tableName, array('retailer_id','manufacturer','code_raw')), array('retailer_id','manufacturer','code_raw'))
    ->addIndex($installer->getIdxName($tableName, array('manufacturer_id')), array('manufacturer_id'));

$installer->getConnection()->createTable($table);

$tableName = $installer->getTable('magedoc/retailer_data_import_adapter_config');
$table = $installer->getConnection()
    ->newTable($tableName)
    ->addColumn(
        'config_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
        ), 'Config primary key'
    )
    ->addColumn(
        'name', Varien_Db_Ddl_Table::TYPE_TEXT, 255,
        array(
            'nullable' => false,
        ), 'Config name'
    )
    ->addColumn(
        'retailer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'unsigned' => true,
            'nullable' => false,
        ), 'Retailer Id'
    )
    ->addColumn(
        'adapter_model', Varien_Db_Ddl_Table::TYPE_TEXT, 255,
        array(
            'nullable' => false,
        ), 'Adapter Model'
    )
    ->addColumn(
        'parser_model', Varien_Db_Ddl_Table::TYPE_TEXT, 255,
        array(
             'nullable' => false,
        ), 'Parser Model'
    )
    ->addColumn(
        'source_adapter_config', Varien_Db_Ddl_Table::TYPE_TEXT, null,
        array(
            'nullable' => true,
        ), 'Source Adapter Configuration'
    )
    ->addColumn(
        'source_fields_map', Varien_Db_Ddl_Table::TYPE_TEXT, null,
        array(
            'nullable' => true,
        ), 'Source fields map'
    )
    ->addColumn(
        'source_fields_filters', Varien_Db_Ddl_Table::TYPE_TEXT, null,
        array(
             'nullable' => true,
        ), 'Source fields filters'
    )
    ->addColumn(
        'starting_record', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
        array(
            'unsigned' => true,
            'nullable' => false,
            'default'  => '1',
        ), 'Starting record'
    )
    ->addColumn(
        'default_qty', Varien_Db_Ddl_Table::TYPE_INTEGER, 4,
        array(
             'unsigned' => true,
             'default'  => '10',
        ), 'Default quantity'
    )
    ->addColumn(
        'compression_type', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Compression type'
    )
    ->addColumn(
        'pricing_model', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Pricing model'
    )
    ->addColumn(
        'discount_percent', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4',
        array(
            'nullable' => true,
            'default'  => null,
            'comment'  => 'Discount percent'
        )
    )
    ->addColumn(
        'vat_percent', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4',
        array(
             'nullable' => true,
             'default'  => null,
             'comment'  => 'VAT percent'
        )
    )
    ->addColumn(
        'code_delimiter', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array('nullable' => false,), 'Product code parsing delimiter'
    )
    ->addColumn(
        'code_part_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false,), 'Product code parsing part count'
    )
    ->addColumn(
        'code_before', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array('nullable' => false,), 'Product code parsing text before code'
    )
    ->addColumn(
        'code_after', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array('nullable' => false,), 'Product code parsing text after code'
    )
    ->addColumn(
        'price_encoding', Varien_Db_Ddl_Table::TYPE_TEXT, 10,
        array(
            'nullable' => false,
            'default'  => 'UTF-8',
            'comment'  => 'Price Encoding'
        )
    )
    ->addIndex(
        $installer->getIdxName($tableName, array('retailer_id')),
        array('retailer_id')
    );

$installer->getConnection()->createTable($table);

$tableName = $installer->getTable('magedoc/supplier_map');

$table = $installer->getConnection()
    ->newTable($tableName)
    ->addColumn(
        'map_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
        ), 'Supplier Map ID'
    )
    ->addColumn(
        'manufacturer', Varien_Db_Ddl_Table::TYPE_TEXT, 40, array(), 'Product brand'
    )
    ->addColumn(
        'supplier_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'nullable' => true,
        ), 'Supplier ID'
    )
    ->addColumn(
        'directory_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'default'  => 1,
            'nullable' => true,
        ), 'Directory ID'
    )
    ->addColumn(
        'retailer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'nullable' => false,
        ), 'Retailer ID'
    )
    ->addColumn(
        'use_crosses', Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array('nullable' => false,'default' => 0,), 'Use crosses'
    )
    ->addColumn(
        'prefix_length', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false,), 'Prefix Length'
    )
    ->addColumn(
        'code_delimiter', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(), 'Code Delimiter'
    )
    ->addColumn(
        'code_part_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('default' => 0,), 'Code part count'
    )
    ->addColumn(
        'suffix_length', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false,), 'Suffix Length'
    )
    ->addColumn(
        'prefix', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array('nullable' => false,), 'Prefix'
    )
    ->addColumn(
        'suffix', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array('nullable' => false,), 'Suffix'
    )
    ->addColumn(
        'alias', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array('nullable' => false,), 'Alias'
    )
    ->addColumn(
        'discount_percent', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4',
        array(
            'nullable' => true,
            'default'  => null,
            'comment'  => 'Discount percent'
        )
    )
    ->addColumn(
        'created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
        array(
            'nullable' => false,
            'default'  => Varien_Db_Ddl_Table::TIMESTAMP_INIT,
        ), 'record created at'
    )
    ->addIndex(
        $installer->getIdxName($tableName, array('manufacturer')),
        array('manufacturer')
    )
    ->addIndex(
        $installer->getIdxName($tableName, array('supplier_id')),
        array('supplier_id')
    )
    ->addIndex(
        $installer->getIdxName($tableName, array('retailer_id', 'manufacturer'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('retailer_id', 'manufacturer'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    );

$installer->getConnection()->createTable($table);


$tableName = $installer->getTable('magedoc/directory');

$table = $installer->getConnection()
    ->newTable($tableName)
    ->addColumn(
        'directory_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
        ), 'Directory ID'
    )
    ->addColumn(
        'version', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(), 'Directory version'
    )
    ->addColumn(
        'name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Directory name'
    )
    ->addColumn(
        'model', Varien_Db_Ddl_Table::TYPE_TEXT, 80, array(), 'Directory model'
    );


$installer->getConnection()->createTable($table);

$retailerTable = $installer->getTable('magedoc/retailer');
$installer->getConnection()->addColumn(
    $retailerTable, 'stock_status',
    array(
         'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
         'length'   => '1',
         'nullable' => false,
         'default'  => 1,
         'comment'  => 'Stock status',
    )
);

$installer->getConnection()->addColumn(
    $retailerTable, 'sort_order',
    array(
         'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
         'length'   => '11',
         'nullable' => false,
         'default'  => 100,
         'comment'  => 'Sort order',
    )
);

$installer->getConnection()->addColumn(
    $retailerTable, 'price_validity_term',
    array(
         'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
         'length'   => '11',
         'nullable' => false,
         'default'  => 168,
         'comment'  => 'Price validity term',
    )
);


$installer->endSetup();
