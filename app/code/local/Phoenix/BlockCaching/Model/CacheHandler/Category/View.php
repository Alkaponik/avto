<?php
class Phoenix_BlockCaching_Model_CacheHandler_Category_View extends Phoenix_BlockCaching_Model_CacheHandler_Abstract
{
    public function isCacheActive(Mage_Core_Block_Abstract $block) 
    {
        if ($this->_isCacheActive === null) {
            if (!Mage::getStoreConfigFlag('blockcaching/catalog/catalog_view_enabled') 
                    || Mage::getSingleton('core/session')->getMessages()->count() > 0 
                    || Mage::getSingleton('catalog/session')->getMessages()->count() > 0
                    || Mage::getSingleton('checkout/session')->getMessages()->count() > 0) {
                $this->_isCacheActive = false;
            }
            else {
                $this->_isCacheActive = true;
            }
        }
        return $this->_isCacheActive;
    }
    
    public function getCacheKey(Mage_Core_Block_Abstract $block)
    {
        return 'CATEGORY_VIEW_' . Mage::app()->getStore()->getId()
             . '_' . Mage::getDesign()->getPackageName()
             . '_' . Mage::getDesign()->getTheme('template')
             . '_' . Mage::getSingleton('customer/session')->getCustomerGroupId()
             . '_' . $block->getCurrentCategory()->getId()
             . '_' . md5($block->getTemplate() . $block->getCurrentCategoryKey() . $_SERVER['REQUEST_URI'])
             . '_' . Mage::app()->getStore()->isCurrentlySecure()
             . '_' . Mage::app()->getStore()->getCurrentCurrencyCode();
    }
    
    public function getCacheTags(Mage_Core_Block_Abstract $block)
    {
        return array(
            Mage_Catalog_Model_Category::CACHE_TAG . '_' . $block->getCurrentCategory()->getId(),
            Mage_Catalog_Model_Category::CACHE_TAG,
            Mage_Catalog_Model_Product::CACHE_TAG,
            Mage_Core_Model_Store_Group::CACHE_TAG,
            'block_html'
        );
    }
    
    public function getCacheLifetime()
    {
        return (int)Mage::helper('blockcaching')->getConfig('catalog/lifetime');
    }
}