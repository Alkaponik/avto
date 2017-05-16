<?php
/* @var Mage_Core_Model_Resource_Setup $installer */

$installer = $this;

$installer->startSetup();

$connection = $installer->getConnection();

$select = $connection->select()
    ->from(
        $this->getTable('magedoc/import_retailer_data'),
        array('data_id', 'td_art_id', new Zend_Db_Expr("'tecdoc'"), 'supplier_id')
    );

$insert = $connection->insertFromSelect(
    $select,
    $this->getTable('magedoc/directory_offer_link'),
    array('data_id','directory_entity_id', 'directory_code', 'supplier_id')
);
$connection->query($insert);

$select = $connection->select()
    ->from(
        $this->getTable('magedoc/import_retailer_data_preview'),
        array('data_id', 'td_art_id', new Zend_Db_Expr("'tecdoc'"), 'supplier_id')
    );

$insert = $connection->insertFromSelect(
    $select,
    $this->getTable('magedoc/directory_offer_link_preview'),
    array('data_id','directory_entity_id', 'directory_code', 'supplier_id')
);
$connection->query($insert);

$connection->update(
    $this->getTable('magedoc/import_retailer_data_base'),
    array('code_raw' => new Zend_Db_Expr('code'))
);

$installer->endSetup();