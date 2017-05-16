<?php

class Testimonial_MageDoc_Model_Criteria extends Mage_Core_Model_Abstract
{
    const ATTRIBUTE_CODE_NEW = '__add_new__';

    protected function _construct()
    {
        $this->_init('magedoc/criteria');
    }

    protected function _beforeSave()
    {
        if ($this->getAttributeCode() == self::ATTRIBUTE_CODE_NEW){
            $attributeCode = $this->_importAttribute();
            $this->setAttributeCode($attributeCode);
        }
    }

    protected function _importAttribute()
    {
        $setup = new Mage_Eav_Model_Entity_Setup('catalog_setup');
        $attributeCode = preg_replace('/\s+/', '_', mb_strtolower($this->getDefaultName(), 'UTF-8'));
        $setup->addAttribute('catalog_product', $attributeCode, array(
            'group'                     => 'MageDoc',
            'type'                      => 'int',
            'backend'                   => '',
            'frontend'                  => '',
            'label'                     => $this->getDefaultName(),
            'input'                     => 'select',
            'source'                    => 'eav/entity_attribute_source_table',
            'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
            'visible'                   => true,
            'required'                  => false,
            'user_defined'              => true,
            'searchable'                => false,
            'filterable'                => true,
            'comparable'                => false,
            'default'                   => '',
            'apply_to'                  => 'spare',
        ));
        return $attributeCode;
    }
}

