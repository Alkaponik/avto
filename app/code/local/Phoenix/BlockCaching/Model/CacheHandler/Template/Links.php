<?php
class Phoenix_BlockCaching_Model_CacheHandler_Template_Links extends Phoenix_BlockCaching_Model_CacheHandler_Abstract
{
    public function isCacheActive(Mage_Core_Block_Abstract $block) 
    {
        if ($this->_isCacheActive === null) {
            if (!Mage::getStoreConfigFlag('blockcaching/template/page_links_enabled')) {
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
        return 'PAGE_LINK_' . Mage::app()->getStore()->getId()
             . '_' . (int)Mage::getSingleton('customer/session')->isLoggedIn()
             . '_' . Mage::getSingleton('customer/session')->getCustomerGroupId()
             . '_' . Mage::helper('checkout/cart')->getSummaryCount()
             . '_' . md5($block->getTemplate())
             . '_' . Mage::app()->getStore()->isCurrentlySecure();
    }
    
    public function getCacheTags(Mage_Core_Block_Abstract $block)
    {
        return array(Mage_Core_Model_Store::CACHE_TAG);
    }
    
    public function getCacheLifetime()
    {
        return (int)Mage::helper('blockcaching')->getConfig('template/lifetime');
    }
}