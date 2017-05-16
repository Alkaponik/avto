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
class Testimonial_MageDoc_Model_Mysql4_Tecdoc_LaCriteria_Collection extends Testimonial_MageDoc_Model_Mysql4_Collection_Abstract
{

    protected function _construct() 
    {
        $this->_init('magedoc/tecdoc_laCriteria');
    }   

    public function addProductFilter($productId)
    {
        if ($productId instanceof Mage_Catalog_Model_Product)
        {
            $productId = $productId ->getTdArtId();
        }
        $this->getSelect()
                ->where("ACR_ART_ID = $productId");
        return $this;
    }
    
    public function joinCriteriaDesignation($collection = null)
    {
        if(is_null($collection)){
            $collection = $this;
        }

        $collection->joinDesignation($collection, 'main_table', 'LAC_KV_DES_ID', array('criteria_value_text' =>
        new Zend_Db_Expr('IFNULL(td_desText.TEX_TEXT, td_laCriteria.LAC_VALUE)')));
        $collection->getSelect()->joinLeft(array('td_criteria' => $this->getTable('magedoc/tecdoc_criteria')), "td_criteria.CRI_ID = main_table.LAC_CRI_ID", '');
        $collection->joinDesignation($collection, 'td_criteria',
                'CRI_DES_ID', array('criteria' => 'td_desText1.TEX_TEXT'));

        return $collection;
    }
    
    

}


