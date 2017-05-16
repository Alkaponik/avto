<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$setup = new Mage_Customer_Model_Resource_Setup('customer_setup');

$managerAttributeCode = 'manager_id';

$setup->addAttribute('customer', $managerAttributeCode, array(
    'type'              => 'static',
    'label'             => 'Manager',
    'position'          => 200,
    'required'          => false,
    'visible'           => false,
    'input'             => 'select',
    'source'            => 'magedoc/source_orderManager',
    'adminhtml_only'    => 1,
));

$installer->getConnection()->addColumn($installer->getTable('customer/entity'), 'manager_id', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'nullable'  => false,
    'default'   =>  0,
    'comment'   => 'Manager Id'
));

$managerAttribute = Mage::getSingleton('eav/config')
    ->getAttribute('customer', $managerAttributeCode);
$managerAttribute->setData('used_in_forms', array(
    'adminhtml_customer'
));
$managerAttribute->save();

$installer->endSetup();