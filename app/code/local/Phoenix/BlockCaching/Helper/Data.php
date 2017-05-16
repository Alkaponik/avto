<?php
/**
 * Magento - Block Caching
 *
 * NOTICE OF LICENSE
 * 
 * This source file is subject to license that is bundled with
 * this package in the file LICENSE.txt.
 *
 * @category   Phoenix
 * @package	   Phoenix_BlockCaching
 * @copyright  Copyright (c) 2009 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 */

class Phoenix_BlockCaching_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_configRoot = 'blockcaching';
    protected $_cacheHandlers = null;
    protected $_isDevAllowed = null;
    
    const CACHE_HANDLERS_CONFIG_PATH = 'block_config';
    
    public function getConfig($key, $store = null)
    {
        return Mage::getStoreConfig("$this->_configRoot/$key", $store);
    }
    
    public function getCacheHandlers($blockClass = null) 
    {
        if ($this->_cacheHandlers === null) {
            $this->_cacheHandlers = Mage::getConfig()->getNode(self::CACHE_HANDLERS_CONFIG_PATH)->asArray();
            uasort($this->_cacheHandlers, array($this, '_handlersSortCallback'));
        }
        
        if ($blockClass) {
            return isset($this->_cacheHandlers[$blockClass]) ? $this->_cacheHandlers[$blockClass] : false;
        }
        else {
            return $this->_cacheHandlers;
        }
    }
    
    protected function _handlersSortCallback($handler1, $handler2)
    {
        if (!isset($handler1['priority']) && isset($handler2['priority'])) {
            return 1;
        }
        elseif (!isset($handler2['priority']) && isset($handler1['priority'])) {
            return -1;
        }
        elseif (!isset($handler2['priority']) && !isset($handler1['priority'])) {
            return 0;
        }
        
        if ($handler1['priority'] == $handler2['priority']) {
            return 0;
        }
        return $handler1['priority'] > $handler2['priority'] ? -1 : 1;
    }
    
    public function getCacheableBlocks()
    {
        return array_keys($this->getCacheHandlers());
    }
    
    public function isDevAllowed($storeId = null)
    {
        if ($this->_isDevAllowed === null) {
            $this->_isDevAllowed = true;
    
            $allowedIps = $this->getConfig('debug/allow_ips', $storeId);
            $remoteAddr = Mage::helper('core/http')->getRemoteAddr();
            if (!empty($allowedIps) && !empty($remoteAddr)) {
                $allowedIps = preg_split('#\s*,\s*#', $allowedIps, null, PREG_SPLIT_NO_EMPTY);
                if (array_search($remoteAddr, $allowedIps) === false
                    && array_search(Mage::helper('core/http')->getHttpHost(), $allowedIps) === false) {
                    $this->_isDevAllowed = false;
                }
            }
        }
        return $this->_isDevAllowed;
    }
    
    public function cleanCategoryCache($categories = array())
    {
        if (is_numeric($categories)) {
            $categories = array($categories);
        }
        
        foreach ($categories as $categoryId) {
            Mage::app()->cleanCache(Mage_Catalog_Model_Category::CACHE_TAG . '_' . $categoryId);
        }
        
        return true;
    }
}
