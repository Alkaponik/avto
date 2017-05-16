<?php

class Testimonial_MageDoc_Model_Source_Retailer_Import_Model extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const RETAILER_INPORT_MODELS_PATH = 'global/retailer_data_models';

    public function getAllOptions($withEmpty=true)
    {
        if (empty($this->_options)) {

            $this->_options = $this->toOptionArray();
            
        }
        if ($withEmpty) {
            array_unshift($this->_options, array(
                'value'=>'',
                'label'=>Mage::helper('core')->__('-- Please Select --'))
            );
        }
        return $this->_options;
    }

    public function toOptionArray()
    {
        $optionsAarray = array();
        foreach (Mage::getConfig()->getNode(self::RETAILER_INPORT_MODELS_PATH)->children() as $model) {
            $labelPath = self::RETAILER_INPORT_MODELS_PATH . '/' . $model->getName() . '/' . 'label';
            $valuePath = self::RETAILER_INPORT_MODELS_PATH . '/' . $model->getName() . '/' . 'class';
            $value = (string) Mage::getConfig()->getNode($valuePath);
            $label = (string) Mage::getConfig()->getNode($labelPath);
            $optionsAarray[$model->getName()] = array(
                'label'  => Mage::helper('magedoc')->__($label),
                'value'  => $model->getName(),
                'model'  => $value
            );
        }
        
        return $optionsAarray;
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
