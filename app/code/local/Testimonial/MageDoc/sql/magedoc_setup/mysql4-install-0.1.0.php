<?php

$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('catalog_setup');
$conn = $installer->getConnection();

$fieldList = array(
    'price',
    'special_price',
    'special_from_date',
    'special_to_date',
    'cost',
    'tier_price',
    'minimal_price',
    'tax_class_id',
    'msrp',
    'manufacturer'
);

// make these attributes applicable to spare product type
foreach ($fieldList as $field) {
    $applyTo = $setup->getAttribute('catalog_product', $field, 'apply_to');
    if ($applyTo!==false){
    $applyTo = !empty($applyTo) ? explode(',', $applyTo) : array();
        if (!empty($applyTo) && !in_array('spare', $applyTo)) {
            $applyTo[] = 'spare';
            $setup->updateAttribute('catalog_product', $field, 'apply_to', join(',', $applyTo));
        }
    }
}

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->run("
	      CREATE TABLE IF NOT EXISTS {$this->getTable('magedoc/manufacturer')}(
	      `td_mfa_id` INT(11) unsigned NOT NULL auto_increment,
	      `enabled` TINYINT(1) unsigned NOT NULL DEFAULT 1,	
	      `logo` VARCHAR(80) default NULL,
	      `name` VARCHAR(255) DEFAULT '',
	      `title` VARCHAR(255) DEFAULT '',
          `description` TEXT default '',
          `url_key` VARCHAR(255) DEFAULT '',
          `url_path` VARCHAR(255) DEFAULT '',
          `meta_title` VARCHAR(255) DEFAULT '',
          `meta_keywords` VARCHAR(255) DEFAULT '',
          `meta_description` VARCHAR(255) DEFAULT '',
          `bottom_content` TEXT default '',
	      PRIMARY KEY (`td_mfa_id`),
          KEY `IDX_ENABLED` (`enabled`)
	    ) ENGINE = InnoDB DEFAULT CHARSET=utf8;
        ");

$installer->run("
	     CREATE TABLE IF NOT EXISTS {$this->getTable('magedoc/supplier')}(
	      `td_sup_id` INT(11) unsigned NOT NULL auto_increment,
	      `enabled` TINYINT(1) unsigned NOT NULL,	
	      `logo` VARCHAR(80) default NULL,
	      `title` VARCHAR(80) DEFAULT '',
	      PRIMARY KEY (`td_sup_id`),
          KEY `IDX_ENABLED` (`enabled`)
	    ) ENGINE= InnoDB DEFAULT CHARSET=utf8;
	  ");

$installer->run("
	      CREATE TABLE IF NOT EXISTS {$this->getTable('magedoc/searchTree')} (
	      `str_id` INT(11) unsigned NOT NULL,
          `path` VARCHAR(80) NOT NULL, 
	      PRIMARY KEY (`str_id`),
          KEY `IDX_PATH` (`path`)
	    ) ENGINE = InnoDB DEFAULT CHARSET=utf8;
	  ");

$setup->addAttribute('catalog_product', 'supplier', array(
    'group'                     => 'MageDoc',
    'type'                      => 'static',
    'backend'                   => '',
    'frontend'                  => '',
    'label'                     => 'Supplier',
    'input'                     => 'select',
    'source'                    => 'magedoc/source_supplier',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'                   => true,
    'required'                  => false,
    'user_defined'              => false,
    'searchable'                => false,
    'filterable'                => true,
    'comparable'                => false,
    'default'                   => null,
    'used_in_product_listing'   => true,
    'apply_to'                  => 'spare'
));

$setup->addAttribute('catalog_product', 'td_art_id', array(
    'group'                     => 'MageDoc',
    'type'                      => 'static',
    'backend'                   => '',
    'frontend'                  => '',
    'label'                     => 'TecDoc Article id',
    'input'                     => 'label',
    'source'                    => '',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'                   => true,
    'required'                  => false,
    'user_defined'              => false,
    'searchable'                => false,
    'filterable'                => true,
    'comparable'                => false,
    'default'                   => ''
));

$tableName = $installer->getTable('catalog/product');
$conn->addColumn($tableName, 'supplier', 'INT(11) unsigned NULL');
$conn->addKey($tableName, 'IDX_SUPPLIER', 'supplier');
$conn->addColumn($tableName, 'td_art_id', 'INT(11) unsigned NULL');
$conn->addKey($tableName, 'IDX_TD_ART_ID', 'td_art_id');

$setup->addAttribute('catalog_category', 'is_import_enabled', array(
    'group'                     => 'MageDoc',
    'type'                      => 'static',
    'backend'                   => '',
    'frontend'                  => '',
    'label'                     => 'Is Import Enabled',
    'input'                     => 'select',
    'source'                    => 'eav/entity_attribute_source_boolean',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'                   => true,
    'required'                  => false,
    'user_defined'              => false,
    'searchable'                => false,
    'filterable'                => false,
    'comparable'                => false,
    'default'                   => '0'));

$setup->addAttribute('catalog_category', 'td_str_id', array(
    'group'                     => 'MageDoc',
    'type'                      => 'static',
    'backend'                   => '',
    'frontend'                  => '',
    'label'                     => 'TecDoc Search Tree Id',
    'input'                     => 'label',
    'source'                    => '',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'                   => true,
    'required'                  => false,
    'user_defined'              => false,
    'searchable'                => false,
    'filterable'                => false,
    'comparable'                => false,
));

$tableName = $installer->getTable('catalog/category');
$conn->addColumn($tableName, 'is_import_enabled', 'INT(1) unsigned NOT NULL DEFAULT "0"');
$conn->addKey($tableName, 'IDS_IS_IMPORT_ENABLED', 'is_import_enabled');
$conn->addColumn($tableName, 'td_str_id', 'INT(11) unsigned NULL');
$conn->addKey($tableName, 'IDX_TD_STR_ID', 'td_str_id');



$importTable = $this->getTable('magedoc/import_retailer_data');
$installer->run("	 
	      CREATE TABLE IF NOT EXISTS " . $importTable . "(
            `data_id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
            `td_art_id` INT(11) DEFAULT NULL,
            `product_id` INT(10) unsigned DEFAULT NULL,
            `card` VARCHAR(40) DEFAULT NULL,
            `name` VARCHAR(255) DEFAULT NULL,
            `description` TEXT,
            `code` VARCHAR(255) NOT NULL,
            `code_normalized` VARCHAR(255) NOT NULL,
            `code_raw` VARCHAR(255) NOT NULL,
            `model` VARCHAR(255) DEFAULT NULL,
            `model_normalized` VARCHAR(255) DEFAULT NULL,
            `cost` DECIMAL(12,4) NOT NULL,
            `price` DECIMAL(12,4) DEFAULT NULL,
            `msrp` DECIMAL(12,4) DEFAULT NULL,
            `final_price` DECIMAL(12,4) DEFAULT NULL,
            `delivery_days` INT(3) DEFAULT NULL,
            `currency` VARCHAR(10) DEFAULT NULL,
            `supplier_id` INT(11) DEFAULT NULL,
            `retailer_id` INT(11) NOT NULL,
            `manufacturer` VARCHAR(40) NOT NULL,
            `manufacturer_id` INT(11) DEFAULT NULL,
            `domestic_stock_qty` INT(11) NOT NULL,
            `general_stock_qty` INT(11) NOT NULL,
            `other_stock_qty` INT(11) NOT NULL,
            `distant_stock_qty` INT(11) NOT NULL,
            `qty` INT(11) NOT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`data_id`),
            UNIQUE KEY `code_manufacturer` (`code_raw`, `manufacturer`, `retailer_id`),
            UNIQUE KEY `code_normalized_manufacturer` (`code_normalized`, `manufacturer`,`retailer_id`),
            KEY `IDX_PRODUCT_ID` (`product_id`),
            KEY `IDX_TD_ART_ID` (`td_art_id`),
            KEY `IDX_CARD` (`card`),
            KEY `IDX_CODE_NORMALIZED` (`code_normalized`),
            KEY `IDX_MODEL` (`model`),
            KEY `IDX_MODEL_NORMALIZED` (`model_normalized`),
            KEY `IDX_SUPPLIER_ID` (`supplier_id`),
            KEY `IDX_RETAILER_ID` (`retailer_id`),
            KEY `IDX_MANUFACTURER_ID` (`manufacturer_id`),
            KEY `IDX_RETAILER_ID_MANUFACTURER_CODE_RAW` (`retailer_id`, `manufacturer`, `code_raw`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	  ");
/*
$installer->run("	 	     
            UPDATE {$importTable}
                INNER JOIN {$installer->getTable('magedoc/tecdoc_articleNormalized')} as articles_normalized
                     ON articles_normalized.ARN_ARTICLE_NR_NORMALIZED = {$importTable}.code
                            AND articles_normalized.ARN_SUP_ID = {$importTable}.supplier_id
                INNER JOIN ARTICLES as articles
                     ON articles_normalized.ARN_ART_ID = articles.ART_ID
                SET {$importTable}.td_art_id = articles.ART_ID 
                WHERE {$importTable}.td_art_id IS NULL;
	  ");

$installer->run("	 
            UPDATE {$importTable}
                INNER JOIN catalog_product_entity ON catalog_product_entity.td_art_id = 
                    {$importTable}.td_art_id
                SET {$importTable}.product_id = catalog_product_entity.entity_id
                WHERE {$importTable}.product_id IS NULL;
	  ");

$installer->run("	 
            UPDATE {$importTable}
                INNER JOIN {$installer->getTable('magedoc/tecdoc_article')} as articles
                    ON articles.ART_ID = {$importTable}.td_art_id
                INNER JOIN {$installer->getTable('magedoc/tecdoc_designation')} ON DESIGNATIONS.DES_ID = articles.ART_COMPLETE_DES_ID AND
                    DESIGNATIONS.DES_LNG_ID = 16
                INNER JOIN {$installer->getTable('magedoc/tecdoc_desText')} ON TEX_ID = DESIGNATIONS.DES_TEX_ID
                INNER JOIN {$installer->getTable('magedoc/tecdoc_supplier')} ON SUPPLIERS.SUP_ID = {$importTable}.supplier_id
                SET {$importTable}.name = CONCAT(DES_TEXTS.TEX_TEXT, ' ',
                SUPPLIERS.SUP_BRAND, ' ', articles.ART_ARTICLE_NR)
                WHERE {$importTable}.name IS NULL;
	  ");

$tableName = $installer->getTable('magedoc/tecdoc_article');
$conn->addColumn($tableName, 'ART_ARTICLE_NR_NORMALIZED', 'VARCHAR(80) NOT NULL');
$conn->addKey($tableName, 'IDX_ART_ARTICLE_NR_NORMALIZED', 'ART_ARTICLE_NR_NORMALIZED');

$installer->run("	 
	     UPDATE {$installer->getTable('magedoc/tecdoc_article')} 
                SET ART_ARTICLE_NR_NORMALIZED=(REPLACE(ART_ARTICLE_NR, ' ', ''));
	  ");
*/

$installer->run("
    CREATE TABLE IF NOT EXISTS {$this->getTable('magedoc/retailer')} (
        `retailer_id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(40) NOT NULL,
        `model` varchar(40) NOT NULL,
        `currency_code` varchar(3) NOT NULL,
        `rate` decimal(4,2) NOT NULL,
        `sort_order` int(11) NOT NULL DEFAULT '100',
        `last_import_date` datetime NOT NULL,
        `margin_ratio` decimal(12,4) NOT NULL DEFAULT '1.0000',
        `fixed_fee` decimal(12,4) NOT NULL DEFAULT '0.0000',
        `enabled` int(1) NOT NULL DEFAULT '0',
        `is_import_enabled` int(1) NOT NULL DEFAULT '0',
        `use_for_autopricing` int(1) NOT NULL DEFAULT '0',
        `is_update_enabled` int(1) NOT NULL DEFAULT '0',
        `update_model` varchar(40) NOT NULL,
        `request_adapter` varchar(40) NOT NULL,
        `session_data` text DEFAULT NULL,
        `discount_table` text DEFAULT NULL,
        `margin_table` text DEFAULT NULL,
      PRIMARY KEY (`retailer_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
    SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO';
    INSERT IGNORE INTO {$this->getTable('magedoc/retailer')} (`retailer_id`, 
        `sort_order`, `name`, `model`, `rate`, `session_data`, `last_import_date`,
        `margin_ratio`, `fixed_fee`,
        `enabled`, `is_import_enabled`, `use_for_autopricing`, `is_update_enabled`)
        VALUES
        (0, 0, 'Aggregator', 'aggregator', '1.00', null, '0000-00-00 00:00:00', '1', '0', '1', '0', '0', '0');
        ");

$installer->endSetup();