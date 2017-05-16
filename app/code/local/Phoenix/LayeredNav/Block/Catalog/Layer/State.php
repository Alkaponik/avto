<?php


/**
 * Layered navigation state
 *
 * @category   Mage
 * @package    Phoenix_LayeredNav
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Phoenix_LayeredNav_Block_Catalog_Layer_State extends Mage_Catalog_Block_Layer_State
{    

    /**
     * Retrieve Clear Filters URL
     *
     * @return string
     */
    public function getClearUrl()
    {
        if (!Mage::helper('phoenix_layerednav')->isSeoUrlEnabled()){
            return parent::getClearUrl();
        }
        $filterState = array();
        foreach ($this->getActiveFilters() as $item) {
            $filterState[$item->getFilter()->getRequestVar()] = $item->getFilter()->getCleanValue();
        }
        $params['_current']     = true;
        $params['_use_rewrite'] = true;
        $params['_query']       = $filterState;
        $params['_escape']      = true;

        foreach (Mage::helper('phoenix_layerednav')->getFilterAlias() as $alias){
            $params[$alias['alias']] = null;
        }

        return Mage::getUrl('*/*/*', $params);
    }
    
}
