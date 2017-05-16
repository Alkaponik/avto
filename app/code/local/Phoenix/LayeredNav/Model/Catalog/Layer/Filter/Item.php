<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Phoenix
 * @package    Phoenix_LayeredNav
 * @copyright  Copyright (c) 2011 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 */

class Phoenix_LayeredNav_Model_Catalog_Layer_Filter_Item extends Mage_Catalog_Model_Layer_Filter_Item
{
    
    /**
     * Get filter item url
     *
     * @return string
     */
    public function getUrl()
    {
        $helper = Mage::helper('phoenix_layerednav');
        if (!$helper->isSeoUrlEnabled()){
            return parent::getUrl();
        }
        $query = array(
            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
        );

        foreach ($helper->getFilterAlias() as $alias){
            $query[$alias['alias']] = null;
        }

        $params = array('_current'=>true, '_use_rewrite'=>true);

        $pathInfo = Mage::registry('orig_path_info');
        if (is_null($pathInfo)){
            $pathInfo = ltrim(Mage::app()->getRequest()->getOriginalPathInfo(),'/');
        }

        if ($helper->getFilterAliasByRequestVar($this->getFilter()->getRequestVar())){
            if (!$helper->isMultipleSelectFilter($this->getFilter())){
                $pathInfo = $helper->removeFilterFromPath($pathInfo,$this->getFilter()->getRequestVar());
                $pathInfo = $helper->appendFilterToPath($pathInfo,$this->getFilter()->getRequestVar(),$this->getLabel());
            }elseif (!$helper->isFilterApplied($this->getFilter()->getRequestVar(), $this->getValue())){
                $pathInfo = $helper->appendFilterToPath($pathInfo,$this->getFilter()->getRequestVar(),$this->getLabel());
            }else{
                $pathInfo = $helper->removeFilterFromPath($pathInfo,$this->getFilter()->getRequestVar(),$this->getLabel());
            }

        }else{
            if (!$helper->isMultipleSelectFilter($this->getFilter())){
                $query[$this->getFilter()->getRequestVar()]=$this->getValue();
            }else{
                $values = $this->getCurrentFilterValues();
                if (!$helper->isFilterApplied($this->getFilter()->getRequestVar(), $this->getValue())){
                    $values[$this->getValue()] = $this->getValue();
                }
                $query[$this->getFilter()->getRequestVar()] = count($values)
                        ? implode(',',$values)
                        : null;
            }
        }

        $params['_direct']=$pathInfo;
        $params['_query'] = $query;
        return Mage::getUrl('*/*/*', $params);
        //return Mage::getUrl('*/*/*', array('_current'=>true, '_use_rewrite'=>true, '_query'=>$query));
    }

    /**
     * Get url for remove item from filter
     *
     * @return string
     */
    public function getRemoveUrl()
    {
        $helper = Mage::helper('phoenix_layerednav');
        if (!$helper->isSeoUrlEnabled()){
            return parent::getRemoveUrl();
        }
        $query = !$helper->isMultipleSelectFilter($this->getFilter())
            ? array($this->getFilter()->getRequestVar()
                        => $this->getFilter()->getResetValue())
            : array($this->getFilter()->getRequestVar()
                        => implode(',',$this->getCurrentFilterValues()));
        foreach ($helper->getFilterAlias() as $alias){
            $query[$alias['alias']] = null;
        }
        $pathInfo = Mage::registry('orig_path_info');
        if (!is_null($pathInfo))
        {
            $value = Mage::helper('phoenix_layerednav')
                ->isMultipleSelectFilter($this->getFilter())
                    ? $this->getLabel()
                    : null;

            $params['_direct'] = $helper->removeFilterFromPath($pathInfo,$this->getFilter()->getRequestVar(), $value);
        }

        $params['_current']     = true;
        $params['_use_rewrite'] = true;
        $params['_query']       = $query;
        $params['_escape']      = true;
        return Mage::getUrl('*/*/*', $params);
    }

    public function getCurrentFilterValues($excludeCurrent = true)
    {
        $values = array();
        foreach ($this->getFilter()->getLayer()->getState()->getFilters() as $item){
            if ($item->getFilter() === $this->getFilter()
                && (!$excludeCurrent || $item->getValue() != $this->getValue())){
                $values[$item->getValue()] = $item->getValue();
            }
        }
        return $values;
    }
}