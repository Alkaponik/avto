<?php
class Phoenix_GetCategoriesList_Block_Widget_Widget extends Mage_Catalog_Block_Product_Abstract implements Mage_Widget_Block_Interface
{
    const MAX_SUBCATEGORIES = 6;
    const DEFAULT_TOP_CATEGORIES_LEVEL = 3;
    
    protected $_stack = array();

    public function getTopCategoriesLevel()
    {
        return self::DEFAULT_TOP_CATEGORIES_LEVEL;
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
		$category_ids = $this->getSelectedCategories();
		if (empty($category_ids)) {
			return array();
		}

		$category_ids = explode(',', $category_ids);
        $topLevel = $this->getTopCategoriesLevel();

		$categoryCollection  = Mage::getResourceModel('catalog/category_collection')
			->addNameToResult()
            ->addAttributeToSelect('graphical_headline')
            ->addAttributeToSelect('hp_position')
			->addIdFilter($category_ids)
			->addFieldToFilter('is_active', 1)
			->addOrderField('level')
            ->addOrderField('hp_position');

		$result = array();
		foreach ($categoryCollection as $cat) {
			if ($cat->getLevel()==$topLevel) {
				$result[$cat->getId()] = $cat;
				$result[$cat->getId()]->setData('childrenToShow', array());
			}
			else {
				$top_parent = explode('/', $cat->getPath());
				$top_parent = $top_parent[$topLevel];
				if (isset($result[$top_parent])) {
					$childrenToShow = $result[$top_parent]->getData('childrenToShow');
					array_push($childrenToShow, $cat);
					$result[$top_parent]->setData('childrenToShow', $childrenToShow);
				}
			}
		}
		
		return $result;
	}
	
    public function getFeaturedProduct($category_id)
    {

        if (!isset($this->_stack[$category_id]['featured_product'])) {
            $this->_stack[$category_id]['featured_product'] = false;
            $product_attribute = $this->getProductAttribute();
            if (empty($product_attribute)) {
                return array();
            }
            elseif (!$product_attribute_collection = Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter($product_attribute)->getData()) {
                return array();
            }

            $productCollection = Mage::getResourceModel('catalog/product_collection')
                    ->addAttributeToSort('updated_at', 'desc')
                    ->addAttributeToFilter($product_attribute, 1)
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
            Mage::getModel('review/review')->appendSummary($productCollection);

            foreach ($productCollection as $object) {
                $this->_stack[$category_id]['featured_product'] = $object;
                break;
            }
        }
        return $this->_stack[$category_id]['featured_product'];
    }

	public function getSelectedCategories()
    {
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


}