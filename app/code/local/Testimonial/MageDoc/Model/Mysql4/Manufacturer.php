<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Articles
 *
 * @author Oleg
 */
class Testimonial_MageDoc_Model_Mysql4_Manufacturer extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_isPkAutoIncrement = false;
        $this->_init('magedoc/manufacturer', 'td_mfa_id');
    }    
    
}

