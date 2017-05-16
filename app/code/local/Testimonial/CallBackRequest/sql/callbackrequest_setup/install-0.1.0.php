<?php
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('callbackrequest/request'))
    ->addColumn('request_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true
        ), 'Id')
    ->addColumn('telephone', Varien_Db_Ddl_Table::TYPE_VARCHAR, 15, array(
            'nullable'  => false
        ), 'Client phone number')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false
        ), 'Name of client')
    ->addColumn('comment', Varien_Db_Ddl_Table::TYPE_TEXT, '1k', array(
        ), 'Comment')
    ->addColumn('checkout_cart', Varien_Db_Ddl_Table::TYPE_TEXT, '1k', array(
        ), 'Checkout Cart')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TINYINT , 1, array(
        'unsigned'  => true,
        'default'   => '0',
        'nullable'  => false
        ), 'Status of application processing')
    ->addColumn('token', Varien_Db_Ddl_Table::TYPE_VARCHAR , 32, array(
        ), 'The security key to change the status of the request')
    ->addColumn('remote_addr', Varien_Db_Ddl_Table::TYPE_VARCHAR , 32, array(
        ), 'User IP address')
    ->addColumn('referer', Varien_Db_Ddl_Table::TYPE_VARCHAR , 255, array(
        ), 'Referer')
    ->addColumn('manager_id', Varien_Db_Ddl_Table::TYPE_INTEGER , null, array(
            'unsigned'  => true,
        ), 'Manager Id')
    ->addColumn('manager_name', Varien_Db_Ddl_Table::TYPE_VARCHAR , 255, array(
        ), 'Manager Name')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'nullable'  => false,
            'default'   => Varien_Db_Ddl_Table::TIMESTAMP_INIT
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Updated At')
    ->addIndex($installer->getIdxName('callbackrequest/request', array('telephone')),
            array('telephone'))
    ->addIndex($installer->getIdxName('callbackrequest/request', array('manager_id')),
            array('manager_id'))
    ->setComment('Callback Request Table');

if(!$installer->getConnection()->isTableExists($installer->getTable('callbackrequest/request'))){
    $installer->getConnection()->createTable($table);
}

$installer->endSetup();