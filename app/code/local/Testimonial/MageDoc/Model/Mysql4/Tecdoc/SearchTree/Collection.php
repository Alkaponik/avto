<?php

class Testimonial_MageDoc_Model_Mysql4_Tecdoc_SearchTree_Collection extends Testimonial_MageDoc_Model_Mysql4_Collection_Abstract
{

     
    /**
     * Current scope (store Id)
     *
     * @var int
     */
    protected $_storeId;

    /**
     * Set store scope
     *
     * @param int|string|Mage_Core_Model_Store $store
     * @return Mage_Catalog_Model_Resource_Collection_Abstract
     */
    public function setStore($store)
    {
        $this->setStoreId(Mage::app()->getStore($store)->getId());
        return $this;
    }

    /**
     * Set store scope
     *
     * @param int|string|Mage_Core_Model_Store $storeId
     * @return Mage_Catalog_Model_Resource_Collection_Abstract
     */
    public function setStoreId($storeId)
    {
        if ($storeId instanceof Mage_Core_Model_Store) {
            $storeId = $storeId->getId();
        }
        $this->_storeId = (int)$storeId;
        return $this;
    }

    /**
     * Return current store id
     *
     * @return int
     */
    public function getStoreId()
    {
        if (is_null($this->_storeId)) {
            $this->setStoreId(Mage::app()->getStore()->getId());
        }
        return $this->_storeId;
    }

    /**
     * Retrieve default store id
     *
     * @return int
     */
    public function getDefaultStoreId()
    {
        return Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
    }
    
    protected function _construct() 
    {
        $this->_init('magedoc/tecdoc_searchTree');
    }   
    
    
    public function joinPath()
    {
        $modelCategory = Mage::getModel('catalog/category')->load(1); // root category
        
        $attr = $modelCategory->getResource()->getAttribute('is_active');
        
        $this->getSelect()
                 ->joinInner(array('md_searchTree' => $this->getTable('magedoc/searchTree')), 
                    'main_table.STR_ID = md_searchTree.str_id', 
                    'path');
         
        return $this;
    }

    
    public function joinSearchTree($collection = null, $joinAlias = 'main_table', $columns = '')
    {
         
        if(is_null($collection)){
            $collection = $this;
        }
        $resource = Mage::getResourceSingleton('catalog/category');
        
        $attributeTdStrId = $resource->getAttribute('td_str_id');
        $categoryTable = $attributeTdStrId->getBackendTable();
        $collection->setJoin('td_linkArtGA', $this);
        
        $collection->getSelect()
          ->joinInner(array('td_linkArtGA' => $this->getTable('magedoc/tecdoc_linkArtGA')),
                "td_linkArtGA.LAG_ART_ID = {$joinAlias}.ART_ID AND td_linkArtGA.LAG_SUP_ID = {$joinAlias}.ART_SUP_ID", array(''))
          ->joinInner(array('td_linkGAStr' => $this->getTable('magedoc/tecdoc_linkGAStr')),
                 "td_linkGAStr.LGS_GA_ID = td_linkArtGA.LAG_GA_ID", array(''))
          ->joinInner(array('catalog_category_entity' => $categoryTable),
                  "catalog_category_entity.td_str_id = td_linkGAStr.LGS_STR_ID",
                    $columns);

        return $this;
    }

    public function joinCategory()
    {
        $model = Mage::getModel('catalog/category')->getResource();
        $isActiveAttribute = $model->getAttribute('is_active');
        $tableActive = $isActiveAttribute->getBackendTable();
     
        $attributeTdStrId = $model->getAttribute('td_str_id');
        $tableTdStrId = $attributeTdStrId->getBackendTable();
       
     
         $this->getSelect()
                 ->joinLeft(array('category_entity' => $model->getTable($tableTdStrId)), 
                    'main_table.STR_ID = category_entity.td_str_id', 
                    array('entity_id' => 'category_entity.entity_id'))
                 ->joinLeft(array('category_entity_int' => $model->getTable($tableActive)), 
                    "category_entity_int.entity_id = category_entity.entity_id 
                         AND category_entity_int.attribute_id = {$isActiveAttribute->getId()}
                         AND category_entity_int.store_id = {$this->getStoreId()}", 
                    array('is_active' => new Zend_Db_Expr('IFNULL(category_entity_int.value, 0)')));
        
     
         return $this;
    }
    
    
    public function addIdFilter($categoryIds)
    {
        if (is_array($categoryIds)) {
            if (empty($categoryIds)) {
                $condition = '';
            } else {
                $condition = array('in' => $categoryIds);
            }
        } elseif (is_numeric($categoryIds)) {
            $condition = $categoryIds;
        } elseif (is_string($categoryIds)) {
            $ids = explode(',', $categoryIds);
            if (empty($ids)) {
                $condition = $categoryIds;
            } else {
                $condition = array('in' => $ids);
            }
        }
        $this->addFieldToFilter('main_table.STR_ID', $condition);
     
        return $this;
    }
}
