<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
    $installer = $this;

    $installer->startSetup();

    $tableName = 'novaposhta/consignment';
    $field     = 'consignment_id';
    $table = $installer->getConnection()
        ->newTable($installer->getTable($tableName))
        ->addColumn($field, Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
            'comment'  => 'Consignment Id'
        ))
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
        ->addColumn('ttn', Varien_Db_Ddl_Table::TYPE_VARCHAR, 18, array(
            'default'  => null,
            'comment'  => 'Consignment note number'
        ))
        ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TINYINT, 3, array(
            'unsigned' => true,
            'nullable' => true,
            'comment'  => 'Consignment note status'
        ))
        ->addColumn('stage', Varien_Db_Ddl_Table::TYPE_TINYINT, 3, array(
            'unsigned' => true,
            'nullable' => true,
            'comment'  => 'Consignment note stage'
        ))
        ->addColumn('state', Varien_Db_Ddl_Table::TYPE_TINYINT, 3, array(
            'unsigned' => true,
            'nullable' => true,
            'comment'  => 'Consignment state'
        ))
        ->addColumn('city_sender_ru', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'Sender city'
        ))
        ->addColumn('city_sender_ua', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'Sender city'
        ))
        ->addColumn('sender_company', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'Sender company'
        ))
        ->addColumn('sender_address', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'The sender\'s address'
        ))
        ->addColumn('sender_contact', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'Contact person sender'
        ))
        ->addColumn('sender_phone', Varien_Db_Ddl_Table::TYPE_VARCHAR, 15, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'Contact phone sender'
        ))
        ->addColumn('date_estimated', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, NULL, array(
            'default'  => null,
            'nullable' => true,
            'comment'  => 'Date estimated'
        ))
        ->addColumn('date_desired', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, NULL, array(
            'default'  => null,
            'nullable' => true,
            'comment'  => 'Date desired delivery order (used when creating consignment)'
        ))
        ->addColumn('payer', Varien_Db_Ddl_Table::TYPE_TINYINT, 3, array(
            'default'  => null,
            'nullable' => true,
            'comment'  => 'Payer'
        ))
        ->addColumn('payer_city', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'Payer city'
        ))
        ->addColumn('payer_company', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'Payer company'
        ))
        ->addColumn('form_payment', Varien_Db_Ddl_Table::TYPE_TINYINT, 3, array(
            'unsigned' => true,
            'nullable' => true,
            'default'  => '0',
            'comment'  => 'Form payment'
        ))
        ->addColumn('sum', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
            'nullable' => true,
            'default'  => null,
            'comment'  => 'Amount payable for delivery'
        ))
        ->addColumn('cost', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
            'nullable' => true,
            'default'  => null,
            'comment'  => 'The declared value of the shipment'
        ))
        ->addColumn('delivery_form', Varien_Db_Ddl_Table::TYPE_TINYINT, 3, array(
            'nullable' => true,
            'default'  => null,
            'unsigned' => true,
            'comment'  => 'Delivery form'
        ))
        ->addColumn('ware_receiver_ua', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'Warehouse address of the recipient'
        ))
        ->addColumn('ware_receiver_ru', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'Warehouse address of the recipient'
        ))
        ->addColumn('back_delivery', Varien_Db_Ddl_Table::TYPE_TINYINT, 3, array(
            'nullable' => false,
            'default'  => 0,
            'unsigned' => true,
            'comment'  => 'Return shipping availability'
        ))
        ->addColumn('city_receiver_ua', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'City recipient'
        ))
        ->addColumn('city_receiver_ru', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'City recipient'
        ))
        ->addColumn('rcpt_name',Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'Recipient organization'
        ))
        ->addColumn('rcpt_warehouse', Varien_Db_Ddl_Table::TYPE_VARCHAR, 10, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'Current number novaposhta Branch address'
        ))
        ->addColumn('rcpt_street_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'Address of the recipient'
        ))
        ->addColumn('date_received', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'default'  => null,
            'nullable' => true,
            'comment'  => 'Date receive the shipment'
        ))
        ->addColumn('receiver', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'Recipient name'
        ))
        ->addColumn('rcpt_phone_num', Varien_Db_Ddl_Table::TYPE_VARCHAR, 15, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'Recipient Contact phone'
        ))
        ->addColumn('redelivery', Varien_Db_Ddl_Table::TYPE_VARCHAR, 18, array(
            'default'  => '',
            'comment'  => 'Return shipping consignment note number'
        ))
        ->addColumn('is_back_delivery', Varien_Db_Ddl_Table::TYPE_TINYINT , 1, array(
            'default'  => 0,
            'comment'  => 'Flag - is back delivery. Default - false (0)'
        ))
        ->addColumn('redelivery_type', Varien_Db_Ddl_Table::TYPE_TINYINT, 3, array(
            'nullable' => true,
            'default'  => null,
            'unsigned' => true,
            'comment'  => 'Redelivery type'
        ))
        ->addColumn('delivery_in_out', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'That in reverse the delivery'
        ))
        ->addColumn('redelivery_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
            'nullable' => true,
            'default'  => null,
            'comment'  => 'Return shipping cost, excluding special price setting'
        ))
        ->addColumn('redelivery_sum', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
            'nullable' => true,
            'default'  => null,
            'comment'  => 'redelivery sum'
        ))
        ->addColumn('redelivery_payment_payer', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'Payer reverse delivery'
        ))
        ->addColumn('redelivery_payment_city', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'Sity Payer reverse delivery'
        ))
        ->addColumn('full_description', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'Full description'
        ))
        ->addColumn('additional_info', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'Additional information'
        ))
        ->addColumn('documents',Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'Documents accompanying goods'
        ))
        ->addColumn('pack_type', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'Type of packaging'
        ))
        ->addColumn('floor_count', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'The rise of the floor'
        ))
        ->addColumn('saturday', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
            'nullable' => true,
            'default'  => '0',
            'unsigned' => true,
            'comment'  => 'Saturday delivery'
        ))
        ->addColumn('delivery_amount',Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'Payment by proxy, unless separately signed agreement'
        ))
        ->addColumn('parent_document_number', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
            'default'  => '',
            'nullable' => false,
            'comment'  => 'number of the parent document'
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
        ->addColumn('weight', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
            'nullable' => true,
            'default'  => null,
            'comment'  => 'weight'
        ))
        ->addColumn('length', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
            'nullable' => true,
            'default'  => null,
            'comment'  => 'length'
        ))
        ->addColumn('width', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
            'nullable' => true,
            'default'  => null,
            'comment'  => 'width'
        ))
        ->addColumn('height', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
            'nullable' => true,
            'default'  => null,
            'comment'  => 'height'
        ))
        ->addIndex($installer->getIdxName($tableName, 'is_back_delivery'), 'is_back_delivery')
        ->addIndex($installer->getIdxName($tableName, 'customer_id'), 'customer_id')
        ->addIndex($installer->getIdxName($tableName, 'order_id'), 'order_id')
        ->addIndex($installer->getIdxName($tableName, 'shipment_id'), 'shipment_id')
        ->addIndex($installer->getIdxName($tableName, 'ttn'), 'ttn')
        ->addIndex($installer->getIdxName($tableName, 'status'), 'status')
        ->addIndex($installer->getIdxName($tableName, 'stage'), 'stage')
        ->addIndex($installer->getIdxName($tableName, 'updated_at'), 'updated_at');

    $installer->getConnection()->createTable($table);
    $installer->endSetup();
