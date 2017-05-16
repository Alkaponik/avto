<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$setup = new Mage_Customer_Model_Resource_Setup('customer_setup');

$setup->updateAttribute(
    'customer_address',
    'telephone',
    'validate_rules',
    serialize(array(
        'input_validation'  => 'numeric',
        'max_text_length'   => 12,
        'min_text_length'   => 10))
);

$installer->endSetup();