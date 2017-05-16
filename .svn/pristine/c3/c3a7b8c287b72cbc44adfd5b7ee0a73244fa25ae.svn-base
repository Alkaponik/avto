<?php

class Ak_NovaPoshta_Model_Source_City extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        $this->_options = array();
        $this->_options = Mage::getModel('novaposhta/city')->getOptionArray();
        return $this->_options;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}