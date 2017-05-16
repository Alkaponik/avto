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

class Phoenix_Brands_Model_Synchronizer
{
    const INDEX_PROCESS_CATALOG_URL = 3;
    const INDEX_PROCESS_CATEGORY_PRODUCTS = 6;
    
    /*
     * @return	array	$createdCategories	ids of created categories
     */
    public function createCategoriesFromAttributeOptions($parentCategoryId, $attributeCode, $storeId = null)
    {
        $changedCategories = array();
        
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attributeCode);
        if (!$attribute || !$attribute->getId() || !$parentCategoryId) {
            return $changedCategories;
        }
        
        // get currently existing brand categories
        $existingCategories = array();
        $existingCategoriesTmp = Mage::helper('phoenixbrands')->getBrandCategoryCollection($parentCategoryId);
        $existingCategoriesTmp = $existingCategoriesTmp->getConnection()->query($existingCategoriesTmp->getSelect());
        while ($categoryData = $existingCategoriesTmp->fetch(Zend_Db::FETCH_ASSOC)) {
            $existingCategories[$this->normalizeBrand($categoryData['name'])] = $categoryData['entity_id'];
        }
        unset($existingCategoriesTmp);
        
        // get products with brands attribute
        $brandedProductsTmp = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect($attributeCode)
            ->addAttributeToFilter($attributeCode, array('notnull' => 1))
            ->setFlag('disable_root_category_filter', true)
            ->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds())
            ->groupByAttribute('entity_id')
            ->addOrder($attributeCode);
        if (!is_null($storeId)){
            $brandedProductsTmp->addStoreFilter($storeId);
        }

        $brandedProductsTmp = $brandedProductsTmp->getConnection()->query($brandedProductsTmp->getSelect());
        $brandedProducts = array();
        while ($brandedProduct = $brandedProductsTmp->fetch(Zend_Db::FETCH_ASSOC)) {
            $brandNameNormalized = $this->normalizeBrand($brandedProduct[$attributeCode]);
            if (!isset($brandedProducts[$brandNameNormalized])) {
                $brandedProducts[$brandNameNormalized] = array();
            }
            // emulate posted_products array during category saving from admin backend
            $brandedProducts[$brandNameNormalized][$brandedProduct['entity_id']] = 0;
        }
        unset($brandedProductsTmp);
        
        $parentCategory = Mage::getModel('catalog/category')->load($parentCategoryId);
        if (!$parentCategory->getId()) {
            return $changedCategories;
        }
        
        Mage::getSingleton('core/resource')->getConnection('write')
            ->raw_query('SET wait_timeout = 1800');
        foreach ($attribute->getSource()->getAllOptions(true, true) as $option) {
            // don't create categories for empty values
            if (empty($option['label'])) {
                continue;
            }
            
            $category = Mage::getModel('catalog/category')->setIsFromMassSynchronization(true);
            
            // category exists
            $normalizedLabel = $this->normalizeBrand($option['label']);
            if (isset($existingCategories[$normalizedLabel])) {
                $category->load($existingCategories[$normalizedLabel]);
                if ($category->getId()) {
                    $categoryExistingProducts = $category->getProductCollection();
                    if ($storeId) {
                        $categoryExistingProducts->setStoreId($storeId);
                        $categoryExistingProducts = $categoryExistingProducts->getAllIds();
                        if (isset($brandedProducts[$option['value']])) {
                            $productsAdded = false;
                            foreach ($brandedProducts[$option['value']] as $productId => $value) {
                                if (!in_array($productId, $categoryExistingProducts)) {
                                    $productsAdded = true;
                                }
                            }
                            
                            if ($productsAdded) {
                                $category->setPostedProducts($brandedProducts[$option['value']])
                                    ->save();
                                $changedCategories[] = $category->getId();
                            }
                        }
                    }
                }
                continue;
            }
            

            if (isset($brandedProducts[$option['value']])) {
                $category->setName($option['label'])
                    ->setIsActive(1)
                    ->setBrand($option['value'])
                    ->setPath($parentCategory->getPath())
                    ->setBlockType(Phoenix_Brands_Model_Catalog_Category::BLOCK_TYPE_BRAND)
                    ->setIncludeInMenu(0);

                $category->setPostedProducts($brandedProducts[$option['value']]);

                $category->save();

                if ($category->getId()) {
                    $existingCategories[$normalizedLabel] = $category->getId();
                    $changedCategories[] = $category->getId();
                }
            }
        }
        
        if (count($changedCategories) > 0) {
            $indexers = array('catalog_category_product', 'catalog_url');
            foreach ($indexers as $indexer) {
                $indexProcess = Mage::getSingleton('index/indexer')->getProcessByCode($indexer);
                //$indexProcess->reindexEverything();
                if ($indexProcess) {
                    $indexProcess->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
                }
            }
        }
        
        return $changedCategories;
    }
    
    public function synchronizeForProduct(Mage_Catalog_Model_Product $product, $attributeCode, $brandsRootCategoryId)
    {
        $manufacturerId = $product->getData($attributeCode);
        if (!$manufacturerId) {
            return false;
        }
        
        if ($manufacturerId == $product->getOrigData($attributeCode)) {
            return false;
        }
        
        $attr = $product->getResource()->getAttribute($attributeCode);
        $manufacturerText = $attr->getSource()->getOptionText($manufacturerId);
        
        $productBrandCategory = null;
        $productCategories = $product->getCategoryIds();
        $brandCategoryIds = array();
        
        $existingCategoriesCollection = Mage::helper('phoenixbrands')->getBrandCategoryCollection($brandsRootCategoryId);
        $existingCategoriesCollection = $existingCategoriesCollection->getConnection()->query($existingCategoriesCollection->getSelect());
        while ($categoryData = $existingCategoriesCollection->fetch(Zend_Db::FETCH_ASSOC)) {
            if (mb_strtolower($categoryData['name']) == mb_strtolower($manufacturerText)) {
                $productBrandCategory = $categoryData['entity_id'];
            }
            else {
                $brandCategoryIds[] = $categoryData['entity_id'];
            }
        }
        
        $productCategories = array_diff($productCategories, $brandCategoryIds);
        $productCategories[] = $productBrandCategory;
        
        $product->setCategoryIds($productCategories)
            ->setData('brand_category_id', $productBrandCategory)
            ->setOrigData($attributeCode, $manufacturerId)
            ->save();
            
        return true;
    }

    public function normalizeBrand($brand)
    {
        return mb_strtolower(preg_replace('/[\s-\/]+/', '', html_entity_decode($brand)), 'UTF-8');
    }
}