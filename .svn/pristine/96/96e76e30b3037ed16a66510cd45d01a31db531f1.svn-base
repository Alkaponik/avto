<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('customernotification/message'), 'manager_id', array(
        'type'    => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => true,
        'comment'   => 'Manager Id'
    ));

$installer->getConnection()
    ->addColumn($installer->getTable('customernotification/message'), 'manager_name', array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'nullable'  => true,
        'comment'   => 'Manager Name'
    ));

$installer->endSetup();