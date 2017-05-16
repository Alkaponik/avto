<?php

class Testimonial_MageDoc_Model_Source_Retailer extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions($withEmpty = true)
    {
        if (is_null($this->_options)) {
            $this->_options = Mage::getResourceSingleton('magedoc/retailer_collection')
                    ->addFieldToFilter('enabled', 1)->toOptionArray();
        }
        $options = $this->_options;
        if ($withEmpty){
            array_unshift($options, array('value'=>'', 'label'=>Mage::helper('magedoc')->__('--Please Select--')));
        }
        return $options;
    }
    
    public function getOptionArray()
    {
        $options = $this->getAllOptions(false);
        $optionArray = array();
        foreach($options as $option){
            $optionArray[$option['value']] = $option['label'];
        }
        return $optionArray;
    }
    
}
