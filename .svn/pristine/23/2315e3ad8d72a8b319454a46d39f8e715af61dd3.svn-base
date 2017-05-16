<?php
$this->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('catalog_setup');

$setup->addAttribute('catalog_category', 'graphical_headline', array(
        'type'              => 'varchar',
        'backend'           => 'catalog/category_attribute_backend_image',
        'frontend'          => '',
        'label'             => 'Graphical Headline',
        'input'             => 'image',
        'class'             => '',
        'source'            => '',
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
        'sort_order'        => 5
));

$this->endSetup();