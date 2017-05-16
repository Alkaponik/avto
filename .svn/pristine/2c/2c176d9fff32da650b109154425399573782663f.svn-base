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

class Testimonial_MageDoc_Model_Mysql4_Tecdoc_Article_Collection extends Testimonial_MageDoc_Model_Mysql4_Collection_Abstract 
{
    protected function _construct()
    {
            $this->_init('magedoc/tecdoc_article');
    }

    public function joinAttribute($attributeName, $collection = null, $joinTable = 'catalog_product_entity', $storeId = 0)
    {
        if (is_null($collection)) {
            $collection = $this;
        }
        $alias = '{{table_alias}}';
        switch ($attributeName) {
            case 'name':
                $columnExpression = "IFNULL({$alias}.value, CONCAT(td_desText.TEX_TEXT, ' ', md_supplier.title, ' ', main_table.ART_ARTICLE_NR))";
                break;
            case 'status':
                $columnExpression = "IFNULL({$alias}.value, 2)";
                break;
            default:
                $columnExpression = null;
                break;
        }
        $this->joinProductAttribute($attributeName, $collection, $joinTable, null, $storeId, $columnExpression);

        return $this;
    }
    
    public function joinProductAttribute($attributeName, $collection = null, $joinTable = 'catalog_product_entity', $joinColumn = null, $storeId = 0, $columnExpression = null, $columnAlias = null)
    {
        Mage::helper('magedoc')->joinProductAttribute($attributeName, $collection, $joinTable, $joinColumn, $storeId, $columnExpression, $columnAlias);

        return $this;
    }
    
    public function joinProductsWithCategory()
    {
        $resource = Mage::getModel('catalog/category')->getResource();
        
        $attributeTdStrId = $resource->getAttribute('td_str_id');
        $tableTdStrId = $attributeTdStrId->getBackendTable();
        $categoryTable = $resource->getTable($tableTdStrId);
        $this->getSelect()
                ->joinInner(array('td_article_normalized'=> $this->getTable('magedoc/tecdoc_articleNormalized')),
                    "td_article_normalized.ARN_ART_ID = main_table.ART_ID",'')
                ->joinInner(array('td_designation' => $this->getTable('magedoc/tecdoc_designation')),
                        "td_designation.DES_ID = main_table.ART_COMPLETE_DES_ID AND td_designation.DES_LNG_ID = {$this->getLngId()}", '')
                ->joinInner(array('td_desText' => $this->getTable('magedoc/tecdoc_desText')), 'td_desText.TEX_ID = td_designation.DES_TEX_ID', 
                        array('category' => 'td_desText.TEX_TEXT'))
                ->joinInner(array('md_supplier' => $this->getTable('magedoc/supplier')), "md_supplier.td_sup_id = main_table.ART_SUP_ID AND md_supplier.enabled = 1",
                        array('supplier_title' => 'md_supplier.title'))
                ->joinInner(array('td_linkArtGA' => $this->getTable('magedoc/tecdoc_linkArtGA')),
                        "td_linkArtGA.LAG_ART_ID = main_table.ART_ID AND main_table.ART_SUP_ID = td_linkArtGA.LAG_SUP_ID", 
                                array(''))
                ->joinInner(array('td_linkGAStr' => $this->getTable('magedoc/tecdoc_linkGAStr')),
                                        "td_linkGAStr.LGS_GA_ID = td_linkArtGA.LAG_GA_ID", array(''))
                ->joinInner(array('catalog_category_entity' => $categoryTable),
                                        "catalog_category_entity.td_str_id = td_linkGAStr.LGS_STR_ID",
                                        array('category_id' => 'catalog_category_entity.entity_id', 'td_str_id' => new Zend_Db_Expr('GROUP_CONCAT(td_str_id)')))
                ->joinLeft(array('catalog_product_entity' => $this->getTable('catalog/product')),
                                        "catalog_product_entity.td_art_id = main_table.ART_ID",
                                        array('entity_id' => 'catalog_product_entity.entity_id', 'td_art_id' => 'td_art_id'));
                
        return $this;
    }
    
    public function joinArticles($collection = null, $joinAlias = 'main_table', $columns = '', $joinArticlesNormalized = true)
    {
        if(is_null($collection)){
            $collection = $this;
        }
        $collectionSelect =  $collection->getSelect();

        if( !$joinArticlesNormalized ) {
            $collectionSelect->joinInner(
                array('td_article'=> $this->getTable('magedoc/tecdoc_article')),
                "td_article.ART_ID = {$joinAlias}.td_art_id",
                $columns
            );
        } else {
            $this->joinArticlesNormalized($collection, $joinAlias, $columns = '');
            $collectionSelect
                    ->joinInner(array('td_article'=> $this->getTable('magedoc/tecdoc_article')),
                            "td_article.ART_ID = td_article_normalized.ARN_ART_ID",
                            $columns);
        }
        return $this;
    }

    public function joinArticlesNormalized($collection = null, $joinAlias = 'main_table', $columns = '')
    {
        if(is_null($collection)){
            $collection = $this;
        }
        $collectionSelect =  $collection->getSelect();

        $collectionSelect
            ->joinInner(array('td_article_normalized'=> $this->getTable('magedoc/tecdoc_articleNormalized')),
                "td_article_normalized.ARN_ARTICLE_NR_NORMALIZED = {$joinAlias}.code_normalized
                                AND td_article_normalized.ARN_SUP_ID = {$joinAlias}.supplier_id",
                $columns);

        return $this;
    }
    
    public function getArticleAdditionalCriteria($typeId)
    {
        $laCrtiteriaCollection = Mage::getResourceModel('magedoc/tecdoc_laCriteria_collection');
        $this->getSelect()
                ->joinInner(array('td_linkArt' => $this->getTable('magedoc/tecdoc_linkArt')),
                        'td_linkArt.LA_ART_ID = main_table.ART_ID' , '')
                ->joinInner(array('td_linkLaTyp' => $this->getTable('magedoc/tecdoc_linkLaTyp')),
                        'td_linkArt.LA_ID = td_linkLaTyp.LAT_LA_ID' , '')
                ->joinInner(array('td_laCriteria' => $this->getTable('magedoc/tecdoc_laCriteria')),
                        'td_laCriteria.LAC_LA_ID = td_linkArt.LA_ID' , 'LAC_LA_ID')
                ->joinInner(array('catalog_product' => $this->getTable('catalog/product')),
                        'catalog_product.td_art_id = main_table.ART_ID')
                ->joinLeft(array('td_criteria' => $this->getTable('magedoc/tecdoc_criteria')),
                        "td_criteria.CRI_ID = td_laCriteria.LAC_CRI_ID", '');
        
        if(is_array($typeId)){
            $this->addFieldToFilter('td_linkLaTyp.LAT_TYP_ID', array('in' => $typeId));
        }else{
            $this->addFieldToFilter('td_linkLaTyp.LAT_TYP_ID', array('eq' => $typeId));            
        }
        
        $this->joinDesignation($this, 'td_laCriteria', 'LAC_KV_DES_ID', array('criteria_value_text' =>
        new Zend_Db_Expr('IFNULL(td_desText.TEX_TEXT, td_laCriteria.LAC_VALUE)')));
        $this->joinDesignation($this, 'td_criteria',
                'CRI_DES_ID', array('criteria' => 'td_desText1.TEX_TEXT'));
        $this->getSelect()->group('LAC_LA_ID');
        $this->_setIdFieldName('LAC_LA_ID');

        return $this;
        
    }

    public function joinGraphics($collection = null, $joinAlias = 'main_table', $columns = null)
    {
        if(is_null($collection)){
            $collection = $this;
        }
        $this->getResource()->joinGraphics($collection->getSelect(), $joinAlias, $columns);
        return $this;
    }
    
    public function addSupplierFilter($supplier)
    {
        $this->addFieldToFilter('ART_SUP_ID', $supplier);
        return $this;
    }

    public function addProductFilter($tdAtrId)
    {
        if ($tdAtrId instanceof Mage_Catalog_Model_Product)
        {
            $tdAtrId = $tdAtrId ->getTdArtId();
        }
        if ($tdAtrId){
            $this->addFieldToFilter('ART_ID', $tdAtrId);
        } else {
            $this->_setIsLoaded(true);
        }

        return $this;
    }

    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->reset(Zend_Db_Select::GROUP);

        $countSelect->columns("COUNT(DISTINCT {$this->getResource()->getIdFieldName()})");

        return $countSelect;
    }
}