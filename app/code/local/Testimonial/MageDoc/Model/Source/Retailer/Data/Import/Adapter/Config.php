<?php
class Testimonial_MageDoc_Model_Source_Retailer_Data_Import_Adapter_Config
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions($withEmpty = true)
    {
        if (is_null($this->_options)) {
            $this->_options = Mage::getResourceModel('magedoc/retailer_data_import_adapter_config_collection')
               ->toOptionArray();
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