<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('sales/creditmemo'), 'cost', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'comment' => 'Cost',
    'scale'     => 4,
    'precision' => 12,
));

$installer->getConnection()
    ->addColumn($installer->getTable('sales/creditmemo'), 'base_cost', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'comment' => 'Base Cost',
    'scale'     => 4,
    'precision' => 12,
));

$installer->getConnection()
    ->addColumn($installer->getTable('sales/creditmemo'), 'margin', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'comment' => 'Margin',
    'scale'     => 4,
    'precision' => 12,
));

$installer->getConnection()
    ->addColumn($installer->getTable('sales/creditmemo'), 'base_margin', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'comment' => 'Base Margin',
    'scale'     => 4,
    'precision' => 12,
));

$installer->endSetup();