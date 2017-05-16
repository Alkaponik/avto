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
 * @package    Phoenix_BlockCaching
 * @copyright  Copyright (c) 2009 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 */

class Phoenix_BlockCaching_Model_Observer
{
    protected $_blockCacheState = array();
    
    const CACHE_CACHED = 1;
    const CACHE_NOT_CACHED = 2;
    const CACHE_NOT_CACHEABLE = 0;
    const CACHE_SESSION_CACHED = 3;
    
    public function stockUpdate($observer)
    {
        if (!Mage::helper('blockcaching')->getConfig('catalog/product_view_enabled') 
                && !Mage::helper('blockcaching')->getConfig('catalog/product_list_enabled')
                && !Mage::helper('blockcaching')->getConfig('catalog/catalog_view_enabled')) {
            return $this;
        }

        // get ordered item
        $item = $observer->getEvent()->getItem();

        if ($productId = $item->getProductId()) {
            $product = Mage::getModel('catalog/product')->load($productId);
            $stock = $product->getStockItem();
            if ($stock->getManageStock() && $stock->getQty() <= 0) {
                // clean cache
                $product->cleanCache();
                Mage::helper('blockcaching')->cleanCategoryCache($product->getCategoryIds());
            }
        }

        return $this;
    }

    public function reviewUpdate($observer)
    {
        if (!Mage::helper('blockcaching')->getConfig('catalog/product_view_enabled') 
                && !Mage::helper('blockcaching')->getConfig('catalog/product_list_enabled')) {
            return $this;
        }
        
        // get review
        $review = $observer->getEvent()->getObject();
        
        foreach ($review->getProductCollection() as $product) {
            $product->cleanCache();
        }

        return $this;
    }
    
    public function afterToHtml($observer)
    {
        $block = $observer->getEvent()->getBlock();
        $transport = $observer->getEvent()->getTransport();
        
        $cacheHandler = Mage::helper('blockcaching')->getCacheHandlers(get_class($block));
        if ($cacheHandler && ($cacheHandler = Mage::getSingleton($cacheHandler['handler_model'])) 
                    && $cacheHandler->isCacheActive($block) && $cacheHandler->isStorableInSession($block)) {
            switch ($this->_blockCacheState($block)) {
                case self::CACHE_NOT_CACHED:
                    Mage::getSingleton('checkout/session')->setData(
                        $cacheHandler->getSessionVarName($block),
                        array(
                            'hash' => $cacheHandler->getSessionCacheHash($block),
                            'html' => $transport->getHtml()
                        )
                    );
                    break;
                default:
                    break;
            }
        } 
            
        if (Mage::helper('blockcaching')->getConfig('debug/enable') && Mage::helper('blockcaching')->isDevAllowed()) {
            $transport->setHtml($this->_decorateBlock($block, $transport->getHtml()));
        }
        return $this;
    }
    
    public function beforeToHtml($observer)
    {
        $block = $observer->getEvent()->getBlock();
        foreach (Mage::helper('blockcaching')->getCacheableBlocks() as $blockClass) {
            if ($block instanceof $blockClass) {
                $cacheHandler = Mage::helper('blockcaching')->getCacheHandlers($blockClass);
                $cacheHandler = Mage::getSingleton($cacheHandler['handler_model']);
                if (!$cacheHandler->isCacheActive($block)) {
                    continue;
                }
                
                if ($cacheHandler->isStorableInSession($block)) {
                    // If cache handler allows to save cache in session
                    if ($this->_blockCacheState($block) == self::CACHE_SESSION_CACHED) {
                        // already cached in session (see afterToHtml())
                        $block->setCacheKey($cacheHandler->getSessionTempCacheKey($block))
                            ->setCacheTags($cacheHandler->getSessionTempCacheTag($block))
                            ->setCacheLifetime(1000);
                        $sessionData = Mage::getSingleton('checkout/session')->getData($cacheHandler->getSessionVarName($block));
                        // hack to prevent rendering of this block, but take a value from session
                        Mage::app()->saveCache($sessionData['html'], $block->getCacheKey(), $block->getCacheTags(), $block->getCacheLifetime());
                    }
                }
                else {
                    $block->setCacheKey($cacheHandler->getCacheKey($block))
                        ->setCacheTags($cacheHandler->getCacheTags($block))
                        ->setCacheLifetime($cacheHandler->getCacheLifetime());
                    
                    if (Mage::helper('blockcaching')->getConfig('debug/enable') && Mage::helper('blockcaching')->isDevAllowed()) {
                        $block->setCacheKey($block->getCacheKey() . '___DEV___');
                        $this->_blockCacheState($block);
                    }
                }
            }
        }
        
        return $this;
    }
    
    protected function _blockCacheState(Mage_Core_Block_Abstract $block)
    {
        $type = get_class($block);
        if (!isset($this->_blockCacheState[$type])) {
            $cacheHandler = Mage::helper('blockcaching')->getCacheHandlers($type);
            if (is_array($cacheHandler) && ($cacheHandler = Mage::getSingleton($cacheHandler['handler_model'])) && $cacheHandler->isStorableInSession($block)) {
                $sessionData = Mage::getSingleton('checkout/session')->getData($cacheHandler->getSessionVarName($block));
                if (is_array($sessionData) && $sessionData['hash'] == $cacheHandler->getSessionCacheHash($block)) {
                    $this->_blockCacheState[$type] = self::CACHE_SESSION_CACHED;
                }
                else {
                    $this->_blockCacheState[$type] = self::CACHE_NOT_CACHED;
                }
            }
            elseif (is_null($block->getCacheLifetime()) || !Mage::app()->useCache(Mage_Core_Block_Abstract::CACHE_GROUP)) {
                $this->_blockCacheState[$type] = self::CACHE_NOT_CACHEABLE;
            }
            elseif (Mage::app()->loadCache($block->getCacheKey()) === false) {
                $this->_blockCacheState[$type] = self::CACHE_NOT_CACHED;
            }
            else {
                $this->_blockCacheState[$type] = self::CACHE_CACHED;
            }
        }
        return $this->_blockCacheState[$type];
    }
    
    protected function _decorateBlock($block, $html)
    {
        switch ($this->_blockCacheState($block)) {
            case self::CACHE_CACHED:
                $color = 'green';
                break;
            case self::CACHE_NOT_CACHED:
                $color = '#EDBD00';
                break;
            case self::CACHE_SESSION_CACHED:
                $color = 'blue';
                break;
            case self::CACHE_NOT_CACHEABLE:
                if (Mage::helper('blockcaching')->getConfig('debug/highlight_noncacheable_blocks')) {
                    $color = 'red';
                    break;
                }
            default:
                return $html;
                break;
        }
        
//        var_dump(get_class($block->getParentBlock()));
        $blockType = get_class($block);
        return '<div style="position:relative; border:1px dotted ' . $color . '; margin:6px 2px; padding:18px 2px 2px 2px; zoom:1;">
                    <div style="position:absolute; left:0; top:0; padding:2px 5px; background:' . $color . '; color:white; 
                        font:normal 11px Arial; text-align:left !important; z-index:998;" onmouseover="this.style.zIndex=\'999\'" 
                        onmouseout="this.style.zIndex=\'998\'" title="' . $blockType 
                        . ($this->_blockCacheState($block) != self::CACHE_NOT_CACHEABLE && $this->_blockCacheState($block) != self::CACHE_SESSION_CACHED ? ' TTL: ' . $block->getCacheLifetime() . '; ' : '')
                        . ($this->_blockCacheState($block) != self::CACHE_NOT_CACHEABLE && $this->_blockCacheState($block) != self::CACHE_SESSION_CACHED ? ' Cache key: ' . str_replace('___DEV___', '', $block->getCacheKey()) . '; ' : '')
                        . ($this->_blockCacheState($block) != self::CACHE_NOT_CACHEABLE && $this->_blockCacheState($block) != self::CACHE_SESSION_CACHED ? ' Cache tags: ' . implode(', ', $block->getCacheTags()) . '; ' : '')
                        . ($this->_blockCacheState($block) == self::CACHE_SESSION_CACHED ? ' Cached in session' . '; ' : '')
                        . ($block->getTemplate() ? ' Template: ' . $block->getTemplate() . '; ' : '') 
                        . ($block->getNameInLayout() ? ' Name in layout: ' . $block->getNameInLayout() . '; ' : '')
                        . '">'
                            . $blockType . ($this->_blockCacheState($block) != self::CACHE_NOT_CACHEABLE && $this->_blockCacheState($block) != self::CACHE_SESSION_CACHED ? ' TTL:' . $block->getCacheLifetime() : '') 
                    . '</div>'
                    . $html 
               . '</div>';
    }
}