<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$tableName = $installer->getTable('magedoc/model');
$table = $installer->getConnection()
    ->newTable($tableName)
    ->addColumn('td_mod_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'TecDoc Model Id')
    ->addColumn('enabled', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default'  => '0',
    ), 'Model enabled/disabled status')
    ->addColumn('visible', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default'  => '1',
    ), 'Model visible/invisible status')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    ), 'Model name')
    ->addColumn('title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    ), 'Model title')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
    ), 'Model description')
    ->addColumn('url_key', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    ), 'Url key')
    ->addColumn('url_path', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    ), 'Url path')
    ->addColumn('meta_keywords', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    ), 'Meta keywords')
    ->addColumn('meta_description', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    ), 'Meta description')
    ->addIndex($installer->getIdxName($tableName, array('enabled')),array('enabled'))
    ->addIndex($installer->getIdxName($tableName, array('visible')),array('visible') );

    $installer->getConnection()->createTable($table);

$installer->endSetup();