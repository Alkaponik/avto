<?php
class Phoenix_BlockCaching_Model_CacheHandler_Checkout_Cart_Sidebar extends Phoenix_BlockCaching_Model_CacheHandler_Abstract
{
    public function isCacheActive(Mage_Core_Block_Abstract $block) 
    {
        if ($this->_isCacheActive === null) {
            if (!Mage::getStoreConfigFlag('blockcaching/checkout/cart_sidebar_enabled')) {
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
        if (Mage::getSingleton('checkout/cart')->getItemsCount() == 0) {
            return 'CHECKOUT_CART_SIDEBAR_BLOCK_' . Mage::app()->getStore()->getId()
                 . '_' . Mage::app()->getStore()->isCurrentlySecure();
        }
        return null;
    }
    
    public function getCacheTags(Mage_Core_Block_Abstract $block)
    {
        if (Mage::getSingleton('checkout/cart')->getItemsCount() == 0) {
            return array(Mage_Core_Model_Store::CACHE_TAG);
        }
        return null;
    }
    
    public function getCacheLifetime()
    {
        if (Mage::getSingleton('checkout/cart')->getItemsCount() == 0) {
            return 3600*8;
        }
        return null;
    }
    
    public function isStorableInSession(Mage_Core_Block_Abstract $block)
    {
        return Mage::getSingleton('checkout/cart')->getItemsCount() > 0;
    }
    
    public function getSessionCacheHash(Mage_Core_Block_Abstract $block)
    {
        return md5(
            Mage::getSingleton('checkout/cart')->getQuote()->getId() . '_' .
            Mage::getSingleton('checkout/cart')->getItemsCount() . '_' .
            Mage::getSingleton('checkout/cart')->getQuote()->getUpdatedAt()
        );
    }
    
    public function getSessionVarName(Mage_Core_Block_Abstract $block)
    {
        return 'CHECKOUT_CART_SIDEBAR_BLOCK';
    }
    
    public function getSessionTempCacheKey(Mage_Core_Block_Abstract $block)
    {
        return 'CHECKOUT_CART_SIDEBAR_BLOCK_' . Mage::getSingleton('checkout/cart')->getQuote()->getId();
    }
    
    public function getSessionTempCacheTag(Mage_Core_Block_Abstract $block)
    {
        return array('CHECKOUT_CART_SIDEBAR_BLOCK');
    }
}