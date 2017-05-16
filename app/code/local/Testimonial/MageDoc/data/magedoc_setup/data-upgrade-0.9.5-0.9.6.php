<?php
/* @var Mage_Core_Model_Resource_Setup $installer */

$installer = $this;

$installer->startSetup();

$connection = $installer->getConnection();

$insertBunch = array();

$select = $connection
    ->select()
    ->from(
        $installer->getTable('magedoc/retailer_data_import_session')
    );

$result = $connection->query($select);
$session = new Varien_Object();
while($row = $result->fetch()){
    $session->setData($row);
    $files = explode(', ', $session['price_filename']);

    foreach($files as $sourceFile) {
        $insertBunch[] = array(
            'source_id'     => new Zend_Db_Expr('NULL'),
            'source_path'   => $sourceFile,
            'session_id'    => $session['session_id'],
            'config_id'     => $session['config_id'],
        );
    }
}

$insertedCount = $connection->insertMultiple($installer->getTable('magedoc/retailer_data_import_session_source'), $insertBunch);

$installer->endSetup();