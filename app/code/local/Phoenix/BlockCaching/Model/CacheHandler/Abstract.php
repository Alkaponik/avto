<?php
abstract class Phoenix_BlockCaching_Model_CacheHandler_Abstract
{
    protected $_isCacheActive = null;
    
    abstract public function isCacheActive(Mage_Core_Block_Abstract $block);
    
    abstract public function getCacheKey(Mage_Core_Block_Abstract $block);
    
    abstract public function getCacheTags(Mage_Core_Block_Abstract $block);
    
    abstract public function getCacheLifetime();
    
    public function isStorableInSession(Mage_Core_Block_Abstract $block)
    {
        return false;
    }
    
    public function getSessionCacheHash(Mage_Core_Block_Abstract $block)
    {
        return false;
    }
    
    public function getSessionVarName(Mage_Core_Block_Abstract $block)
    {
        return false;
    }
    
    public function getSessionTempCacheKey(Mage_Core_Block_Abstract $block)
    {
        return false;
    }
    
    public function getSessionTempCacheTag(Mage_Core_Block_Abstract $block)
    {
        return array(get_class($block));
    }
}