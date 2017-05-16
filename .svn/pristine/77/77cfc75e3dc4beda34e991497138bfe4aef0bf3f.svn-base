<?php
class Ak_NovaPoshta_Model_Consignment extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('novaposhta/consignment');
    }
    
    public function loadByTtn($number)
    {
        $this->load($number, 'ttn');
        return $this;
    }

    public function canUpdate()
    {
        if (($interval = Mage::helper('novaposhta')->getMinUpdateTimeout()) && $this->getUpdatedAt()) {
            $date = Mage::app()->getLocale()->date()->sub($interval, Zend_Date::MINUTE);
            if ($date->compare(strtotime($this->getUpdatedAt())) == -1)
            {
                return false;
            }
        }
        return true;
    }
}





























