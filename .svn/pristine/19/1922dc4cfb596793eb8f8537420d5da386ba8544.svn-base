<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'customernotification/message'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('customernotification/message'))
    ->addColumn('message_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Message Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
    'default'   => 0,
), 'Order Id')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => true,
), 'Order Id')
    ->addColumn('order_increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
), 'Order Increment Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => true,
), 'Customer Id')
    ->addColumn('customer_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
), 'Customer Name')
    ->addColumn('channel', Varien_Db_Ddl_Table::TYPE_TEXT, 40, array(
), 'Channel')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    'unsigned'  => true,
    'nullable'  => false,
    'default'   => '0'
), 'Status')
    ->addColumn('event', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
), 'Event')
    ->addColumn('recipient', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
), 'Recipient')
    ->addColumn('text', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
), 'Text')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    'nullable'  => false,
), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    'nullable'  => false,
), 'Updated At')
    ->setComment('Customer Notification Message');
$installer->getConnection()->createTable($table);

$installer->getConnection()
    ->addColumn($installer->getTable('sales/invoice'), 'sms_sent', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'unsigned'=> true,
    'comment' => 'Sms Sent',
));

$installer->endSetup();