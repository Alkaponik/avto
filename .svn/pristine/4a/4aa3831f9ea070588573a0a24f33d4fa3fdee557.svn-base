<?php

class Testimonial_MageDoc_Model_Mysql4_Import_Retailer_Data_Collection extends Testimonial_MageDoc_Model_Resource_Collection_Abstract
{
    protected $_importModel;
    protected $_storeId;
    const DELIMITER = '; ';

    protected function _construct()
    {
        $this->_init('magedoc/import_retailer_data');
    }
    
    public function setImportModel($model)
    {
        if(!($model instanceof Testimonial_MageDoc_Model_Import_Abstract)
                && strlen($model)){
            $this->_importModel = Mage::getModel($model);
        }else{
            $this->_importModel = $model;
        }
        return $this;
    }
    
    protected function _afterLoad()
    {
        parent::_afterLoad();
        foreach ($this->_items as $item) {
            $this->processItemAfterLoad($item);
        }
        return $this;
    }

    public function processItemAfterLoad($item)
    {
        $categoryIds = $item->getCategoryId();
        if(strlen($categoryIds)) {
            $categoryIds = array_unique(explode(',', $categoryIds));
        }else {
            $categoryIds = array();
        }
        $item->setCategoryId($categoryIds);
        Mage::getSingleton('magedoc/tecdoc_article')
            ->processItemGraphicsAfterLoad($item);
        $data = $this->getImportModel()->getAdditionalData($item);
        $item->addData($data);
        
        return $this;
    }
       
    
    public function getImportModel()
    {
        if(!isset($this->_importModel)){
            $this->_importModel = Mage::getModel('magedoc/import_default');
        }

        return $this->_importModel;
    }
    
    public function setStoreId($storeId)
    {
        if ($storeId instanceof Mage_Core_Model_Store) {
            $storeId = $storeId->getId();
        }
        $this->_storeId = (int)$storeId;
        return $this;
    }

    public function getStoreId()
    {
        if (is_null($this->_storeId)) {
            $this->setStoreId(Mage::app()->getStore()->getId());
        }
        return $this->_storeId;
    }

    public function joinProducts($collection = null, $joinAlias = 'main_table', $columns = '', $joinCondition = null, $productTableAlias = 'catalog_product_entity', $joinPrices = true)
    {
        if(is_null($collection)){
            $collection = $this;
        }
        $select = $collection->getSelect();
        $resource = Mage::getModel('catalog/product')->getResource();
        
        $productTable = $resource->getEntityTable();

        $priceAttribute = $resource->getAttribute('price');
        $priceTable = $priceAttribute->getBackendTable();

        if (is_null($joinCondition)){
            $joinCondition = "{$productTableAlias}.td_art_id = {$joinAlias}.ART_ID";
        }

        $select->joinLeft(array($productTableAlias => $productTable), $joinCondition, '');
        $collection->addFilterToMap('is_imported', "{$productTableAlias}.entity_id IS NOT NULL");
        if ($joinPrices) {
            $select->
            joinLeft(
                array('catalog_product_price' => $priceTable),
                "catalog_product_price.entity_id = {$productTableAlias}.entity_id
                    AND catalog_product_price.attribute_id = {$priceAttribute->getId()}
                    AND catalog_product_price.store_id = {$this->getStoreId()}",
                $columns);
        }
        if (is_array($columns)){
            foreach ($columns as $column => $expression){
                $collection->addFilterToMap($column, $expression);
            }
        }
        $select->where('main_table.qty > 0');
        return $this;
    }
        

    public function joinRetailer()
    {
        $this->getSelect()
                ->joinInner(array('retailer' => $this->getTable('magedoc/retailer')),
                        'retailer.retailer_id = main_table.retailer_id', array(
                            'retailer_name' => 'retailer.name',
                            'retailer_rate' => 'retailer.rate'))
                ->where('retailer.enabled = 1');
        return $this;
    }
        
    
    public function addEnabledRetailerImportFilter()
    {
        $this->addFieldToFilter('retailer.is_import_enabled', 1);
        return $this;
    }
    
    public function addSupplierFilter($supplier)
    {
        $this->addFieldToFilter('supplier_id', $supplier);
        return $this;
    }
    
    public function addRetailerFilter($retailer)
    {
        $this->addFieldToFilter('main_table.retailer_id', $retailer);
        return $this;
    }

    public function addCategoryFilter($category)
    {
        $this->addFieldToFilter('catalog_category_entity.entity_id', $category);
        return $this;
    }

    public function addProductFilter($product)
    {
        if ($product instanceof Mage_Catalog_Model_Product) {
            if ($tdAtrId = $product->getTdArtId()) {
                $this->addFieldToFilter('main_table.td_art_id', $tdAtrId);
            } else {
                $this->addFieldToFilter('main_table.product_id', $product->getId());
            }
            return $this;
        }
        if ($product) {
            $this->addFieldToFilter('main_table.td_art_id', $product);
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

        $groupPart = $countSelect->getPart(Zend_Db_Select::GROUP);
        if (count($groupPart)){
            $countSelect->columns(new Zend_Db_Expr('1'));
            $select = $this->getConnection()->select()
                ->from($countSelect, new Zend_Db_Expr('COUNT(*)'));
        }else{
            $countSelect->columns('COUNT(*)');
            $select = $countSelect;
        }

        return $select;
    }

}
