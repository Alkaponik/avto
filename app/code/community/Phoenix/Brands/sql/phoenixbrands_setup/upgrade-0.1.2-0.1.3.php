<?php

$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('catalog_setup');
$conn = $installer->getConnection();

$setup->addAttribute('catalog_category', 'brand', array(
    'group'                     => 'Brand',
    'type'                      => 'static',
    'backend'                   => '',
    'frontend'                  => '',
    'label'                     => 'Brand',
    'input'                     => 'select',
    'source'                    => 'phoenixbrands/eav_entity_attribute_source_override',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'                   => true,
    'required'                  => false,
    'user_defined'              => false,
    'group'                     => 'Display Settings',
));

$tableName = $installer->getTable('catalog/category');
$conn->addColumn($tableName, 'brand', 'INT(11) unsigned NULL');
$conn->addKey($tableName, $installer->getIdxName('catalog/category', array('brand')), 'brand');

$installer->endSetup();