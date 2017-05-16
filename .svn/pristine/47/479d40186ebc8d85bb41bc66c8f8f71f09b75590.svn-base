<?php

class Ak_NovaPoshta_Model_Source_Method extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        $this->_options = array();
        $this->_options = Mage::helper('payment')->getPaymentMethodList(TRUE, TRUE);
        return $this->_options;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}