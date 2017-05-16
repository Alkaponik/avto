<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$setup = new Mage_Customer_Model_Resource_Setup('customer_setup');

$setup->updateAttribute(
    'customer',
    'website_id',
    'default_value',
    1
);

$installer->endSetup();