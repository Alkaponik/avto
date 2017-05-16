<?php

class Testimonial_MageDoc_Block_Adminhtml_Product_Criteria extends Testimonial_MageDoc_Block_Criteria
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('magedoc/product/criteria.phtml')->setArea('frontend');
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


