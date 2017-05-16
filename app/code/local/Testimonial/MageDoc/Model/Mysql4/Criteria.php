<?php

class Testimonial_MageDoc_Model_Mysql4_Criteria extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_isPkAutoIncrement = false;
        $this->_init('magedoc/criteria', 'td_cri_id');
    }    
    
}

