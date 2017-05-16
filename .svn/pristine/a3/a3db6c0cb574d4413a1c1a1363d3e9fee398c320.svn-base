<?php
class Testimonial_MageDoc_Block_Adminhtml_Retailer_Price extends Testimonial_MageDoc_Block_Price
{          
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('magedoc/product/price.phtml')->setArea('frontend');
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