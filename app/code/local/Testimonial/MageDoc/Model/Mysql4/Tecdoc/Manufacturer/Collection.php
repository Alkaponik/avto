<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Collection
 *
 * @author Oleg
 */

class Testimonial_MageDoc_Model_Mysql4_Tecdoc_Manufacturer_Collection extends Testimonial_MageDoc_Model_Mysql4_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('magedoc/tecdoc_manufacturer');
        
         $this->_map['fields']['title'] = 'IFNULL(title, MFA_BRAND)';
         $this->_map['fields']['enabled'] = 'IFNULL(enabled, 1)';
         $this->_map['fields']['td_mfa_id'] = 'IFNULL(td_mfa_id, MFA_ID)';
    }
    
    public function joinManufacturers()
    {
        if (isset($this->_joins['manufacturer'])){
            return $this->_joins['manufacturer'];
        }

        $this->getResource()->prepareFullSelect($this->getSelect(), 'main_table');

        $this->_joins['manufacturer'] = $this;
        return $this;
    }

    public function addEnabledFilter($enabled = true)
    {
        $this->addFieldToFilter('enabled', $enabled);

        return $this;
    }
}
