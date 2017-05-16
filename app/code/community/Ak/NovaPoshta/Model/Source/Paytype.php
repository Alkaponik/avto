<?php

class Ak_NovaPoshta_Model_Source_Paytype extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        return $this->_options = array(
            array('value' => 1, 'label' => Mage::helper('novaposhta')->__('Cash')),
            array('value' => 2, 'label' => Mage::helper('novaposhta')->__('Payment by the bank'))
        );
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}