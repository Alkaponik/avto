<?php
/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$tableName = $installer->getTable('magedoc/des_text_template');

$table = $installer->getConnection()
    ->newTable($tableName)
    ->addColumn('td_tex_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
    'identity' => true,
    'nullable' => false,
    'primary'  => true,
), 'TecDoc Designation  Text Id')
    ->addColumn('text', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
), 'Designation Text');

$installer->getConnection()->createTable($table);

$installer->endSetup();