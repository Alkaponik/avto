<?php

class Testimonial_MageDoc_Block_Adminhtml_Product_Information 
    extends Testimonial_MageDoc_Block_Product_Information
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('magedoc/product/information.phtml')->setArea('frontend');
    }
    
    protected function _getUrlModelClass()
    {
        return 'adminhtml/url';
    }

    public function getFormKey()
    {
        return Mage::getSingleton('core/session')->getFormKey();
    }
    
}


