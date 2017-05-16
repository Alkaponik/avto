<?php

class Testimonial_MageDoc_Block_Type_Categories extends Mage_Catalog_Block_Product_Abstract
{

    const MAX_SUBCATEGORIES = 6;
    const DEFAULT_TOP_CATEGORIES_LEVEL = 3;

    protected $_stack = array();

    protected function _construct()
    {
        parent::_construct();

        $this->addData(array(
            'cache_lifetime'    => null,
            'cache_tags'        => array(Mage_Catalog_Model_Product::CACHE_TAG, Mage_Catalog_Model_Category::CACHE_TAG),
        ));
    }

    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array(
            'MAGEDOC_TYPE_CATEGORIES',
            Mage::app()->getStore()->getId(),
            Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template'),
            Mage::getSingleton('customer/session')->getCustomerGroupId(),
            'template' => $this->getTemplate(),
            $this->getType()->getId()
        );
    }

    public function getTopCategoriesLevel()
    {
        $level = Mage::helper('magedoc')->getTypeTopCategoryLevel();
        return $level
            ? $level
            : self::DEFAULT_TOP_CATEGORIES_LEVEL;
    }

    public function getMaxSubcategoreies()
    {
        return self::MAX_SUBCATEGORIES;
    }

    public function getFeaturedProductCategoryIds($category_id, $featuredProduct)
    {
        if (!isset($this->_stack[$category_id]['category_ids'])){
            $this->_stack[$category_id]['category_ids'] = $featuredProduct->getCategoryIds();
        }
        return $this->_stack[$category_id]['category_ids'];
    }

    public function isParentOfFeaturedProduct($category_id, $featuredProduct)
    {
        if ($featuredProduct instanceof Mage_Catalog_Model_Product){
            return in_array($category_id, $this->getFeaturedProductCategoryIds($category_id, $featuredProduct)) ? true : false;
        }
        return false;
    }


    /* Example of usage:
    $result = $this->getCategoriesCollection();
	foreach ($result as $cat_crt) {
		echo '<h1>'.$cat_crt->getName().'</h1>';
		$childrenToShow = $cat_crt->getData('childrenToShow');
		if (!empty($childrenToShow)) {
			foreach ($childrenToShow as $child) {
				echo '<h2>'.$child->getName().'</h2>';
			}
		}
	}
	*/
    public function getCategoriesCollection()
    {
        $categoryIds = $this->getSelectedCategories();
        if (empty($categoryIds)) {
            return array();
        }

        $topLevel = $this->getTopCategoriesLevel();

        $categoryCollection  = Mage::getResourceModel('catalog/category_collection')
            ->addNameToResult()
            ->addAttributeToSelect('graphical_headline')
            ->addAttributeToSelect('hp_position')
            ->addIdFilter($categoryIds)
            ->addFieldToFilter('is_active', 1)
            //->addOrderField('level')
            ->addOrderField('sort_order');

        $result = array();
        foreach ($categoryCollection as $cat) {
            if ($cat->getLevel()==$topLevel) {
                $result[$cat->getId()] = $cat;
                $result[$cat->getId()]->setData('childrenToShow', array());
            }
            else {
                $top_parent = explode('/', $cat->getPath());
                if (isset($top_parent[$topLevel])) {
                    $top_parent = $top_parent[$topLevel];
                    if (isset($result[$top_parent])) {
                        $childrenToShow = $result[$top_parent]->getData('childrenToShow');
                        array_push($childrenToShow, $cat);
                        $result[$top_parent]->setData('childrenToShow', $childrenToShow);
                    }
                }
            }
        }

        return $result;
    }

    public function getFeaturedProduct($category_id)
    {

        if (!isset($this->_stack[$category_id]['featured_product'])) {
            $this->_stack[$category_id]['featured_product'] = false;
            /*$product_attribute = $this->getProductAttribute();
            if (empty($product_attribute)) {
                return array();
            }
            elseif (!$product_attribute_collection = Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter($product_attribute)->getData()) {
                return array();
            }*/

            $productCollection = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToSort('updated_at', 'desc')
                //->addAttributeToFilter($product_attribute, 1)
                ->addAttributeToSelect(array(
                'thumbnail',
                'small_image',
                'image',
                'name',
                'cost',
                'manufacturer'))
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addCategoryFilter(Mage::getModel('catalog/category')->load($category_id))
                ->setPage(1,1);
            $productCollection->load();
            //Mage::getModel('review/review')->appendSummary($productCollection);

            foreach ($productCollection as $object) {
                $this->_stack[$category_id]['featured_product'] = $object;
                break;
            }
        }
        return $this->_stack[$category_id]['featured_product'];
    }

    public function getSelectedCategories()
    {
        if ($this->hasData('category_ids')){
            return $this->getData('category_ids');
        }
        $categories = Mage::getResourceModel('catalog/category_collection');
        $categories->addIdFilter(Mage::helper('magedoc')->getTypeTopCategories());
        $pathConditions = array();
        foreach ($categories as $category){
            $pathConditions[] = "c.path LIKE '{$category->getPath()}/%'";
        }

        /** @var $adapter Varien_Db_Adapter_Pdo_Mysql */
        $adapter = $categories->getConnection();
        $select = $adapter->select()
            ->from(array('c' => $categories->getTable('catalog/category')), 'entity_id')
            ->join(
                array('cpi' => $categories->getTable('catalog/category_product_index')),
                'c.entity_id = cpi.category_id',
                '')
            ->join(
                array('mtp' => $categories->getTable('magedoc/type_product')),
                $adapter->quoteInto('mtp.product_id = cpi.product_id AND mtp.type_id = ?', $this->getVehicleType()->getId()),
                '')
            ->where('c.level <= ?', $this->getTopCategoriesLevel() + 1)
            ->group('c.entity_id');
        if ($pathConditions){
            $select->where(implode(' OR ', $pathConditions));
        }

        $this->setData('category_ids', $adapter->fetchAll($select));
        return $this->getData('category_ids');
        return $this->getData('catsselected');
    }

    public function getProductAttribute()
    {
        return $this->getData('productattr');
    }

    public function getProductImgWidth()
    {
        return $this->getData('productimgwidth');
    }

    public function getProductImgHeight()
    {
        return $this->getData('productimgheight');
    }

    public function getVehicleType()
    {
        return $this->getParentBlock()->getVehicleType();
    }

    public function getCategoryUrl($category)
    {
        $requestVar = Mage::getSingleton('magedoc/catalog_layer_filter_type')->getRequestVar();
        return $category->getUrl()."?{$requestVar}={$this->getVehicleType()->getId()}";
    }
}