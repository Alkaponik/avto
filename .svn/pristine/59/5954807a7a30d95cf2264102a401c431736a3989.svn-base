<?php

$this->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('catalog_setup');

$setup->addAttribute('catalog_category', 'block_type', array(
    'type'              => 'int',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Block Type',
    'input'             => 'select',
    'class'             => '',
    'source'            => 'phoenixbrands/source_categoryBlockType',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'group'             => 'Display Settings'
));

$setup->addAttribute('catalog_category', 'logo_image', array(
        'type'              => 'varchar',
        'backend'           => 'catalog/category_attribute_backend_image',
        'frontend'          => '',
        'label'             => 'Brand Logo Image',
        'input'             => 'image',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'sort_order'        => 5
));

$setup->addAttribute('catalog_category', 'seo_image', array(
        'type'              => 'varchar',
        'backend'           => 'catalog/category_attribute_backend_image',
        'frontend'          => '',
        'label'             => 'Brand Seo Page Image',
        'input'             => 'image',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'sort_order'        => 5
));

$setup->addAttribute('catalog_product', 'brand_category_id', array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Brand Category',
        'input'             => 'label',
        'class'             => '',        
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'           => false,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'used_in_product_listing' => true
));

$setup->addAttribute('catalog_product', 'collection_category_id', array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Collection Category',
        'input'             => 'label',
        'class'             => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => true,
        'unique'            => false,
));



$this->endSetup();