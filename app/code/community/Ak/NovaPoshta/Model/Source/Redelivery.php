<?php

class Ak_NovaPoshta_Model_Source_Redelivery extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        return $this->_options = array(
            array('value' => 1, 'label' => Mage::helper('novaposhta')->__('Documents')),
            array('value' => 2, 'label' => Mage::helper('novaposhta')->__('Cash')),
            array('value' => 3, 'label' => Mage::helper('novaposhta')->__('Trays (containers)')),
            array('value' => 4, 'label' => Mage::helper('novaposhta')->__('Product')),
            array('value' => 5, 'label' => Mage::helper('novaposhta')->__('other'))
        );
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}