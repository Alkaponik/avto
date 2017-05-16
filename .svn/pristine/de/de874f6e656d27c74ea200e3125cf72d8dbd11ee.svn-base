<?php

class Testimonial_MageDoc_Block_Adminhtml_Order_View_Inquiry extends Mage_Adminhtml_Block_Sales_Items_Abstract
{
    protected function _beforeToHtml()
    {
        if (!$this->getParentBlock()) {
            Mage::throwException(Mage::helper('adminhtml')->__('Invalid parent block for this block'));
        }
        $this->setOrder($this->getParentBlock()->getOrder());
        parent::_beforeToHtml();
    }

    public function getRetailerName($id)
    {
        $retailer = Mage::getSingleton('magedoc/retailer')->load($id);
        return $retailer->getName();
    }
    
    public function getInquiryHtml(Varien_Object $inquiry)
    {
        return $this->getItemRenderer('inquiry_default')
            ->setInquiry($inquiry)
            ->setCanEditQty($this->canEditQty())
            ->toHtml();
    } 

}
