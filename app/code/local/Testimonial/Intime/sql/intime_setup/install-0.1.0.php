<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$tableName = 'intime/warehouse';
$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable($tableName))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true
        ), 'Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false
        ), 'Name of warehouse')
    ->addColumn('phone', Varien_Db_Ddl_Table::TYPE_VARCHAR, 15, array(
            'nullable'  => false
        ), 'phone')
    ->addColumn('adress', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        ), 'adress')
    ->addColumn('city_code', Varien_Db_Ddl_Table::TYPE_INTEGER, NULL, array(
        'nullable'  => false
        ), 'city code')
    ->addColumn('number', Varien_Db_Ddl_Table::TYPE_INTEGER , 1, array(
        'nullable'  => false
        ), 'number of warehouse in the cyty')
    ->addIndex($installer->getIdxName($tableName, 'id'), 'id')
    ->addIndex($installer->getIdxName($tableName, 'city_code'), 'city_code')
    ->setComment('Intime warehouse Table');

if(!$installer->getConnection()->isTableExists($installer->getTable($tableName))){
    $installer->getConnection()->createTable($table);
}

$tableName = 'intime/city';

$table = $installer->getConnection()
    ->newTable($installer->getTable($tableName))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true
        ), 'Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false
        ), 'Name of city')
    ->addColumn('phone', Varien_Db_Ddl_Table::TYPE_VARCHAR, 15, array(
            'nullable'  => false
        ), 'phone')
    ->addColumn('adress', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        ), 'adress')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 10, array(
        'nullable'  => false
        ), 'code')
    ->addColumn('region_id', Varien_Db_Ddl_Table::TYPE_INTEGER, NULL, array(
        'nullable'  => true
        ), 'region_id')
    ->addIndex($installer->getIdxName($tableName, 'id'), 'id')
    ->setComment('Intime city Table');

if(!$installer->getConnection()->isTableExists($installer->getTable($tableName))){
    $installer->getConnection()->createTable($table);
}

$tableName = 'intime/consignment';

$table = $installer->getConnection()
    ->newTable($installer->getTable($tableName))
    ->addColumn('consignment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true
        ), 'consignment_id')
    ->addColumn('customer_id',Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'comment'  => 'Customer Id'
    ))
    ->addColumn('order_id',Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'comment'  => 'Order Id'
    ))
    ->addColumn('order_increment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'comment'  => 'Order increment Id'
    ))
    ->addColumn('shipment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'comment'  => 'Shipment Id'
    ))
    ->addColumn('ttn', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(
        'nullable'  => true
        ), 'Consignment note number')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TINYINT, 3, array(
        'nullable'  => true
        ), 'Consignment status')
    ->addColumn('status_text', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'default'  => null,
        ), 'text value of status ')
    ->addColumn('sender_city', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'default'  => '',
        ), 'Sender city')
    ->addColumn('receiver_city', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'default'  => '',
        ), 'Receive city')
    ->addColumn('contact', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'default'  => '',
        ), 'contact')
    ->addColumn('type_delivery', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'default'  => '',
        ), 'Type delivery')
    ->addColumn('num_places', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'default'  => '',
        ), 'Number places in delivery')
    ->addColumn('volume', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'default'  => '',
        ), 'volume in delivery')
    ->addColumn('payer', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'default'  => '',
        ), 'volume in delivery')
    ->addColumn('redelivery', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(
        'default'  => '',
        ), 'Redelivery')
    ->addColumn('is_back_delivery', Varien_Db_Ddl_Table::TYPE_TINYINT , 1, array(
        'default'  => 0,
    ), 'Flag - is back delivery. Default - false (0)')
    ->addColumn('sum', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => true,
        'default'   => '0.0000',
        ), 'Sum')
    ->addColumn('arrival_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'default'  => null,
        'nullable' => true,
        'comment'  => 'delivery arrival date'
    ))
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'default'  => null,
        'nullable' => true,
        'comment'  => 'Created'
    ))
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'default'  => null,
        'nullable' => true,
        'comment'  => 'Updated'
    ))
    ->addIndex($installer->getIdxName($tableName, 'customer_id'), 'customer_id')
    ->addIndex($installer->getIdxName($tableName, 'order_id'), 'order_id')
    ->addIndex($installer->getIdxName($tableName, 'shipment_id'), 'shipment_id')
    ->addIndex($installer->getIdxName($tableName, 'ttn'), 'ttn')
    ->addIndex($installer->getIdxName($tableName, 'status'), 'status')
    ->addIndex($installer->getIdxName($tableName, 'updated_at'), 'updated_at')
    ->setComment('Consignment data');

if(!$installer->getConnection()->isTableExists($installer->getTable($tableName))){
    $installer->getConnection()->createTable($table);
}
$installer->endSetup();