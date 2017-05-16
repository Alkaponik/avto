<?php

class Testimonial_MageDoc_Model_Mysql4_Retailer_Config_Supply extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_isPkAutoIncrement = false;

    protected function _construct()
    {
        $this->_init('magedoc/retailer_config_supply', 'retailer_id');
    }
    
}


