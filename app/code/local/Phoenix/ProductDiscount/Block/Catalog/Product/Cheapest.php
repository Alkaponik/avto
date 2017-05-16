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
 * @package    Phoenix_Vfg
 * @copyright  Copyright (c) 2010 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 */
class Phoenix_ProductDiscount_Block_Catalog_Product_Cheapest extends Mage_Catalog_Block_Product_Abstract {
    protected $_productsCount = null;
    protected $_attributeCode = null;
    protected $_currentCategory = null;

    const DEFAULT_PRODUCTS_COUNT = 10;

    /**
     * Initialize block's cache
     */
    protected function _construct() {
        parent::_construct();
        $this->addData(array(
                'cache_lifetime'    => 86400,
                'cache_tags'        => array(Mage_Catalog_Model_Product::CACHE_TAG),
        ));
    }


    public function getCategoryPath() {
        $result = array();
        if ($_category = $this->getCurrentCategory()) {
            $pathInStore = $_category->getPathIds();
            $pathIds = $pathInStore;
            $categories = $_category->getParentCategories();

            // add category path breadcrumb
            foreach ($pathIds as $categoryId) {
                if (isset($categories[$categoryId]) && $categories[$categoryId]->getName()) {
                    $result[] = $categories[$categoryId]->getName();
                }
            }
        }
        return implode(', ', $result);
    }

    public function getCurrentProduct() {
        return Mage::registry('current_product');

    }

    public function getCurrentCategory() {
        if ($this->_currentCategory === null) {
            $this->_currentCategory = false;

            // get current category
            $cat = Mage::registry('current_category');

            // if no current category is registered get first category of the product
            if (!$cat) {
                if ($product = $this->getCurrentProduct()) {
                    $productCatIds = $product->getCategoryIds();

                    // remove root category from list
                    if ($key = array_search(Mage::app()->getStore()->getRootCategoryId(), $productCatIds)) {
                        unset($productCatIds[$key]);
                    }
                    if (!empty($productCatIds)) {
                        $cat = Mage::getModel('catalog/category')->load(array_shift($productCatIds));
                    }
                }
            }

            // get parent category
            if ($cat) {
                $parentCatId = $cat->getParentId();
                if (!empty($parentCatId) && Mage::app()->getStore()->getRootCategoryId() != $parentCatId) {
                    $this->_currentCategory = Mage::getModel('catalog/category')->load($parentCatId);
                } else {
                    $this->_currentCategory = $cat;
                }
            }
        }

        return $this->_currentCategory;
    }

    public function getCurrenCategoryKey() {
        if ($category = $this->getCurrentCategory()) {
            return $category->getId();
        } else {
            return Mage::app()->getStore()->getRootCategoryId();
        }
    }
    /**
     * Retrieve Key for caching block content
     *
     * @return string
     */

    public function getCacheKey() {
        return 'CATALOG_PRODUCT_CHEAP_' . Mage::app()->getStore()->getId()
                . '_' . Mage::getDesign()->getPackageName()
                . '_' . Mage::getDesign()->getTheme('template')
                . '_' . Mage::getSingleton('customer/session')->getCustomerGroupId()
                . '_' . md5($this->getTemplate().$this->getCurrenCategoryKey())
                . '_' . $this->getProductsCount();
    }

    /**
     * Prepare collection with new products and applied page limits.
     *
     * return Mage_Catalog_Block_Product_Cheapest
     */
    protected function _beforeToHtml() {

        $collection = Mage::getResourceModel('catalog/product_collection')
                ->addFinalPrice()
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->addStoreFilter()
                ->addWebsiteFilter()
                ->addAttributeToFilter($this->getAttributeCode(), array("=" => '1'))
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('cost')
                ->addExpressionAttributeToSelect('discount','({{cost}} - {{price}}) / {{cost}}', array('price', 'cost'))
                ->addAttributeToSort('discount', 'desc')
                ->setPage(1,$this->getProductsCount());

        if ($this->getCurrentCategory()) {
            $collection->addCategoryFilter($this->getCurrentCategory());
        }

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

        $this->setProductCollection($collection);
        return parent::_beforeToHtml();
    }

    /**
     * Set how much product should be displayed at once.
     *
     * @param $count
     * @return Mage_Catalog_Block_Product_New
     */
    public function setProductsCount($count) {
        $this->_productsCount = $count;
        return $this;
    }

    /**
     * Get how much products should be displayed at once.
     *
     * @return int
     */
    public function getProductsCount() {
        if (null === $this->_productsCount) {
            $this->_productsCount = self::DEFAULT_PRODUCTS_COUNT;
        }
        return $this->_productsCount;
    }
}
