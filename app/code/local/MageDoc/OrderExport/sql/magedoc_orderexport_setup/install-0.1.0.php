<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$this->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order'), 'export_status', array(
    'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'comment'  => 'Export Status',
    'default'  => '0',
    'nullable' => false,
    'unsigned' => true
));

$setup = new Mage_Eav_Model_Entity_Setup('sales_setup');
$setup->addAttribute('order', 'export_status', array('type'=>'static'));

$this->endSetup();