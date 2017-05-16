<?php

$installer = $this;

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$salesSetup = new Mage_Sales_Model_Resource_Setup('sales_setup');
$conn = $installer->getConnection();


$setup->addAttribute('customer', 'vehicle', array(
    'group'                     => 'My vehicle',
    'type'                      => 'static',
    'backend'                   => '',
    'frontend'                  => '',
    'label'                     => 'Choose your vehicle',
    'visible'                   => true,
    'required'                  => false,
    'user_defined'              => false,
));

$tableName = $installer->getTable('customer/entity');
$conn->addColumn($tableName, 'vehicle', 'INT(11)');
$conn->addKey($tableName, 'IDX_VEHICLE', 'vehicle');

$installer->run("	 
    CREATE TABLE IF NOT EXISTS  " . $this->getTable('magedoc/type_product') . " (
        `product_id` INT(11) NOT NULL,
        `type_id` INT(11) NOT NULL,
        PRIMARY KEY (`product_id`,`type_id`),
        KEY `IDX_PRODUCT_ID` (`product_id`),
        KEY `IDX_TYPE_ID` (`type_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$setup->addAttribute('catalog_product', 'retailer_id', array(
    'group'                     => 'MageDoc',
    'type'                      => 'static',
    'backend'                   => '',
    'frontend'                  => '',
    'label'                     => 'Retailer',
    'input'                     => 'select',
    'source'                    => 'magedoc/source_retailer',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'                   => true,
    'required'                  => false,
    'user_defined'              => false,
    'searchable'                => false,
    'filterable'                => true,
    'comparable'                => false,
    'default'                   => '0'
));

$tableName = $installer->getTable('catalog/product');
$conn->addColumn($tableName, 'retailer_id', 'INT(11) unsigned NULL DEFAULT NULL');
$conn->addKey($tableName, 'IDX_RETAILER_ID', 'retailer_id');


$installer->run("	 
    CREATE TABLE IF NOT EXISTS " . $this->getTable('magedoc/order_vehicle') . "(
  `vehicle_id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `manufacturer_id` INT(11) DEFAULT NULL,
  `production_start_year` VARCHAR(4) NOT NULL,
  `model_id` INT(11) DEFAULT NULL,
  `type_id` INT(11) DEFAULT NULL,
  `manufacturer` VARCHAR(60) NOT NULL,
  `model` VARCHAR(100) NOT NULL,
  `type` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`vehicle_id`),
  KEY `IDX_ORDER_ID` (`order_id`),
  KEY `IDX_MANUFACTURER_ID` (`manufacturer_id`),
  KEY `IDX_MODEL_ID` (`model_id`),
  KEY `IDX_TYPE_ID` (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
");

$salesSetup->addAttribute('order', 'manager_id', array(
    'type'      => 'int',
    'default'   => 0,
    'grid'      => true));
$conn->addKey($installer->getTable('sales/order'), 'IDX_MANAGER_ID', 'manager_id');
$conn->addKey($installer->getTable('sales/order_grid'), 'IDX_MANAGER_ID', 'manager_id');

$salesSetup->addAttribute('order', 'telephone', array(
    'type'      => 'varchar',
    'grid'      => true));
$conn->addKey($installer->getTable('sales/order'), 'IDX_TELEPHONE_ID', 'telephone');
$conn->addKey($installer->getTable('sales/order_grid'), 'IDX_TELEPHONE_ID', 'telephone');


$salesSetup->addAttribute('quote_item', 'retailer_id', array('type' => 'int'));
$salesSetup->addAttribute('quote_item', 'retailer', array('type' => 'varchar'));
$salesSetup->addAttribute('quote_item', 'cost', array('type' => 'decimal'));
$tableName = $installer->getTable('sales/quote_item');
$conn->addKey($tableName, 'IDX_RETAILER_ID', 'retailer_id');

$salesSetup->addAttribute('order_item', 'retailer_id', array('type' => 'int'));
$salesSetup->addAttribute('order_item', 'retailer', array('type' => 'varchar'));
$salesSetup->addAttribute('order_item', 'cost', array('type' => 'decimal'));
$tableName = $installer->getTable('sales/order_item');
$conn->addKey($tableName, 'IDX_RETAILER_ID', 'retailer_id');

$table = $installer->getConnection()
    ->newTable($installer->getTable('magedoc/order_inquiry'))
    ->addColumn('inquiry_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Inquiry Id')
    ->addColumn('vehicle_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Vehicle Id')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
        'default'   => '0',
        ), 'Order Id')
    ->addColumn('parent_inquiry_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Parent inquiry Id')
    ->addColumn('quote_inquiry_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Quote inquiry Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Updated At')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Product Id')
    ->addColumn('product_type', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Product Type')
    ->addColumn('product_options', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Product Options')
    ->addColumn('weight', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Weight')
    ->addColumn('is_virtual', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Is Virtual')
    ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Sku')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Name')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Description')
    ->addColumn('applied_rule_ids', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Applied Rule Ids')
    ->addColumn('additional_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Additional Data')
    ->addColumn('free_shipping', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Free Shipping')
    ->addColumn('is_qty_decimal', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Is Qty Decimal')
    ->addColumn('no_discount', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'No Discount')
    ->addColumn('qty_backordered', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Qty Backordered')
    ->addColumn('qty_canceled', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Qty Canceled')
    ->addColumn('qty_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Qty Invoiced')
    ->addColumn('qty_ordered', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Qty Ordered')
    ->addColumn('qty_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Qty Refunded')
    ->addColumn('qty_shipped', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Qty Shipped')
    ->addColumn('base_cost', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Base Cost')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Price')
    ->addColumn('base_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Base Price')
    ->addColumn('original_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Original Price')
    ->addColumn('base_original_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Original Price')
    ->addColumn('tax_percent', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Tax Percent')
    ->addColumn('tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Tax Amount')
    ->addColumn('base_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Base Tax Amount')
    ->addColumn('tax_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Tax Invoiced')
    ->addColumn('base_tax_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Base Tax Invoiced')
    ->addColumn('discount_percent', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Discount Percent')
    ->addColumn('discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Discount Amount')
    ->addColumn('base_discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Base Discount Amount')
    ->addColumn('discount_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Discount Invoiced')
    ->addColumn('base_discount_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Base Discount Invoiced')
    ->addColumn('amount_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Amount Refunded')
    ->addColumn('base_amount_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Base Amount Refunded')
    ->addColumn('row_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Row Total')
    ->addColumn('base_row_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Base Row Total')
    ->addColumn('row_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Row Invoiced')
    ->addColumn('base_row_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Base Row Invoiced')
    ->addColumn('row_weight', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Row Weight')
    ->addColumn('base_tax_before_discount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Tax Before Discount')
    ->addColumn('tax_before_discount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tax Before Discount')
    ->addColumn('ext_order_inquiry_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Ext Order inquiry Id')
    ->addColumn('locked_do_invoice', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Locked Do Invoice')
    ->addColumn('locked_do_ship', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Locked Do Ship')
    ->addColumn('price_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Price Incl Tax')
    ->addColumn('base_price_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Price Incl Tax')
    ->addColumn('row_total_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Row Total Incl Tax')
    ->addColumn('base_row_total_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Row Total Incl Tax')
    ->addColumn('hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Hidden Tax Amount')
    ->addColumn('base_hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Hidden Tax Amount')
    ->addColumn('hidden_tax_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Hidden Tax Invoiced')
    ->addColumn('base_hidden_tax_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Hidden Tax Invoiced')
    ->addColumn('hidden_tax_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Hidden Tax Refunded')
    ->addColumn('base_hidden_tax_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Hidden Tax Refunded')
    ->addColumn('is_nominal', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Nominal')
    ->addColumn('tax_canceled', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tax Canceled')
    ->addColumn('hidden_tax_canceled', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Hidden Tax Canceled')
    ->addColumn('tax_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tax Refunded')
    ->addColumn('category', Varien_Db_Ddl_Table::TYPE_TEXT, 80, array(
        'nullable'  => true,
        ), 'Category Name')
    ->addColumn('supplier', Varien_Db_Ddl_Table::TYPE_TEXT, 80, array(
        'nullable'  => true,
        ), 'Supplier Brand Name')
    ->addColumn('retailer', Varien_Db_Ddl_Table::TYPE_TEXT, 40, array(
        'nullable'  => true,
        ), 'Retailer Name')
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
        ), 'Category Id')
    ->addColumn('supplier_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
        ), 'Supplier Id')
    ->addColumn('article_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
        ), 'Article Id')
    ->addColumn('retailer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
        ), 'Retailer Id')
    ->addColumn('cost', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Cost')
    ->addColumn('subtotal', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'subtotal')
    ->addColumn('row_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'row_total')
    ->addColumn('supply_status', Varien_Db_Ddl_Table::TYPE_VARCHAR, 40, array(
        'nullable'  => false,
        'default'   => 'unreserved',
        ), 'Supply Status')
    ->addColumn('qty_supplied', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => 0,
        ), 'Supplied qty')
    ->addColumn('qty_reserved', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => 0,
        ), 'Reserved qty')
    ->addColumn('supply_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
        'default'   => 'NULL',
        ), 'Supply Date')
    ->addIndex($installer->getIdxName('magedoc/order_inquiry', array('vehicle_id')),
        array('vehicle_id'))
    ->addIndex($installer->getIdxName('magedoc/order_inquiry', array('order_id')),
        array('order_id'))
    ->addIndex($installer->getIdxName('magedoc/order_inquiry', array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName('magedoc/order_inquiry', array('supply_status')),
        array('supply_status'))
    ->addIndex($installer->getIdxName('magedoc/order_inquiry', array('supply_date')),
        array('supply_date'))
    ->addForeignKey($installer->getFkName('magedoc/order_inquiry', 'order_id', 'sales/order', 'entity_id'),
        'order_id', $installer->getTable('sales/order'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('magedoc/order_inquiry', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Order inquiry');
if(!$installer->getConnection()->isTableExists($installer->getTable('magedoc/order_inquiry'))){
    $installer->getConnection()->createTable($table);
}

            
$table = $installer->getConnection()
    ->newTable($installer->getTable('magedoc/quote_inquiry'))
    ->addColumn('inquiry_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Inquiry Id')
    ->addColumn('parent_inquiry_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
        ), 'Parent Inquiry Id')
    ->addColumn('quote_vehicle_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
        ), 'Quote Vehicle Id')
    ->addColumn('quote_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Quote Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Updated At')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Product Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('parent_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Parent Item Id')
    ->addColumn('is_virtual', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Is Virtual')
    ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Sku')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Name')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Description')
    ->addColumn('applied_rule_ids', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Applied Rule Ids')
    ->addColumn('additional_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Additional Data')
    ->addColumn('free_shipping', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Free Shipping')
    ->addColumn('is_qty_decimal', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Is Qty Decimal')
    ->addColumn('no_discount', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'No Discount')
    ->addColumn('weight', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Weight')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Qty')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Price')
    ->addColumn('base_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Base Price')
    ->addColumn('custom_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Custom Price')
    ->addColumn('discount_percent', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Discount Percent')
    ->addColumn('discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Discount Amount')
    ->addColumn('base_discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Base Discount Amount')
    ->addColumn('tax_percent', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Tax Percent')
    ->addColumn('tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Tax Amount')
    ->addColumn('base_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Base Tax Amount')
    ->addColumn('row_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Row Total')
    ->addColumn('base_row_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Base Row Total')
    ->addColumn('row_total_with_discount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Row Total With Discount')
    ->addColumn('row_weight', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Row Weight')
    ->addColumn('product_type', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Product Type')
    ->addColumn('base_tax_before_discount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Tax Before Discount')
    ->addColumn('tax_before_discount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tax Before Discount')
    ->addColumn('original_custom_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Original Custom Price')
    ->addColumn('redirect_url', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Redirect Url')
    ->addColumn('base_cost', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Cost')
    ->addColumn('price_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Price Incl Tax')
    ->addColumn('base_price_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Price Incl Tax')
    ->addColumn('row_total_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Row Total Incl Tax')
    ->addColumn('base_row_total_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Row Total Incl Tax')
    ->addColumn('hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Hidden Tax Amount')
    ->addColumn('base_hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Hidden Tax Amount')
    ->addColumn('cost', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default' => '0.0000'
        ), 'Cost')
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
        ), 'Category Id')
    ->addColumn('supplier_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
        ), 'Supplier Id')
    ->addColumn('article_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
        ), 'Article Id')
    ->addColumn('retailer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
        ), 'Retailer Id')
    ->addColumn('category', Varien_Db_Ddl_Table::TYPE_TEXT, 80, array(
        'nullable'  => true,
        ), 'Category Name')
    ->addColumn('supplier', Varien_Db_Ddl_Table::TYPE_TEXT, 80, array(
        'nullable'  => true,
        ), 'Supplier Brand Name')
    ->addColumn('retailer', Varien_Db_Ddl_Table::TYPE_TEXT, 40, array(
        'nullable'  => true,
        ), 'Retailer Brand Name')
    ->addIndex($installer->getIdxName('magedoc/quote_inquiry', array('parent_inquiry_id')),
        array('parent_inquiry_id'))
    ->addIndex($installer->getIdxName('magedoc/quote_inquiry', array('quote_vehicle_id')),
        array('quote_vehicle_id'))
    ->addIndex($installer->getIdxName('magedoc/quote_inquiry', array('quote_id')),
        array('quote_id'))
    ->addIndex($installer->getIdxName('magedoc/quote_inquiry', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('magedoc/quote_inquiry', 'parent_inquiry_id', 'magedoc/quote_inquiry', 'inquiry_id'),
        'parent_inquiry_id', $installer->getTable('magedoc/quote_inquiry'), 'inquiry_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('magedoc/quote_inquiry', 'quote_vehicle_id', 'magedoc/quote_vehicle', 'vehicle_id'),
        'quote_vehicle_id', $installer->getTable('magedoc/quote_vehicle'), 'vehicle_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('magedoc/quote_inquiry', 'quote_id', 'sales/quote', 'entity_id'),
        'quote_id', $installer->getTable('sales/quote'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('magedoc/quote_inquiry', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Quote Inquiries');
if(!$installer->getConnection()->isTableExists($installer->getTable('magedoc/quote_inquiry'))){
    $installer->getConnection()->createTable($table);
}



$table = $installer->getConnection()
    ->newTable($installer->getTable('magedoc/quote_vehicle'))
    ->addColumn('vehicle_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Vehicle Id')
    ->addColumn('quote_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Quote Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Updated At')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Customer Id')
    ->addColumn('customer_vehicle_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Customer Vehicle Id')
    ->addColumn('manufacturer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Manufacturer Id')
    ->addColumn('model_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Model Id')
    ->addColumn('type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Type Id')
    ->addColumn('production_start_year', Varien_Db_Ddl_Table::TYPE_VARCHAR, 4, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Production Start Year')
    ->addColumn('manufacturer', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Manufacturer Name')
    ->addColumn('model', Varien_Db_Ddl_Table::TYPE_VARCHAR, 80, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Model Name')
    ->addColumn('type', Varien_Db_Ddl_Table::TYPE_VARCHAR, 120, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Type Text')
    ->addIndex($installer->getIdxName('magedoc/quote_vehicle', array('quote_id')),
        array('quote_id'))
    ->addIndex($installer->getIdxName('magedoc/quote_vehicle', array('customer_id')),
        array('customer_id'))
    ->addForeignKey($installer->getFkName('magedoc/quote_vehicle', 'quote_id', 'sales/quote', 'entity_id'),
        'quote_id', $installer->getTable('sales/quote'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Quote Vehicle');;
if(!$installer->getConnection()->isTableExists($installer->getTable('magedoc/quote_vehicle'))){
    $installer->getConnection()->createTable($table);
}

$table = $installer->getConnection()
    ->newTable($installer->getTable('magedoc/invoice_inquiry'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Parent Id')
    ->addColumn('base_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Price')
    ->addColumn('tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tax Amount')
    ->addColumn('base_row_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Row Total')
    ->addColumn('discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Discount Amount')
    ->addColumn('row_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Row Total')
    ->addColumn('base_discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Discount Amount')
    ->addColumn('price_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Price Incl Tax')
    ->addColumn('base_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Tax Amount')
    ->addColumn('base_price_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Price Incl Tax')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Qty')
    ->addColumn('base_cost', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Cost')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Price')
    ->addColumn('base_row_total_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Row Total Incl Tax')
    ->addColumn('row_total_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Row Total Incl Tax')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Product Id')
    ->addColumn('order_inquiry_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Order Inquiry Id')
    ->addColumn('additional_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Additional Data')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Description')
    ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Sku')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Name')
    ->addColumn('hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Hidden Tax Amount')
    ->addColumn('base_hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Hidden Tax Amount')
    ->addColumn('category', Varien_Db_Ddl_Table::TYPE_TEXT, 80, array(
        'nullable'  => true,
        ), 'Category Name')
    ->addColumn('supplier', Varien_Db_Ddl_Table::TYPE_TEXT, 80, array(
        'nullable'  => true,
        ), 'Supplier Brand Name')
    ->addColumn('retailer', Varien_Db_Ddl_Table::TYPE_TEXT, 40, array(
        'nullable'  => true,
        ), 'Retailer Brand Name')        
    ->addIndex($installer->getIdxName('magedoc/invoice_inquiry', array('parent_id')),
        array('parent_id'))
    ->addForeignKey($installer->getFkName('magedoc/invoice_inquiry', 'parent_id', 'sales/invoice', 'entity_id'),
        'parent_id', $installer->getTable('sales/invoice'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('MageDoc Flat Invoice Inquiry');

if(!$installer->getConnection()->isTableExists($installer->getTable('magedoc/invoice_inquiry'))){
    $installer->getConnection()->createTable($table);
}

$table = $installer->getConnection()
    ->newTable($installer->getTable('magedoc/shipment_inquiry'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Parent Id')
    ->addColumn('row_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Row Total')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Price')
    ->addColumn('weight', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Weight')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Qty')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Product Id')
    ->addColumn('order_inquiry_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Order Item Id')
    ->addColumn('additional_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Additional Data')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Description')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Name')
    ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Sku')
    ->addColumn('category', Varien_Db_Ddl_Table::TYPE_TEXT, 80, array(
        'nullable'  => true,
        ), 'Category Name')
    ->addColumn('supplier', Varien_Db_Ddl_Table::TYPE_TEXT, 80, array(
        'nullable'  => true,
        ), 'Supplier Brand Name')
    ->addColumn('retailer', Varien_Db_Ddl_Table::TYPE_TEXT, 40, array(
        'nullable'  => true,
        ), 'Retailer Brand Name')                
    ->addIndex($installer->getIdxName('magedoc/shipment_inquiry', array('parent_id')),
        array('parent_id'))
    ->addForeignKey($installer->getFkName('magedoc/shipment_inquiry', 'parent_id', 'sales/shipment', 'entity_id'),
        'parent_id', $installer->getTable('sales/shipment'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Magedoc Flat Shipment Inquiry');
if(!$installer->getConnection()->isTableExists($installer->getTable('magedoc/shipment_inquiry'))){
    $installer->getConnection()->createTable($table);
}

$salesSetup->addAttribute('quote', 'inquiries_qty', array(
    'type'      => 'int',
    'default'   => 0));


$salesSetup->addAttribute('order_item', 'supply_status', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'length'    => 40,
    'default'   => 'unreserved'));
$tableName = $installer->getTable('sales/order_item');
$conn->addKey($tableName, 'IDX_SUPPLY_STATUS', 'supply_status');
$salesSetup->addAttribute('order_item', 'supply_date', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable'  => true,
    'default'   => 'NULL'));
$conn->addKey($tableName, 'IDX_SUPPLY_DATE', 'supply_date');
$salesSetup->addAttribute('order_item', 'qty_supplied', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'nullable'  => true,
    'length'    => '12,4',
    'default'   => 0));
$salesSetup->addAttribute('order_item', 'qty_reserved', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'nullable'  => true,
    'length'    => '12,4',
    'default'   => 0));


$table = $installer->getConnection()
    ->newTable($installer->getTable('magedoc/retailer_config'))
    ->addColumn('retailer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Retailer Id')
    ->addColumn('login', Varien_Db_Ddl_Table::TYPE_VARCHAR, 80, array(
        ), 'Login')
    ->addColumn('password', Varien_Db_Ddl_Table::TYPE_VARCHAR, 80, array(
        ), 'Password')
    ->addColumn('login_url', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        ), 'Login url path')
    ->addColumn('login_request_method', Varien_Db_Ddl_Table::TYPE_VARCHAR, 8, array(
        ), 'Login Request Method')
    ->addColumn('is_login_use_ajax', Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array(
        'default'   => 0
        ), 'Is Login use ajax')        
    ->addColumn('is_login_use_cookie', Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array(
        'default'   => 0
        ), 'Is Login use ajax')                
    ->addColumn('login_param_string', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        ), 'Login params string')
    ->addColumn('cookie_expression', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        ), 'Expression for cookie')
    ->addColumn('source_url', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        ), 'Source url path')
    ->addColumn('source_request_method', Varien_Db_Ddl_Table::TYPE_VARCHAR, 8, array(
        ), 'Source Request Method')
    ->addColumn('is_source_use_ajax', Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array(
        'default'   => 0
        ), 'Is source use ajax')        
    ->addColumn('source_param_string', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        ), 'Source params string')
    ->addColumn('check_url', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        ), 'Url path for check')
    ->addColumn('check_request_method', Varien_Db_Ddl_Table::TYPE_VARCHAR, 8, array(
        ), 'Check request method')
    ->addColumn('is_check_use_ajax', Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array(
        'default'   => 0
        ), 'Is check use ajax')
    ->addColumn('check_param_string', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        ), 'Check params string')
    ->addColumn('request_param_headers', Varien_Db_Ddl_Table::TYPE_TEXT, '1k', array(
        'default' => NULL
        ), 'Additional HTTP headerss for request')
    ->addColumn('login_param_headers', Varien_Db_Ddl_Table::TYPE_TEXT,'1k', array(
        'default' => NULL
        ), 'Additional HTTP headerss for login')
    ->setComment('MageDoc Retailer Configuration Table');
if(!$installer->getConnection()->isTableExists($installer->getTable('magedoc/retailer_config'))){
    $installer->getConnection()->createTable($table);
}

$salesSetup->addAttribute('order', 'last_manager_id', array(
    'type'      => 'int',
    'default'   => 0,
    'grid'      => true));
$conn->addKey($installer->getTable('sales/order'), 'IDX_LAST_MANAGER_ID', 'last_manager_id');
$conn->addKey($installer->getTable('sales/order_grid'), 'IDX_LAST_MANAGER_ID', 'last_manager_id');

$salesSetup->addAttribute('order', 'supply_status', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'length'    => 40,
    'default'   => 'pending',
    'grid'      => true));
$conn->addKey($installer->getTable('sales/order'), 'IDX_SUPPLY_STATUS', 'supply_status');
$conn->addKey($installer->getTable('sales/order_grid'), 'IDX_SUPPLY_STATUS', 'supply_status');



$installer->endSetup();