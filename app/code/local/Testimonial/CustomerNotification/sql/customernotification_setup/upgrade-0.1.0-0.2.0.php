<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('sales/shipment_track'), 'sms_sent', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'unsigned'=> true,
    'comment' => 'Sms Sent',
));

$installer->getConnection()
    ->addColumn($installer->getTable('customernotification/message'), 'entity_type', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'  => 255,
    'nullable'  => true,
    'comment'   => 'Related Entity Type'
));

$installer->getConnection()
    ->addColumn($installer->getTable('customernotification/message'), 'entity_id', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'unsigned'  => true,
    'nullable'  => true,
    'comment'   => 'Related Entity Id'
));

$installer->getConnection()
    ->addColumn($installer->getTable('customernotification/message'), 'attempt_count', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'unsigned'  => true,
    'nullable'  => false,
    'default'   => '0',
    'comment'   => 'Send Attempt Count'
));

$installer->getConnection()
    ->addColumn($installer->getTable('customernotification/message'), 'success_count', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'unsigned'  => true,
    'nullable'  => false,
    'default'   => '0',
    'comment'   => 'Send Success Count'
));

$installer->getConnection()
    ->addColumn($installer->getTable('customernotification/message'), 'status_text', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'  => 255,
    'nullable'  => false,
    'default'   => '',
    'comment'   => 'Status Text'
));

$installer->endSetup();