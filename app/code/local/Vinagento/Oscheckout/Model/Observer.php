<?php
class Vinagento_Oscheckout_Model_Observer{
    public function osRedirect($observer) {
        if (Mage::helper('oscheckout')->isActive()) {
	          Mage::app()->getResponse()->setRedirect(Mage::getUrl("checkout/onestep"));
        }
    }	
}