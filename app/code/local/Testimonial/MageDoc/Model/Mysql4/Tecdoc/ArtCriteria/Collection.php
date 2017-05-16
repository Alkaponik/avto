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
class Testimonial_MageDoc_Model_Mysql4_Tecdoc_ArtCriteria_Collection extends Testimonial_MageDoc_Model_Mysql4_Collection_Abstract
{

    protected function _construct() 
    {
        $this->_init('magedoc/tecdoc_artCriteria');
    }
    
    public function addProductFilter($tdAtrId)
    {
        if ($tdAtrId instanceof Mage_Catalog_Model_Product)
        {
            $tdAtrId = $tdAtrId ->getTdArtId();
        }
        if ($tdAtrId){
            $this->getSelect()
                ->where("ACR_ART_ID = ?", $tdAtrId);
        } else {
            $this->_setIsLoaded(true);
        }

        return $this;
    }
    
    public function joinCriteriaDesignation($collection = null, $joinAlias = 'main_table', $criteriaNameColumns = null, $criteriaColumns = null)
    {
        if (is_null($collection)){
            $collection = $this;
        }
        if (is_null($criteriaNameColumns)){
            $criteriaNameColumns = array('criteria' => '{{des_text}}.TEX_TEXT');
        }
        if (is_null($criteriaColumns)){
            $criteriaColumns = array('criteria_value_text' =>
            new Zend_Db_Expr("IFNULL({{des_text}}.TEX_TEXT, {$joinAlias}.ACR_VALUE)"));
        }
        $this->joinDesignation($collection, $joinAlias, 'ACR_KV_DES_ID', $criteriaColumns);
        $collection->getSelect()->joinLeft(array('td_criteria' => $this->getTable('magedoc/tecdoc_criteria')), "CRI_ID = ACR_CRI_ID", '');
        $this->joinDesignation($collection, 'td_criteria',
                'CRI_DES_ID', $criteriaNameColumns);

        return $this;
    }

    public function joinArtCriteria($collection = null, $criteriaIds = array(), $joinTableAlias = 'main_table', $joinTableColumn = 'ART_ID', $columns = '', $artCriteriaTableAlias = 'td_art_criteria', $joinExpression = null)
    {
        if(is_null($collection)){
            $collection = $this;
        }
        $adapter = $collection->getConnection();
        if (is_null($joinExpression)){
            $joinExpression = "{$joinTableAlias}.{$joinTableColumn} = {$artCriteriaTableAlias}.ACR_ART_ID";
        }
        $criIdExpr = !empty($criteriaIds)
            ? $adapter->quoteInto("{$artCriteriaTableAlias}.ACR_CRI_ID IN (?)", $criteriaIds)
            : '';
        $collection->getSelect()
            ->joinLeft(array($artCriteriaTableAlias => $this->getMainTable()),
            $joinExpression." AND {$criIdExpr}" ,
            $columns);

        return $this;

    }
}