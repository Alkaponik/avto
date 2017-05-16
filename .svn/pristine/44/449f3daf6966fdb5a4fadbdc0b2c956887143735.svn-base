<?php
/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();
$connection = $installer->getConnection();

$tables = array(
    $installer->getTable('sales/order_item'),
    $installer->getTable('magedoc/order_inquiry')
);

foreach ($tables as $table) {
    $connection->addColumn(
        $table,
        'receipt_reference',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'Receipt Reference'
        )
    );

    $connection->addColumn(
        $table,
        'return_reference',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'Return Reference'
        )
    );

    $connection->addIndex(
        $table,
        $installer->getIdxName($table, array('receipt_reference')),
        array('receipt_reference')
    );

    $connection->addIndex(
        $table,
        $installer->getIdxName($table, array('return_reference')),
        array('return_reference')
    );
}

$installer->endSetup();