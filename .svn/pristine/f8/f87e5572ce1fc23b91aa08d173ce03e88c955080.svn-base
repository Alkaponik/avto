<?php

class Testimonial_Intime_Model_Source_Tracking_Abstract extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    protected $_pathToXml = '';

    public function getAllOptions()
    {
        $this->_options = array();
        foreach (Mage::getConfig('intime')->getNode($this->_pathToXml)->children() as $type) {
            $labelPath = $this->_pathToXml . '/' . $type->getName() . '/label';
            $valuePath = $this->_pathToXml . '/' . $type->getName() . '/value';
            $value = (string) Mage::getConfig('intime')->getNode($valuePath);
            $this->_options[$value] = Mage::helper('intime')->__((string) Mage::getConfig()->getNode($labelPath));
        }
        return $this->_options;
    }

}