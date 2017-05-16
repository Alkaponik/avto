<?php
class Testimonial_MageDoc_Model_Source_Stock_Status extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const RETAILER_INPORT_MODELS_PATH = 'global/stock_statuses';
    const OUT_OF_STOCK = 0;
    const IN_STOCK = 1;
    const AVAILABLE_FOR_PURCHASE = 2;

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
            $valuePath = self::RETAILER_INPORT_MODELS_PATH . '/' . $model->getName() . '/' . 'id';
            $value = (string) Mage::getConfig()->getNode($valuePath);
            $label = (string) Mage::getConfig()->getNode($labelPath);
            $optionsAarray[$model->getName()] = array(
                'label'  => Mage::helper('magedoc')->__($label),
                'value'  => $value,
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