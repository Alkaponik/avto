<?php

class PHoenix_LayeredNav_Model_Source_FilterableAttribute
{
    protected $_collection;

    /**
     * Retrieve option values array
     *
     * @return array
     */
    public function toOptionArray($withEmpty = true)
    {
        $options = array();
        if ($withEmpty){
            $options[] = array(
                'label' => Mage::helper('phoenix_layerednav')->__('No Associated Attribute'),
                'value' => ''
            );
        }
        $collection = $this->getCollection();
        foreach ($collection as $attribute) {
            $options[] = array(
                'label' => Mage::helper('catalog')->__($attribute['frontend_label']),
                'value' => $attribute['attribute_code']
            );
        }
        return $options;
    }

    public function getCollection()
    {
        if (!isset($this->_collection)){
            $this->_collection = Mage::getResourceModel('catalog/product_attribute_collection')
                ->setItemObjectClass('catalog/resource_eav_attribute')
                ->addIsFilterableFilter();
        }
        return $this->_collection;
    }
}
