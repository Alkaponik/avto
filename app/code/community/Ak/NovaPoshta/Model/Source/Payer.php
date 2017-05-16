<?php

class Ak_NovaPoshta_Model_Source_Payer extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        return $this->_options = array(
            array('value' => 0, 'label' => Mage::helper('novaposhta')->__('Recipient')),
            array('value' => 1, 'label' => Mage::helper('novaposhta')->__('Sender')),
            array('value' => 2, 'label' => Mage::helper('novaposhta')->__('Third person'))
        );
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}