<?php
class Phoenix_Brands_AdminController extends Mage_Adminhtml_Controller_Action
{
    public function generateCategoriesAction()
    {
        $storeId = $this->getRequest()->getParam('store');
        if (!$brandsRootCategory = Mage::helper('phoenixbrands')->getBrandsRootCategoryId($this->getRequest()->getParam('store'))) {
            $this->_redirectUrl($this->getRequest()->getHeader('Referer'));
            return;
        }
        
        if (!$brandAttribute = Mage::helper('phoenixbrands')->getConfig('brands_attribute')) {
            $this->_redirectUrl($this->getRequest()->getHeader('Referer'));
            return;
        }
        
        $createdCategories = count(Mage::getModel('phoenixbrands/synchronizer')->createCategoriesFromAttributeOptions($brandsRootCategory, $brandAttribute, $storeId));
        if ($createdCategories > 0) {
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('%s categories were successfully created or changed', $createdCategories));
        }
        else {
            Mage::getSingleton('adminhtml/session')->addError($this->__('No categories were created or changed'));
        }
        
        $this->_redirectUrl($this->getRequest()->getHeader('Referer'));
    }
}