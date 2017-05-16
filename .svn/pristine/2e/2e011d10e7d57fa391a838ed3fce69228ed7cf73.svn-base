<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$tableName = 'novaposhta/city';

$installer->getConnection()
    ->addColumn($installer->getTable($tableName), 'area_name_ua', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    =>  100,
    'comment' => 'Area Name Ua',
));

$field = 'region_id';

$installer->getConnection()
    ->addColumn($installer->getTable($tableName), $field, array(
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'comment' => 'Region Id',
));

$installer->getConnection()->addIndex(
    $installer->getTable($tableName),
    $installer->getIdxName($tableName, $field),
    $field
);

$installer->endSetup();