<?php
class Phoenix_BlockCaching_Model_CacheHandler_Template extends Phoenix_BlockCaching_Model_CacheHandler_Abstract
{
    protected $_templateAleasesToCache = null;
    
    public function isCacheActive(Mage_Core_Block_Abstract $block) 
    {
        if (!Mage::getStoreConfigFlag('blockcaching/template/template_enabled') 
                    || !in_array($block->getBlockAlias(), $this->_getTemplateAliasesToCache())) {
            return false;
        }
        else {
            return true;
        }
    }
    
    public function getCacheKey(Mage_Core_Block_Abstract $block)
    {
        return 'CORE_TEMPLATE_' . Mage::app()->getStore()->getId()
             . '_' . $block->getNameInLayout()
             . '_' . (int)Mage::getSingleton('customer/session')->isLoggedIn()
             . '_' . Mage::getSingleton('customer/session')->getCustomerGroupId()
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
    
    protected function _getTemplateAliasesToCache()
    {
        if ($this->_templateAleasesToCache === null) {
            $this->_templateAleasesToCache = explode("\n", Mage::helper('blockcaching')->getConfig('template/template_include'));
        }
        return $this->_templateAleasesToCache;
    }
}