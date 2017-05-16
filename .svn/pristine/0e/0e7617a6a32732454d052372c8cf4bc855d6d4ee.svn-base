<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Criteria
 *
 * @author Oleg
 */
class Testimonial_MageDoc_Model_Mysql4_Tecdoc_SearchTree extends Testimonial_MageDoc_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('magedoc/tecdoc_searchTree', 'STR_ID');
    }   
    
    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param Mage_Core_Model_Abstract $object
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->joinInner(
                array(
                    'md_searchTree' => $this->getTable('magedoc/searchTree')), 
                "{$this->getMainTable()}.STR_ID = md_searchTree.str_id", 
                'path');
        return $select;
    }
}

