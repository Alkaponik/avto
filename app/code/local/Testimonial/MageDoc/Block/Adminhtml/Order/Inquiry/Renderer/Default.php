<?php

class Testimonial_MageDoc_Block_Adminhtml_Order_Inquiry_Renderer_Default 
    extends Mage_Adminhtml_Block_Sales_Items_Renderer_Default
{
    public function getInquiry()
    {
        return $this->_getData('inquiry');
    }
    
    public function getRetailerOptions()
    {
        return Mage::getModel('magedoc/source_retailer')->getOptionArray();
    }
}