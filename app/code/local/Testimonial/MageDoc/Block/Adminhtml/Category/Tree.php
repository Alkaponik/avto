<?php

class Testimonial_MageDoc_Block_Adminhtml_Category_Tree extends Mage_Adminhtml_Block_Catalog_Category_Tree
{
    protected $_collection;
    protected $_withProductCount;

    public function __construct()
    {
        parent::__construct();
        $this->setUseAjax(true);
        $this->_withProductCount = false;
    }

    public function getCategoryCollection()
    {
        $storeId = $this->getRequest()->getParam('store', $this->_getDefaultStoreId());
        $collection = $this->getData('category_collection');
        if (is_null($collection)) {
            $collection = Mage::getResourceModel('magedoc/tecdoc_searchTree_collection');
            $collection->joinPath()
                    ->setStoreId($storeId)
                    ->joinCategory()
                    ->joinDesignation($collection, 'main_table', 'STR_DES_ID', array('name' => 'CONCAT(IF(md_searchTree.is_import_enabled, \'i\', \'\'),IF(md_searchTree.is_enabled, \'e\', \'\'),{{des_text}}.TEX_TEXT)'));
            $this->setData('category_collection', $collection);
        }
        $this->_collection = $collection;
        
        return $collection;
    }


    public function getNodesUrl()
    {
        return $this->getUrl('*/adminhtml_category/jsonTree');
    }

    public function getSwitchTreeUrl()
    {
        return $this->getUrl("*/adminhtml_category/tree", array('_current'=>true, 'store'=>null, '_query'=>false, 'id'=>null, 'parent'=>null));
    }

    public function getMoveUrl()
    {
        return $this->getUrl('*/adminhtml_category/move', array('store'=>$this->getRequest()->getParam('store')));
    }

    
    public function getBreadcrumbsJavascript($path, $javascriptVarName)
    {
        if (empty($path)) {
            return '';
        }
        
        $categories = Mage::getResourceSingleton('magedoc/tecdoc_searchTree_tree')
            ->setStoreId($this->getStore()->getId())->loadBreadcrumbsArray($path);
        if (empty($categories)) {
            return '';    
        }
        
        foreach ($categories as $key => $category) {
            $categories[$key] = $this->_getNodeJson($category);
        }
        
        return
            '<script type="text/javascript">'
            . $javascriptVarName . ' = ' . Mage::helper('core')->jsonEncode($categories) . ';'
            . ($this->canAddSubCategory() ? '$("add_subcategory_button").show();' : '$("add_subcategory_button").hide();')
            . '</script>';
        
    }

    /**
     * Get JSON of a tree node or an associative array
     *
     * @param Varien_Data_Tree_Node|array $node
     * @param int $level
     * @return string
     */
    protected function _getNodeJson($node, $level = 2)
    {
        // create a node from data array
        
        if (is_array($node)) {
            $node = new Varien_Data_Tree_Node($node, 'STR_ID', new Varien_Data_Tree);
        }
        $item = array();
        $item['text'] = $this->buildNodeName($node);

        //$rootForStores = Mage::getModel('core/store')->getCollection()->loadByCategoryIds(array($node->getEntityId()));
        $rootForStores = in_array($node->getId(), $this->getRootIds());        
        $item['id']  = $node->getId();
        $item['store']  = (int) $this->getStore()->getId();
        $item['path'] = $node->getData('path');
        $item['entity_id'] = $node->getEntityId();
        $item['cls'] = 'folder ' . ($node->getIsActive() ? 'active-category' : 'no-active-category');
        //$item['allowDrop'] = ($level<3) ? true : false;
        $allowMove = $this->_isCategoryMoveable($node);
        $item['allowDrop'] = $allowMove;
        // disallow drag if it's first level and category is root of a store
        $item['allowDrag'] = $allowMove && (($node->getLevel()==1 && $rootForStores) ? false : true);
        if ((int)$node->getChildrenCount()>0) {
            $item['children'] = array();
        }
        $isParent = $this->_isParentSelectedCategory($node);
        if ($node->hasChildren()) {
            $item['children'] = array();
            if (!($this->getUseAjax() && $node->getLevel() > 1 && !$isParent)) {
                foreach ($node->getChildren() as $child) {
                    $item['children'][] = $this->_getNodeJson($child, $level+1);
                }
            }
        }

        if ($isParent || $node->getLevel() < 2) {
            $item['expanded'] = true;
        }

        return $item;
    }

    /**
     * Get category name
     *
     * @param Varien_Object $node
     * @return string
     */
    public function buildNodeName($node)
    {       
        $result = $node->getName();//$node->getData('STR_DES_TEXT');
        if ($this->_withProductCount) {
             $result .= ' (' . $node->getProductCount() . ')';
        }
        return $result;
    }

    public function getNode($parentNodeCategory, $recursionLevel=2)
    {
        $tree = Mage::getResourceModel('magedoc/tecdoc_searchTree_tree');

        $nodeId     = $parentNodeCategory->getId();
        $parentId   = $parentNodeCategory->getParentId();

        $node = $tree->loadNode($nodeId);
        $node->loadChildren($recursionLevel);

        if ($node && $nodeId != Mage_Catalog_Model_Category::TREE_ROOT_ID) {
            $node->setIsVisible(true);
        } elseif($node && $node->getId() == Mage_Catalog_Model_Category::TREE_ROOT_ID) {
            $node->setName(Mage::helper('catalog')->__('Root'));
        }

        $tree->addCollectionData($this->getCategoryCollection());

        return $node;
    }
    
    
    
    
    public function getRoot($parentNodeCategory=null, $recursionLevel=null)
    {
        if (!is_null($parentNodeCategory)&& $parentNodeCategory->getId()) {
            return $this->getNode($parentNodeCategory, $recursionLevel);
        }
        $root = Mage::registry('root');
        if (is_null($root)) {
            $storeId = (int) $this->getRequest()->getParam('store');

            if ($storeId) {
                $store = Mage::app()->getStore($storeId);
                $rootId = $store->getRootCategoryId();
            }
            else {
                $rootId = Mage_Catalog_Model_Category::TREE_ROOT_ID;
            }
            
            /* @andrew: Hardcoded Root Category Id for all store views */
            $rootId = Mage_Catalog_Model_Category::TREE_ROOT_ID;

            $tree = Mage::getResourceSingleton('magedoc/tecdoc_searchTree_tree')
                ->load(null, $recursionLevel);

            if ($this->getCategory()) {
                $tree->loadEnsuredNodes($this->getCategory(), $tree->getNodeById($rootId));
            }

            $tree->addCollectionData($this->getCategoryCollection());
           
            $root = $tree->getNodeById($rootId);
            
            if ($root && $rootId != Mage_Catalog_Model_Category::TREE_ROOT_ID) {
                
                $root->setIsVisible(true);
            }
            elseif($root && $root->getId() == Mage_Catalog_Model_Category::TREE_ROOT_ID) {
                $root->setName(Mage::helper('catalog')->__('Root'));
            }

            Mage::register('root', $root);
        }

        return $root;
    }
    
    public function getEditUrl()
    {
        return $this->getUrl("*/adminhtml_category/edit", array('_current'=>true, 'store'=>null, '_query'=>false, 'id'=>null, 'parent'=>null));
    }

}
