<?php
/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('magedoc/type_product'), 'type', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 1,
        'nullable' => false,
        'default' => 'S',
        'comment' => 'Vehicle Type',
    ));
$installer->endSetup();
