<?php

$installer = $this;
$installer->startSetup();
$conn = $installer->getConnection();

$table = $installer->getTable('shipping_multipletablerates');

if ($this->tableExists($table)){
    $this->getConnection()->addColumn($table,
    'transport_type', "VARCHAR(32) NOT NULL DEFAULT 0");
}

$this->run("
        ALTER TABLE {$table} DROP INDEX `dest_country` ,
        ADD UNIQUE `dest_country` ( `website_id` , `dest_country_id` , `dest_region_id` , `dest_zip` , `condition_name` , `condition_value` , `method_code` , `customer_group_id` , `product_group_id`, `transport_type`)"
        );

$setup = new Mage_Eav_Model_Entity_Setup('catalog_setup');

$setup->addAttribute('catalog_product', 'transport_type', array(
    'type'              => 'static',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Transport Type',
    'input'             => 'select',
    'class'             => '',
    'source'            => 'phoenix_multipletablerates/source_transport_type',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => true,
    'user_defined'      => false,
    'default'           => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'used_in_product_listing' => true,
    'group'             => 'General'
));

$tableName = $installer->getTable('catalog/product');
$conn->addColumn($tableName, 'transport_type', 'INT(11) unsigned NULL DEFAULT 2');
$conn->addKey($tableName, 'IDX_TRANSPORT_TYPE', 'transport_type');

$this->endSetup();