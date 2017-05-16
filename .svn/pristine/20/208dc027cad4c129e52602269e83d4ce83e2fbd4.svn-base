<?php

class Testimonial_MageDoc_Block_Adminhtml_Order_Inquiry_Column_Qty 
    extends Mage_Adminhtml_Block_Sales_Items_Column_Default
{
    public function getInquiry()
    {
        $inquiry = $this->_getData('item'); 
        if($inquiry->hasData('order_inquiry_id')){
            return $inquiry->getOrderInquiry();
        } else {
            return $inquiry;
        }
        
    }

}