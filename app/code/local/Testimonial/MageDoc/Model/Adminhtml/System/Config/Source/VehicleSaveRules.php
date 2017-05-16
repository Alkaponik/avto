<?php

class Testimonial_MageDoc_Model_Adminhtml_System_Config_Source_VehicleSaveRules
{
    protected $_collection;
    const VEHICLE_SAVE_RULE_PATH = 'global/vehicle_save_rules';
    
    public function toOptionArray($addEmpty = true)
    {
        foreach (Mage::getConfig()->getNode(self::VEHICLE_SAVE_RULE_PATH)->children() as $type) {
            $labelPath = self::VEHICLE_SAVE_RULE_PATH . '/' . $type->getName() . '/label';
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