<?php

$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create table 'catalog/product'
 */

$table = Mage::getResourceSingleton('flatcatalog/product')
    ->createMainTable($installer, $installer->getTable('flatcatalog/product'));
