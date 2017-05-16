<?php
$this->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->addAttribute('catalog_product', 'is_featured_product', array(
    'type'              => 'int',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Is Featured Product',
    'input'             => 'boolean',
    'class'             => '',
    'source'            => 'eav/entity_attribute_source_boolean',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'used_in_product_listing' => true,
    'group'             => 'General'
));

$attr = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'is_featured_product');
$attr_set = Mage::getModel('eav/entity_attribute_set');
$attr_set->addSetInfo('catalog_product', array($attr));

if ($attr) {
    $attr_sets_ids = $setup->getAllAttributeSetIds('catalog_product');
    foreach ($attr_sets_ids as $attr_set_id) {
        if (!$attr->isInSet($attr_set_id)) {
            $setup->addAttributeToSet('catalog_product', $attr_set_id, 'General', 'is_featured_product', 100);
        }
    }
}

$this->endSetup();