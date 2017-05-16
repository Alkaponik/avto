<?php
class Testimonial_Intime_Model_Consignment extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('intime/consignment');
    }
    
    public function loadByTtn($number)
    {
        return $this->load($number, 'ttn');
    }

    public function canUpdate()
    {
        if (($interval = Mage::helper('intime')->getMinUpdateTimeout()) && $this->getUpdatedAt()) {
            $date = Mage::app()->getLocale()->date()->sub($interval, Zend_Date::MINUTE);
            if ($date->compare(strtotime($this->getUpdatedAt())) == -1)
            {
                return false;
            }
        }
        return true;
    }
}





























