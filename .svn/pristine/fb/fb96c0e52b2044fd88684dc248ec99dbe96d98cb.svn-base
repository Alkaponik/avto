<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('avtoto/autopricing_process_status'))
    ->addColumn('status_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Status Id')
    ->addColumn('process_code', Varien_Db_Ddl_Table::TYPE_TEXT, 32,
        array(
            'nullable'  => false,
        ), 'Process Code')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 15,
        array(
            'nullable'  => false,
            'default'   => 'pending',
        ), 'Status')
    ->addColumn('started_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
        array(
        ), 'Started At')
    ->addColumn('ended_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Ended At')
    ->addColumn('mode', Varien_Db_Ddl_Table::TYPE_TEXT, 9,
        array(
            'nullable'  => false,
            'default'   => 'real_time',
        ), 'Mode')
    ->addIndex(
        $installer->getIdxName('avtoto/autopricing_process_status', array('process_code'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('process_code'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->setComment('Process Status');

$installer->getConnection()->createTable($table);
$installer->endSetup();