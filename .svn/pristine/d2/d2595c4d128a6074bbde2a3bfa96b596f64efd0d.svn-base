<?php

class Testimonial_MageDoc_Model_Adminhtml_System_Config_Source_OrderState
{
    protected $_collection;
    const ORDER_STATE_PATH = 'global/order_state';
    
    public function toOptionArray($addEmpty = true)
    {
        foreach (Mage::getConfig()->getNode(self::ORDER_STATE_PATH)->children() as $type) {
            $labelPath = self::ORDER_STATE_PATH . '/' . $type->getName() . '/label';
            $options[] = array(
                'label' => Mage::helper('magedoc')->__((string) Mage::getConfig()->getNode($labelPath)),
                'value' => $type->getName()
            );
        }            

        return $options;
    }
    
    public function getOptionArray()
    {
        $options = $this->toOptionArray(false);
        $optionArray = array();
        foreach ($options as $option){
            $optionArray[$option['value']] = $option['label'];
        }
        return $optionArray;
    }
}