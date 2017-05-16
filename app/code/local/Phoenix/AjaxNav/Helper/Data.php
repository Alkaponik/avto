<?php

class Phoenix_AjaxNav_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isEnabled()
    {
        return Mage::getStoreConfig('ajaxnav/settings/enabled');
    }

    public function getHandleSelector($handles)
    {
        return "'.block-layered-nav a, .toolbar a, .toolbar option'";
        return "$$('.block-layered-nav a, .toolbar a, .toolbar option')";
    }
}