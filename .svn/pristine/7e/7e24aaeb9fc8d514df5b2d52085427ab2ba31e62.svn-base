<?php

class Phoenix_Brands_Block_Brand_List extends Mage_Core_Block_Template
{
    protected $_collection;
    protected $_columnsCount;
    protected $_brandsSidebarCount;
    protected $_featuredBrandsCount;

    public function getBrandsSidebarCount()
    {
        if (!$this->_brandsSidebarCount) {
            $this->_brandsSidebarCount = Mage::helper('phoenixbrands')->getConfig('brands_sidebar_num');
        }
        return $this->_brandsSidebarCount;
    }
    
    public function getFeaturedBrandsCount()
    {
        if (!$this->_featuredBrandsCount) {
            $this->_featuredBrandsCount = Mage::helper('phoenixbrands')->getConfig('brands_featured_num');
        }
        return $this->_featuredBrandsCount;
    }
    
    public function getColumnsCount()
    {
        if (!$this->_columnsCount) {
            $this->_columnsCount = Mage::helper('phoenixbrands')->getConfig('brands_columns');
        }
        return $this->_columnsCount > 0 ? $this->_columnsCount : 1;
    }

    public function getItemsInColumns()
    {
        $collection = $this->getCategoryCollection();

        $c = 0;
        $letters = array();
        foreach ($collection as $category) {
            $letter = ucfirst(substr($category->getName(),0,1));
            if (!isset($letters[$letter]['items'])){
                $letters[$letter]['items'] = array();
            }
            $letters[$letter]['items'][] = $category;
            if (isset($letters[$letter]['count'])){
                $letters[$letter]['count']++;
            }
            else{
                $letters[$letter]['count'] = 1;
            }
            $c++;
        }
        
        $itemsPerColumn = ceil(($c + count($letters)) / $this->getColumnsCount());

        $col = 0;
        $c = 0;
        $columns = array();
        foreach ($letters as $letter => $items) {
            if (!isset($columns[$col])) {
                $columns[$col] = array();
            }
            $columns[$col][$letter]=$items['items'];
            $c += $items['count'];
            $c++;
            if ($c >= $itemsPerColumn) {
                $c=0;
                $col++;
            }
        }
        return $columns;
    }

    public function getCategoryCollection()
    {
        if (!isset($this->_collection)) {
            $this->_collection = Mage::helper('phoenixbrands')->getBrandCategoryCollection();
        }
        return $this->_collection;
    }

    public function getBrandsRootCategoryId()
    {        
        return Mage::helper('phoenixbrands')->getBrandsRootCategoryId();
    }
    
    public function getBrandCategoryUrl($category)
    {
        return $category->getBrandCategoryUrl();
    }

    public function getFeaturedBrands($count = 0) 
    {
        $collection = Mage::getSingleton('catalog/category')->getCategories(Mage::helper('phoenixbrands')->getBrandsRootCategoryId(), 1, false, true, false)
            ->addAttributeToSort('name')
            ->addAttributeToSelect('logo_image')
            ->addAttributeToFilter('is_active', '1')
            ->addAttributeToFilter('is_featured', '1')
            ->addAttributeToFilter('block_type', Phoenix_Brands_Model_Catalog_Category::BLOCK_TYPE_BRAND);
        
        if ($count > 0) {
            $collection->setPage(1, $count);
        }
                            
        return $collection;
    }
}
