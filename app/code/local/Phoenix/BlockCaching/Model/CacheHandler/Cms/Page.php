<?php
class Phoenix_BlockCaching_Model_CacheHandler_Cms_Page extends Phoenix_BlockCaching_Model_CacheHandler_Abstract
{
    protected $_cmsPageUrlsToCache;
    
    public function isCacheActive(Mage_Core_Block_Abstract $block) 
    {
        if ($this->_isCacheActive === null) {
            if (!Mage::getStoreConfigFlag('blockcaching/cms/cms_pages_enabled')
                    || in_array($block->getPage()->getIdentifier(), $this->_getCmsPageUrlsToCache())) {
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
        return 'CMS_PAGE_' . Mage::app()->getStore()->getId()
             . '_' . $block->getPage()->getId()
             . '_' . Mage::app()->getStore()->isCurrentlySecure();
    }
    
    public function getCacheTags(Mage_Core_Block_Abstract $block)
    {
        return array(Mage_Cms_Model_Page::CACHE_TAG, Mage_Core_Model_Store_Group::CACHE_TAG);
    }
    
    public function getCacheLifetime()
    {
        return (int)Mage::helper('blockcaching')->getConfig('cms/lifetime');
    }
    
    protected function _getCmsPageUrlsToCache()
    {
        if ($this->_cmsPageUrlsToCache === null) {
            $this->_cmsPageUrlsToCache = explode("\n", Mage::helper('blockcaching')->getConfig('cms/cms_pages_exclude'));
        }
        return $this->_cmsPageUrlsToCache;
    }
}