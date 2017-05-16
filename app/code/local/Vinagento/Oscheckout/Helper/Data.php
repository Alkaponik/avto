<?php
class Vinagento_Oscheckout_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isActive() {
        return (boolean) Mage::getStoreConfig('oscheckout/general/active');
    }
}