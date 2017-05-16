<?php

class Phoenix_LayeredNav_Block_Catalog_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{
    /**
     * Return current URL with rewrites and additional parameters
     *
     * @param array $params Query parameters
     * @return string
     */
    public function getPagerUrl($params=array())
    {
        if (!Mage::helper('phoenix_layerednav')->isSeoUrlEnabled()){
            return parent::getPagerUrl($params);
        }
        $urlParams = array();
        $urlParams['_current']  = true;
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = true;
        $urlParams['_query']    = $params;
        
        $pathInfo = Mage::registry('orig_path_info');
        if (is_null($pathInfo)){
            $pathInfo = ltrim(Mage::app()->getRequest()->getOriginalPathInfo(),'/');
        }

        $urlParams['_direct']=$pathInfo;

        return $this->getUrl('*/*/*', $urlParams);
    }
}
