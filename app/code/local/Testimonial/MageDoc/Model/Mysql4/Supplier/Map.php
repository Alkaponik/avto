<?php


class Testimonial_MageDoc_Model_Mysql4_Supplier_Map extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_isPkAutoIncrement = false;
        $this->_init('magedoc/supplier_map', 'map_id');
    }    
    
}


