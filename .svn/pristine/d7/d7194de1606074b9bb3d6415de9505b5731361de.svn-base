<?php
/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('sales/quote_item'), 'quote_vehicle_id', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable' => true,
        'default' => null,
        'comment' => 'Quote Vehicle Id',
    ));
$installer->endSetup();
