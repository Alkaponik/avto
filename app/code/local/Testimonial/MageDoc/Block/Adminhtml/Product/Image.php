<?php

class Testimonial_MageDoc_Block_Adminhtml_Product_Image 
    extends Testimonial_MageDoc_Block_Product_Image
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('magedoc/product/image.phtml')->setArea('frontend');
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


