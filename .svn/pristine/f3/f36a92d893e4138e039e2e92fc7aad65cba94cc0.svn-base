<?php

class Testimonial_MageDoc_Block_Adminhtml_Order_Invoice_Create_Inquiries 
        extends Mage_Adminhtml_Block_Sales_Order_Invoice_Create_Items
{        
    public function getInquiryHtml(Varien_Object $inquiry)
    {
        return $this->getItemRenderer('inquiry_default')
            ->setInquiry($inquiry)
            ->setCanEditQty($this->canEditQty())
            ->toHtml();
    } 
}
