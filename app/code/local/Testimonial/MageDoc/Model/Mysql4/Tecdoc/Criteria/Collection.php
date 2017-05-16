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

class Testimonial_MageDoc_Model_Mysql4_Tecdoc_Criteria_Collection extends Testimonial_MageDoc_Model_Mysql4_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('magedoc/tecdoc_criteria');

        $this->_map['fields']['enabled'] = 'IFNULL(enabled, 0)';
        $this->_map['fields']['td_cri_id'] = 'IFNULL(td_cri_id, CRI_ID)';
    }
    
    public function joinCriteria()
    {
        if (isset($this->_joins['criteria'])){
            return $this->_joins['criteria'];
        }
        
        $this->getSelect()
        ->joinLeft(array('md_criteria' => $this->getTable('magedoc/criteria')),
                    'main_table.CRI_ID = md_criteria.td_cri_id',
                    array('enabled'     => new Zend_Db_Expr('IFNULL(enabled, 0)'),
                        'is_import_enabled'     => new Zend_Db_Expr('IFNULL(is_import_enabled, 0)'),
                        'name'       => new Zend_Db_Expr('IFNULL(name, td_desText.TEX_TEXT)'),
                        'td_cri_id'   => new Zend_Db_Expr('IFNULL(td_cri_id, CRI_ID)'),
                        'attribute_code' => 'attribute_code'
                        ));
        $this->_map['fields']['name'] = 'IFNULL(name, td_desText.TEX_TEXT)';
        $this->_joins['criteria'] = $this;
        return $this;
    }

    public function addEnabledFilter($enabled = true)
    {
        return $this->addFieldToFilter('enabled', $enabled);
    }
}
