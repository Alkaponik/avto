<?php
class Phoenix_GetCategoriesList_Block_Widget extends Mage_Adminhtml_Block_Catalog_Category_Widget_Chooser
{
	protected $_expandedCategories = array();
	
	public function __construct()
    {
        parent::__construct();
        $this->setTemplate('getcategorieslist/widget/categories.phtml');
    }
    
	protected function _getNodeJson($node, $level = 0)
    {
        $item = parent::_getNodeJson($node, $level);
        
        $item['text'] .= ' ('.$item['id'].')';
        if (in_array($item['id'], $this->getSelectedCategories())) {
            $item['checked'] = true;
        }
        $item['is_anchor'] = (int)$node->getIsAnchor();
        $item['url_key'] = $node->getData('url_key');
        
        if (in_array($item['id'], $this->_expandedCategories)) {
        	 $item['expanded'] = true;
        }
        
        return $item;
    }
    
    public function setSelectedCategories($selectedCategories) {
    	$this->_selectedCategories = $selectedCategories;
    	
    	foreach ($this->_selectedCategories as $cat_id) {
    		$parent_categories = Mage::getModel('catalog/category')->load($cat_id)->getParentIds();
    		$this->_expandedCategories = array_unique(array_merge($this->_expandedCategories, $parent_categories));
    	}
    	
        return $this;
    }
    
    
}