<?php
require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml').DS.'Catalog'.DS.'CategoryController.php';

class Testimonial_MageDoc_Adminhtml_CategoryController extends Mage_Adminhtml_Catalog_CategoryController
{
    
    protected $_searchTreeCollection;
    protected $_collectionArray = array();

    
    protected function _getTdCategoryCollection()
    {
        if(!isset($this->_searchTreeCollection)){
            $this->_searchTreeCollection = 
                    Mage::getResourceModel('magedoc/tecdoc_searchTree_collection')
                        ->joinPath()
                        ->joinCategory()
                        ->joinDesignation($this->_searchTreeCollection, 'main_table', 'STR_DES_ID', 'name');
        }
        return $this->_searchTreeCollection;
    }

    public function getCategoryModel()
    {
        $this->_categoryModel = Mage::getModel('catalog/category');
        return $this->_categoryModel;
    }

    protected function _getCategoryData($tecdocSearchTreeItem, $storeId)
    {
        $category = $this->getCategoryModel();
        $ids = $tecdocSearchTreeItem->getPathIds();
        for ($i = count($ids) - 1; $i > 0; $i--) {
            $parentCategory = $category->loadByAttribute('td_str_id', $ids[$i]);
            if ($parentCategory) {
                $parentId = $parentCategory->getParentId();
                $path = $parentCategory->getPath();
                $level = $parentCategory->getLevel() + 1;
                break;
            }
        }
        if (!$parentCategory) {
            $parentId = Mage::helper('magedoc')->getSearchTreeRootCategoryId($storeId);
            $parentCategory = $category->load($parentId);
            $path = $parentCategory->getPath();
        }
        $data = array(
            'store_id'             => $storeId,
            'url_key'              => '',
            'description'          => '',
            'thumbnail'            => '',
            'image'                => '',
            'meta_title'           => '', 
            'meta_keywords'        => '',
            'meta_description'     => '',
            'include_in_menu'      => '',
            'url_path'             => '',
            'display_mode'         => '',
            'landing_page'         => '',
            'is_anchor'            => '',
            'page_layout'          => '',
            'custom_layout_update' => '',
            'parent_id'            => $parentId,
            'name'                 => $tecdocSearchTreeItem->getName(),
            'is_active'            => 0,
            'path'                 => $path,
            'td_str_id'            => $tecdocSearchTreeItem->getStrId(),
            'parent_category_name' => $parentCategory->getName()
        );
        $data = Mage::helper('magedoc')->getEntityDefaultValues('category', $data, $storeId);

        return $data;
    }


    protected function _initCategory($getRootInstead = false)
    {
        if ($category = Mage::registry('category')){
            return $category;
        }

        $this->_title($this->__('Catalog'))
             ->_title($this->__('Categories'))
             ->_title($this->__('Manage Categories'));

        $categoryId = (int) $this->getRequest()->getParam('id',false);
        
        $storeId    = (int) $this->getRequest()->getParam('store');
        $category = $this->getCategoryModel();
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            $strId = $category->getTdStrId();
            if ($storeId) {
                $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
                if (!in_array($rootId, $category->getPathIds())) {
                    // load root category instead wrong one
                    if ($getRootInstead) {
                        $category->load($rootId);
                    }
                    else {
                        $this->_redirect('*/*/', array('_current'=>true, 'id'=>null));
                        return false;
                    }
                }
            }
        }else{
            $strId = (int) $this->getRequest()->getParam('str_id',false);
            if($strId){
                $tecdocSearchTree = $this->_getTdCategoryCollection()
                    ->addFieldToFilter('main_table.STR_ID', $strId);

                $tecdocSearchTreeItem = $tecdocSearchTree->getFirstItem();
                if($tecdocSearchTreeItem->getEntityId() !== null){
                    $category = $this->getCategoryModel()
                                    ->load($tecdocSearchTreeItem->getEntityId());
                }else{
                    $categoryData = $this->_getCategoryData($tecdocSearchTreeItem, $storeId);
                    $category->unsetData();
                    $category->setData($categoryData);
                }
            }
        }
        $searchTree = Mage::getModel('magedoc/searchTree')->load($strId);
        $category->setSearchTree($searchTree);

        if ($activeTabId = (string) $this->getRequest()->getParam('active_tab_id')) {
            Mage::getSingleton('admin/session')->setActiveTabId($activeTabId);
        }
        $this->getRequest()->setParam('parent', $category->getParentId());
        
        Mage::register('category', $category);
        Mage::register('current_category', $category);
        Mage::getSingleton('cms/wysiwyg_config')->setStoreId($this->getRequest()->getParam('store'));
        return $category;
    }
    
    
    protected function _initSearchTree($getRootInstead = false)
    {
        $strId = (int) $this->getRequest()->getPost('id');
        $storeId    = (int) $this->getRequest()->getParam('store');
        $searchTree = Mage::getModel('magedoc/tecdoc_searchTree');
        $searchTree->setStoreId($storeId);

        if ($strId) {
            $searchTree->load($strId);
            if ($storeId) {
                $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
                if (!in_array($rootId, $searchTree->getPathIds())) {
                    // load root category instead wrong one
                    if ($getRootInstead) {
                        $searchTree->load($rootId);
                    }
                    else {
                        $this->_redirect('*/*/', array('_current'=>true, 'id'=>null));
                        return false;
                    }
                }
            }
        }

        if ($activeTabId = (string) $this->getRequest()->getParam('active_tab_id')) {
            Mage::getSingleton('admin/session')->setActiveTabId($activeTabId);
        }

        Mage::register('category', $searchTree);
        Mage::register('current_category', $searchTree);
        return $searchTree;
    }
    
   
    protected function _setActiveMenu($menuPath)
    {
        Mage_Adminhtml_Controller_Action::_setActiveMenu('magedoc/categories');
        return $this;
    }
   
    /**
     * Get tree node (Ajax version)
     */
    public function categoriesJsonAction()
    {
        if ($this->getRequest()->getParam('expand_all')) {
            Mage::getSingleton('admin/session')->setIsTreeWasExpanded(true);
        } else {
            Mage::getSingleton('admin/session')->setIsTreeWasExpanded(false);
        }
        if ($categoryId = (int) $this->getRequest()->getPost('id')) {
            $this->getRequest()->setParam('id', $categoryId);
            if (!$category = $this->_initSearchTree()) {
                return;
            }
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('magedoc/adminhtml_category_tree')
                    ->getTreeJson($category)
            );
        }
    }

    public function saveAction()
    {

        if (!$category = $this->_initCategory()) {
            return;
        }

        $refreshTree = 'false';
        if ($data = $this->getRequest()->getPost()) {
            if ($category->getId() || !empty($data['general']['is_active']) || empty($data['general']['search_tree'])){
                parent::saveAction();
            }
            try{
                $searchTree = $category->getSearchTree();
                $searchTree->addData($data['general']['search_tree']);
                $searchTree->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magedoc')->__('Search tree has been saved.'));
                $refreshTree = 'true';
            }
            catch (Exception $e){
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage())
                    ->setCategoryData($data);
                $refreshTree = 'false';
            }
        }

        $url = $this->getUrl('*/*/edit', array('_current' => true, 'str_id' => $category->getTdStrId()));
        $this->getResponse()->setBody(
            '<script type="text/javascript">parent.updateContent("' . $url . '", {}, '.$refreshTree.');</script>'
        );
    }

    public function listAction()
    {
        $source = Mage::getModel('magedoc/source_category')->getOptionArray();
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($source));
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('magedoc/categories');
    }
}
