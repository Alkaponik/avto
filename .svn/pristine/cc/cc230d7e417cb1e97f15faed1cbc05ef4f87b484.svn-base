<?php

class Testimonial_MageDoc_Model_Mysql4_Supplier_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    
    protected function _construct()
    {
            $this->_init('magedoc/supplier');
    }

    public function toOptionArray()
    {
        return $this->_toOptionArray('td_sup_id', 'title');
    }
    
    public function joinSuppliers($collection = null, $joinAlias = 'main_table', $columns = '')
    {
        if(is_null($collection)){
            $collection = $this;
        }
        if ($collection instanceof Varien_Data_Collection_Db){
            $select = $collection->getSelect();
        }else{
            $select = $collection;
        }
        $select->joinInner(
            array('md_supplier' => $this->getTable('magedoc/supplier')),
            "md_supplier.td_sup_id = {$joinAlias}.supplier_id AND md_supplier.enabled = 1",
            $columns);
        return $collection;
    }

}
