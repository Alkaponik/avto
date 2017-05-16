<?php

class Phoenix_LayeredNav_Block_Html_Pager extends Mage_Page_Block_Html_Pager
{
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
