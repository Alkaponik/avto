<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$setup = new Mage_Eav_Model_Entity_Setup('customer_setup');

$eavConfig = Mage::getSingleton('eav/config');

$eavConfig->getAttribute('customer', 'lastname')->setIsRequired(false)->save();

$eavConfig->getAttribute('customer_address', 'lastname')->setIsRequired(false)->save();

$eavConfig->getAttribute('customer_address', 'street')->setIsRequired(false)->save();

//$eavConfig->getAttribute('customer_address', 'city')->setIsRequired(false)->save();