<?php

$this->startSetup();

$table = $this->getTable('shipping_multipletablerates');

if ($this->tableExists($table)){
    $this->getConnection()->addColumn($table,
    'customer_group_id', "int(10) NOT NULL DEFAULT 0");

    $this->getConnection()->addColumn($table,
    'product_group_id', "int(10) NOT NULL DEFAULT 0");

    $this->run("
        ALTER TABLE {$table} DROP INDEX `dest_country` ,
        ADD UNIQUE `dest_country` ( `website_id` , `dest_country_id` , `dest_region_id` , `dest_zip` , `condition_name` , `condition_value` , `method_code` , `customer_group_id` , `product_group_id`)"
        );
}

$setup = new Mage_Eav_Model_Entity_Setup('catalog_setup');

$this->endSetup();