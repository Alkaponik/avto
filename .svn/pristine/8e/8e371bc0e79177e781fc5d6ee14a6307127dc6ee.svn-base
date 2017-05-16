<?php
/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$tableName = $installer->getTable('magedoc/criteria');
$table = $installer->getConnection()
    ->newTable($tableName)
    ->addColumn('td_cri_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 6, array(
    'identity' => true,
    'unsigned' => true,
    'nullable' => false,
    'primary'  => true,
), 'TecDoc Criteria Id')
    ->addColumn('enabled', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    'unsigned' => true,
    'nullable' => false,
    'default'  => '0',
), 'Article enabled/disabled status')
    ->addColumn('is_import_enabled', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    'unsigned' => true,
    'nullable' => false,
    'default'  => '0',
), 'Is import enabled')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
), 'Model name')
    ->addColumn('attribute_code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
), 'Product Attribute Code')
    ->addIndex($installer->getIdxName($tableName, array('enabled')),array('enabled'))
    ->addIndex($installer->getIdxName($tableName, array('is_import_enabled')),array('is_import_enabled') );

$installer->getConnection()->createTable($table);

$installer->endSetup();