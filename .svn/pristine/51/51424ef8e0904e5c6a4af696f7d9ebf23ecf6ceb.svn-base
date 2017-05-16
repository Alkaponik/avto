<?php
class Phoenix_Brands_Model_Source_CategoryBlockType extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions($withEmpty=true)
    {
        if (!$this->_options) {
            $this->_options = array(                
                array(
                    'value' => Phoenix_Brands_Model_Catalog_Category::BLOCK_TYPE_BRAND,
                    'label' => Mage::helper('phoenixbrands')->__('Brand'),
                    ),
                array(
                    'value' => Phoenix_Brands_Model_Catalog_Category::BLOCK_TYPE_COLLECTION,
                    'label' => Mage::helper('phoenixbrands')->__('Brand Collection'),
                    ),
                );
        }
        $options = $this->_options;
        if ($withEmpty) {
            array_unshift($options, array(
                'value' => '',
                'label' => Mage::helper('core')->__('-- Please Select --'))
            );
        }
        return $options;
    }
}