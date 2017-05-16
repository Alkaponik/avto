<?php
/* @var Mage_Core_Model_Resource_Setup $installer */

$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

$select = $connection->select()
    ->from(
        array('import_retailer_data' => $this->getTable('magedoc/import_retailer_data')),
        array('data_id', new Zend_Db_Expr('\'tire\''), 'supplier_id')
    )->where('retailer_id > 1000');

$insert = $connection->insertFromSelect(
    $select,
    $this->getTable('magedoc/directory_offer_link'),
    array('data_id', 'directory_code', 'supplier_id'),
    Varien_Db_Adapter_Interface::INSERT_ON_DUPLICATE
);

$connection->query($insert);

$installer->endSetup();