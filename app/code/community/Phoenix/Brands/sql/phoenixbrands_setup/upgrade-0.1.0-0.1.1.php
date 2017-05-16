<?php
$this->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('catalog_setup');

$setup->addAttribute('catalog_category', 'is_featured', array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Is Featured',
        'input'             => 'select',
        'class'             => '',
        'source'            => 'eav/entity_attribute_source_boolean',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'group'             => 'General Information'
));

$this->endSetup();