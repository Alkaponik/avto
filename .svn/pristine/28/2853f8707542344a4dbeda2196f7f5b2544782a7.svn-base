<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Phoenix
 * @package    Phoenix_Brands
 * @copyright  Copyright (c) 2012 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 */

class Phoenix_Brands_Model_Observer 
{
    protected $_categories = array();

    public function catalog_product_save_before(Varien_Event_Observer $observer) 
    {
        $product = $observer->getProduct();

        $categoryIds = $product->getCategoryIds();
        $oldCategoryIds = $product->getResource()->getCategoryIds($product);

        $insertedCategoryIds = array_diff($categoryIds, $oldCategoryIds);
        $deletedCategoryIds = array_diff($oldCategoryIds, $categoryIds);
        $untouchedCategoryIds = array_intersect($oldCategoryIds, $categoryIds);

        $brandCategory = $this->_getFirstBrandCategory($insertedCategoryIds);
        $attributeCode = Mage::helper('phoenixbrands')->getConfig('brands_attribute');

        if (!$brandCategory) {
            /* Check if product doesn't already belong to a brand category */
            if (!$brandCategory = $this->_getFirstBrandCategory($untouchedCategoryIds)) {
                /* If product was removed from brand category */
                $brandCategory = $this->_getFirstBrandCategory($deletedCategoryIds);
                if($brandCategory) {
                    $product->setData($attributeCode, null);
                    $product->setData('brand_category_id', null);
                    $product->setData('collection_category_id', null);
                }
                /* If product is in a brand category and BrandAttribute value is empty */
            }else {
                if (!$product->getData($attributeCode)) {
                    $brandName = $brandCategory->getName();
                    $attribute = $product->getResource()->getAttribute($attributeCode);
                    $value = $attribute->getSource()->getOptionId($brandName);
                    $product->setData($attributeCode, $value);
                }
                if (!$product->hasData('brand_category_id')) {
                    $product->setData('brand_category_id', $brandCategory->getId());
                }
                if (!$product->hasData('collection_category_id') && $collectionCategory = $this->_getFirstBrandCollectionCategory($untouchedCategoryIds,$brandCategory->getId())) {
                    $product->setData('collection_category_id', $collectionCategory->getId());
                }elseif ($collectionCategory = $this->_getFirstBrandCollectionCategory($insertedCategoryIds,$brandCategory->getId())) {
                    $product->setData('collection_category_id', $collectionCategory->getId());
                }elseif ($collectionCategory = $this->_getFirstBrandCollectionCategory($deletedCategoryIds,$brandCategory->getId())) {
                    $product->setData('collection_category_id', null);
                }elseif (!$this->_getFirstBrandCollectionCategory($untouchedCategoryIds,$brandCategory->getId())) {
                    $product->setData('collection_category_id', null);
                }
            }
            /**
             * If product was just added to brand category
             * set manufaturer & brand_category_id & collection_category_id values
             */
        }else {
            $brandName = $brandCategory->getName();
            $attribute = $product->getResource()->getAttribute($attributeCode);
            $value = $attribute->getSource()->getOptionId($brandName);
            $product->setData($attributeCode, $value);
            $product->setData('brand_category_id', $brandCategory->getId());
            if ($collectionCategory = $this->_getFirstBrandCollectionCategory($insertedCategoryIds,$brandCategory->getId())) {
                $product->setData('collection_category_id', $collectionCategory->getId());
            }
        }
    }
    
    public function catalog_product_save_after(Varien_Event_Observer $observer)
    {
        $product = $observer->getProduct();
        $brandCategory = $this->getBrandCategoryByProductCategories($product);
        $attributeCode = Mage::helper('phoenixbrands')->getConfig('brands_attribute');
        
        if ($brandCategory) {
            $storeId = Mage::helper('phoenixbrands')->getStoreIdByCategory($brandCategory);
            $brandsRootCategoryId = Mage::helper('phoenixbrands')->getBrandsRootCategoryId($storeId);
        }
        else {
            $storeId = current($product->getStoreIds());
            $brandsRootCategoryId = Mage::helper('phoenixbrands')->getBrandsRootCategoryId($storeId);
        }
        
        if ($brandsRootCategoryId && $product->getData($attributeCode) && $product->getData($attributeCode) != $product->getOrigData($attributeCode)) {
            // create new category if needed
            Mage::getSingleton('phoenixbrands/synchronizer')->synchronizeForProduct($product, $attributeCode, $brandsRootCategoryId);
            //Mage::getSingleton('phoenixbrands/synchronizer')->createCategoriesFromAttributeOptions($brandsRootCategoryId, $attributeCode, $storeId);
        }
        
        return $this;
    }
    
    public function getBrandCategoryByProductCategories(Mage_Catalog_Model_Product $product)
    {
        $categoryIds = $product->getCategoryIds();
        $oldCategoryIds = (array)$product->getOrigData('category_ids');

        $insertedCategoryIds = array_diff($categoryIds, $oldCategoryIds);
        $deletedCategoryIds = array_diff($oldCategoryIds, $categoryIds);
        $untouchedCategoryIds = array_intersect($oldCategoryIds, $categoryIds);

        if ($brandCategory = $this->_getFirstBrandCategory($insertedCategoryIds)) {
            return $brandCategory;
        }
        
        if ($brandCategory = $this->_getFirstBrandCategory($deletedCategoryIds)) {
            return $brandCategory;
        }
        
        if ($brandCategory = $this->_getFirstBrandCategory($untouchedCategoryIds)) {
            return $brandCategory;
        }
        
        return false;
    }
    
    public function catalog_category_change_products(Varien_Event_Observer $observer) 
    {
        $category = $observer->getCategory();
        
        if ($category->getIsFromMassSynchronization()) {
            return $this;
        }
        
        $productIds = $observer->getProductIds();
        $products = $category->getPostedProducts();

        /**
         * Example re-save category
         */
        if (is_null($products)) {
            return $this;
        }

        /**
         * old category-product relationships
         */
        $oldProducts = $category->getProductsPosition();

        $insert = array_diff_key($products, $oldProducts);
        $delete = array_diff_key($oldProducts, $products);

        $storeId = Mage::helper('phoenixbrands')->getStoreIdByCategory($category);
        $brandCategory = $this->_getFirstBrandCategory($category);
        if (!$brandCategory) {
            return $this;
        }
        $categoryId = $category->getId();
        $brandCategoryId = $brandCategory->getId();
        $collectionCategoryId = $category->getBlockType() == Phoenix_Brands_Model_Catalog_Category::BLOCK_TYPE_COLLECTION ?
                $category->getId() : null;
        $brandName = $brandCategory->getName();
        $resource = $category->getResource();
        $product = Mage::getModel('catalog/product');
        $product->setStoreId($storeId);
        $attributeCode = Mage::helper('phoenixbrands')->getConfig('brands_attribute');
        $attribute = $product->getResource()->getAttribute($attributeCode);

        foreach($insert as $productId => $v) {
            $product->unsetData();
            $product->setOrigData();
            $product->setStoreId($storeId);
            $product->setId($productId);

            $value = $attribute->getSource()->getOptionId($brandName);
            if (is_null($value)) {
                $attribute->setOption(
                        array(
                        'value' => array(
                                "store_$storeId" => array(
                                        0 => $brandName,
                                        $storeId => $brandName
                                )
                        )
                        )
                );
                try {
                    $attribute->save();
                } catch (Exception $e) {
                    Mage::log('Failed Saving Attribute');
                    Mage::log($e->getMessage());
                    return null;
                }
                $value = $attribute->getSource()->getOptionId($brandName);
            }

            $product->setData($attributeCode, $value);
            $product->getResource()->saveAttribute($product, $attributeCode);
            $product->setData('brand_category_id', $brandCategoryId);
            $product->getResource()->saveAttribute($product, 'brand_category_id');
            if ($collectionCategoryId) {
                $product->setData('collection_category_id', $collectionCategoryId);
                $product->getResource()->saveAttribute($product, 'collection_category_id');
            }
        }

        foreach($delete as $productId => $v) {
            $product->unsetData();
            $product->setOrigData();
            $product->setStoreId($storeId);
            $product->setId($productId);
            $product->setData($attributeCode, null);
            $product->getResource()->saveAttribute($product, $attributeCode);
            $product->setData('brand_category_id', null);
            $product->getResource()->saveAttribute($product, 'brand_category_id');

            if ($collectionCategoryId) {
                $product->setData('collection_category_id', null);
                $product->getResource()->saveAttribute($product, 'collection_category_id');
            }
        }
    }

    protected function _getFirstBrandCategory($categories) 
    {
        if (!is_array($categories)) {
            $categories = array($categories);
        }
        foreach ($categories as $category) {
            if (!$category instanceof Mage_Catalog_Model_Category) {
                $category = $this->getCategoryById($category);
            }
            $storeId = Mage::helper('phoenixbrands')->getStoreIdByCategory($category);
            $brandsRootCategoryId = Mage::helper('phoenixbrands')->getBrandsRootCategoryId($storeId);
            if (!$brandsRootCategoryId) {
                continue;
            }
            $categoryIds = explode('/', $category->getPath());
            $brandCategoryId = false;
            foreach ($categoryIds as $key => $value) {
                if ($value == $brandsRootCategoryId) {
                    $brandCategoryId = current($categoryIds);
                    break;
                }
            }
            if ($brandCategoryId === false) {
                continue;
            }
            $categoryId = $category->getId();
            if ($brandCategoryId == $categoryId) {
                $brandCategory = $category;
            }else {
                $brandCategory = $this->getCategoryById($brandCategoryId);
            }
            return $brandCategory;
        }
        return null;
    }

    public function _getFirstBrandCollectionCategory($categories, $brandCategoryId) 
    {
        if (!is_array($categories)) {
            $categories = array($categories);
        }
        foreach ($categories as $category) {
            if (!$category instanceof Mage_Catalog_Model_Category) {
                $category = $this->getCategoryById($category);
            }
            $categoryIds = explode('/', $category->getPath());
            if ($category->getBlockType() == Phoenix_Brands_Model_Catalog_Category::BLOCK_TYPE_COLLECTION 
                        && in_array($brandCategoryId, $categoryIds)) {
                return $category;
            }
        }
        return null;
    }

    public function getCategoryById($categoryId) {
        if (!isset($this->_categories[$categoryId])) {
            $category = Mage::getModel('phoenixbrands/catalog_category')->load($categoryId);
            $this->_categories[$categoryId] = $category;
        }
        return $this->_categories[$categoryId];
    }

    public function initProductCollectionCategory(Varien_Event_Observer $observer) 
    {
        $product = $observer->getProduct();
        if ($brandCategoryId = $product->getBrandCategoryId()) {
            $productBrandCategory = Mage::getModel('catalog/category')->load($brandCategoryId);
            Mage::register('current_brand_category', $productBrandCategory);
        }
        if ($collectionCategoryId = $product->getCollectionCategoryId()) {
            $productCollectionCategory = Mage::getModel('catalog/category')->load($collectionCategoryId);
            Mage::register('current_collection_category', $productCollectionCategory);
        }
    }

    public function assignHandlers(Varien_Event_Observer $observer) 
    {
        $catalogHelper = $observer->getEvent()->getHelper();
        $helper = Mage::helper('phoenixbrands');
        $catalogHelper->addHandler('productAttribute', $helper)
                ->addHandler('categoryAttribute', $helper);
        return $this;
    }

    public function adminhtml_catalog_product_form_prepare_excluded_field_list(Varien_Event_Observer $observer) 
    {
        $tabAttributes = $observer->getObject();
        $tabAttributes->setFormExcludedFieldList(
            array_merge(
                $tabAttributes->getFormExcludedFieldList(),
                array(
                    'collection_category_id',
                    'brand_category_id'
                )
            )
        );
    }

    public function catalog_category_delete_after(Varien_Event_Observer $observer)
    {
        $category = $observer->getCategory();
        if ($category->getBlockType() == Phoenix_Brands_Model_Catalog_Category::BLOCK_TYPE_BRAND ||
                        $category->getBlockType() == Phoenix_Brands_Model_Catalog_Category::BLOCK_TYPE_COLLECTION ) {
            $attributeCode = $category->getBlockType() == Phoenix_Brands_Model_Catalog_Category::BLOCK_TYPE_BRAND
                                                                        ? 'brand_category_id' 
                                                                        : 'collection_category_id';
            $collection = Mage::getResourceModel('catalog/product_collection');
            $collection->addAttributeToFilter($attributeCode, $category->getId());

            foreach ($collection as $product) {
                $product->setData($attributeCode, null);
                if ($attributeCode == 'brand_category_id') {
                    $product->setData(Mage::helper('phoenixbrands')->getConfig('brands_attribute'), null);
                    $product->getResource()->saveAttribute($product, Mage::helper('phoenixbrands')->getConfig('brands_attribute'));
                    $product->setData('collection_category_id', null);
                    $product->getResource()->saveAttribute($product, 'collection_category_id');
                }
                $product->getResource()->saveAttribute($product, $attributeCode);
            }
        }
    }
    
    public function addCategoryLayoutUpdate(Varien_Event_Observer $observer)
    {
        $action = $observer->getEvent()->getAction();
        if ($action->getFullActionName() != 'catalog_category_view') {
            return $this;
        }
        
        $category = Mage::registry('current_category');
        
        $storeId = Mage::helper('phoenixbrands')->getStoreIdByCategory($category);
        $brandsRootCategoryId = Mage::helper('phoenixbrands')->getBrandsRootCategoryId($storeId);
        
        if ($category->getBlockType() == Phoenix_Brands_Model_Catalog_Category::BLOCK_TYPE_BRAND 
                                    && $brandsRootCategoryId != $category->getId()) {
            $action->getLayout()->getUpdate()
                ->addHandle('phoenixbrands_brands_brand_category');
        }
        elseif ($brandsRootCategoryId && $brandsRootCategoryId == $category->getId()) {
            $action->getLayout()->getUpdate()
                ->addHandle('phoenixbrands_brands_list');
        }
            
        return $this;
    }
    
    /*
     * Change layout update based on module settings
     */
    public function changeLayoutUpdateBrandCategory(Varien_Event_Observer $observer)
    {
        $action = $observer->getEvent()->getAction();
        if ($action->getFullActionName() != 'catalog_category_view') {
            return $this;
        }
        
        $category = Mage::registry('current_category');
        $storeId = Mage::helper('phoenixbrands')->getStoreIdByCategory($category);
        $brandsRootCategoryId = Mage::helper('phoenixbrands')->getBrandsRootCategoryId($storeId);
        
        if ($category->getBlockType() == Phoenix_Brands_Model_Catalog_Category::BLOCK_TYPE_BRAND 
                                    && $brandsRootCategoryId != $category->getId()) {
            // Change placement of current brand logo
            $sidebarPosition = Mage::helper('phoenixbrands')->getConfig('sidebar_logo_position');
            if (strlen($sidebarPosition) > 0) {
                $action->getLayout()->getUpdate()
                    ->addUpdate('<remove name="phoenixbrands.brand.sidebar"/>');
                if ($sidebarPosition != 'remove') {
                    $sidebarPosition = explode('_', $sidebarPosition);
                    $action->getLayout()->getUpdate()
                        ->addUpdate('
                            <reference name="' . $sidebarPosition[0] . '">
                            	<block type="core/template" name="phoenixbrands.brand.sidebar.manualPlacement" 
                            			template="phoenixbrands/brand_category_sidebar.phtml" ' . $sidebarPosition[1] . '="-"/>
                            </reference>
                        ');
                }
            }
        }
        
        return $this;
    }
    
    /*
     * Change layout update based on module settings
     */
    public function changeLayoutUpdate(Varien_Event_Observer $observer)
    {
        $action = $observer->getEvent()->getAction();
        
        $storeId = Mage::app()->getStore()->getId();
        $brandsRootCategoryId = Mage::helper('phoenixbrands')->getBrandsRootCategoryId($storeId);        
        if ($brandsRootCategoryId) {
            // Change placement of featured brands sidebar list
            $sidebarPosition = Mage::helper('phoenixbrands')->getConfig('sidebar_list_position');
            if (strlen($sidebarPosition) > 0) {
                $action->getLayout()->getUpdate()
                    ->addUpdate('<remove name="phoenixbrands.list.sidebar"/>');
                if ($sidebarPosition != 'remove') {
                    $sidebarPosition = explode('_', $sidebarPosition);
                    $action->getLayout()->getUpdate()
                        ->addUpdate('
                            <reference name="' . $sidebarPosition[0] . '">
                            	<block type="phoenixbrands/brand_list" name="phoenixbrands.list.sidebar.manualPlacement" 
                            			template="phoenixbrands/manufacturers_sidebar.phtml" ' . $sidebarPosition[1] . '="-"/>
                            </reference>
                        ');
                }
            }
        }
        
        return $this;
    }
}