<?php
class Phoenix_Brands_Block_Adminhtml_FrontendModel_GenerateCategories extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        
        $store = Mage::app()->getStore(Mage::app()->getRequest()->getParam('store'));
        if (!$store->getId()) {
            return '';
        }
        
        $brandsRootCategoryId = Mage::helper('phoenixbrands')->getBrandsRootCategoryId($store->getId());
        if (!$brandsRootCategoryId) {
            return $this->__('Please specify brands root category for this store and save configuration');
        }
        
        if (!Mage::helper('phoenixbrands')->getConfig('brands_attribute')) {
            return $this->__('Please specify brands attribute and save configuration');
        }
        
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setLabel($this->__('Generate categories'))
            ->setOnClick("window.location = '" . Mage::getSingleton('adminhtml/url')->getUrl('phoenixbrands/admin/generateCategories', array('store' => $store->getId())) . "'")
            ->toHtml();
        
        return $button;
    }
}
