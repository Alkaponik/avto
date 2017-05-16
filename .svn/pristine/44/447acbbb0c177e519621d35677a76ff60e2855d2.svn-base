<?php
class Testimonial_MageDoc_Model_Source_Retailer_Config_Supply extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const IMPORT_MODEL_CONFIG_PATH = 'global/delivery_type';

    public function getAllOptions($withEmpty=true)
    {
        if (empty($this->_options)) {

            $this->_options = $this->toOptionArray();

        }
        if ($withEmpty) {
            array_unshift($this->_options,
                array(
                     'value'=>'',
                     'label'=>Mage::helper('core')->__('-- Please Select --')
                )
            );
        }
        return $this->_options;
    }

    public function toOptionArray()
    {
        $optionsAarray = array();
        foreach (Mage::getConfig()->getNode(static::IMPORT_MODEL_CONFIG_PATH)->children() as $model) {
            $labelPath = static::IMPORT_MODEL_CONFIG_PATH . '/' . $model->getName() . '/' . 'label';
            $label = (string) Mage::getConfig()->getNode($labelPath);
            $optionsAarray[$model->getName()] = array(
                'label'  => Mage::helper('magedoc')->__($label),
                'value'  => $model->getName(),
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