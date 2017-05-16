<?php
class Phoenix_BlockCaching_Model_CacheHandler_Cms_Block extends Phoenix_BlockCaching_Model_CacheHandler_Abstract
{
    protected $_cmsBlockIdsToCache;
    
    public function isCacheActive(Mage_Core_Block_Abstract $block) 
    {
        if ($this->_isCacheActive === null) {
            if (!Mage::getStoreConfigFlag('blockcaching/cms/cms_block_enabled')
                    || in_array($block->getBlockId(), $this->_getCmsBlockIdsToCache())) {
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
        return 'CMS_BLOCK_' . Mage::app()->getStore()->getId()
             . '_' . $block->getBlockId()
             . '_' . Mage::app()->getStore()->isCurrentlySecure();
    }
    
    public function getCacheTags(Mage_Core_Block_Abstract $block)
    {
        return array(Mage_Cms_Model_Block::CACHE_TAG, Mage_Core_Model_Store_Group::CACHE_TAG);
    }
    
    public function getCacheLifetime()
    {
        return (int)Mage::helper('blockcaching')->getConfig('cms/lifetime');
    }
    
    protected function _getCmsBlockIdsToCache()
    {
        if ($this->_cmsBlockIdsToCache === null) {
            $this->_cmsBlockIdsToCache = explode(',', Mage::helper('blockcaching')->getConfig('cms/cms_block_exclude'));
        }
        return $this->_cmsBlockIdsToCache;
    }
}