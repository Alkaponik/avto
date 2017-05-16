<?php

class Testimonial_MageDoc_Model_Mysql4_Model_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('magedoc/model');
    }
    
    public function addEnabledFilter($enabled = true)
    {
        return $this->addFieldToFilter('enabled', $enabled);
    }
}
