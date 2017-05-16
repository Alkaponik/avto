<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$setup = new Mage_Customer_Model_Resource_Setup('customer_setup');

$setup->addAttribute('customer', 'sugarcrm_contact_id', array(
    'type' => 'static',
    'label' => 'SugarCRM Corresponding Contact Id',
    'input' => 'text',
    'position' => 200,
    'required' => false,
    'visible' => false,
    'system' => true
));

$installer->getConnection()->addColumn($installer->getTable('customer/entity'), 'sugarcrm_contact_id', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length' => 36,
    'nullable' => true,
    'comment' => 'SugarCRM Corresponding Contact Id'
));

$installer->getConnection()->addKey($installer->getTable('customer/entity'), $installer->getIdxName('customer/entity', array('sugarcrm_contact_id')), 'sugarcrm_contact_id');

$installer->getConnection()->addColumn($installer->getTable('admin/user'), 'sugarcrm_user_id', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length' => 36,
    'nullable' => true,
    'comment' => 'SugarCRM User Id'
));

$installer->getConnection()->addKey($installer->getTable('admin/user'), $installer->getIdxName('admin/user', array('sugarcrm_user_id')), 'sugarcrm_user_id');

$installer->endSetup();