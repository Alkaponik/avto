<?php
class Phoenix_BlockCaching_Model_CacheHandler_Product_List_Upsell extends Phoenix_BlockCaching_Model_CacheHandler_Abstract
{
    public function isCacheActive(Mage_Core_Block_Abstract $block) 
    {
        if ($this->_isCacheActive === null) {
            if (!Mage::getStoreConfigFlag('blockcaching/catalog/product_upsell_enabled')) {
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
        return 'PRODUCT_UPSELL_' . Mage::app()->getStore()->getId()
             . '_' . Mage::getDesign()->getPackageName()
             . '_' . Mage::getDesign()->getTheme('template')
             . '_' . $block->getProduct()->getId()
             . '_' . Mage::getSingleton('customer/session')->getCustomerGroupId()
             . '_' . md5($block->getTemplate() . $_SERVER['REQUEST_URI'])
             . '_' . Mage::app()->getStore()->isCurrentlySecure()
             . '_' . Mage::app()->getStore()->getCurrentCurrencyCode();
    }
    
    public function getCacheTags(Mage_Core_Block_Abstract $block)
    {
        return array(
            Mage_Catalog_Model_Product::CACHE_TAG . '_' . $block->getProduct()->getId(),
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