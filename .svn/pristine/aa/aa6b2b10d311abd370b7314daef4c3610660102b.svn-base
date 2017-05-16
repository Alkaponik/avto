<?php

$this->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('catalog_setup');

$setup->addAttribute('catalog_category', 'hp_position', array(
        'type'              => 'int',
        'label'             => 'Position on Homepage',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'sort_order'        => 10
));


$setup->updateAttribute('catalog_category', 'hp_position', 'is_global', Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE);
$this->endSetup();