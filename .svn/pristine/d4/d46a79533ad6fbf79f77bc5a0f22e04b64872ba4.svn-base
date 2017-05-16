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
class Testimonial_MageDoc_Model_Mysql4_Tecdoc_Supplier_Collection extends Testimonial_MageDoc_Model_Mysql4_Collection_Abstract
{

    protected function _construct() 
    {
        $this->_init('magedoc/tecdoc_supplier');
        
         $this->_map['fields']['title'] = 'IFNULL(title, SUP_BRAND)';
         $this->_map['fields']['enabled'] = 'IFNULL(enabled, 0)';
         $this->_map['fields']['td_sup_id'] = 'IFNULL(td_sup_id, SUP_ID)';
    }    
    
    public function getOptionArray()
    {
        $options = array();
        foreach($this->toOptionArray() as $item) {
            $options[$item['value']] = $item['label'];
        }

        return $options;
    }

    public function toOptionArray()
    {
        return $this->_toOptionArray('sup_id', 'sup_brand');
    }
    
    public function joinSuppliers($collection = null)
    {
        if (is_null($collection)){
            $collection = $this;
        }
        if ($collection instanceof Varien_Data_Collection_Db){
            $select = $collection->getSelect();
        }else{
            $select = $collection;
        }
        $select->joinLeft(
            array('md_supplier' => $this->getTable('magedoc/supplier')),
            'main_table.SUP_ID = md_supplier.td_sup_id',
            array(
                'enabled'=> new Zend_Db_Expr('IFNULL(enabled, 0)'),
                'title' => new Zend_Db_Expr('IFNULL(title, SUP_BRAND)'),
                'td_sup_id' => new Zend_Db_Expr('IFNULL(td_sup_id, SUP_ID)'),
                'logo'));
        $this->_joins['supplier'] = $this;
        
        return $this;
    }


    
}
