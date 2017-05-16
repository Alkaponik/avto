<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$salesSetup = new Mage_Sales_Model_Resource_Setup('sales_setup');

$salesSetup->addAttribute('quote_item', 'custom_cost', array('type' => 'decimal'));

$salesSetup->addAttribute('order_item', 'original_cost', array('type' => 'decimal'));

$salesSetup->addAttribute('order_item', 'base_original_cost', array('type' => 'decimal'));

$installer->endSetup();