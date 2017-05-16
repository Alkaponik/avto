<?php

class Testimonial_MageDoc_Model_Order_Creditmemo_Inquiry extends Mage_Sales_Model_Order_Creditmemo_Item
{
    protected $_orderInquiry = null;
    
    function _construct()
    {
        $this->_init('magedoc/order_creditmemo_inquiry');
    }

    public function setOrderInquiry(Testimonial_MageDoc_Model_Order_Inquiry $inquiry)
    {
        $this->_orderInquiry = $inquiry;
        $this->setOrderInquiryId($inquiry->getId());
        return $this;
    }

    public function getOrderInquiry()
    {
        if (is_null($this->_orderInquiry)) {
            if ($this->getCreditmemo()) {
                $this->_orderInquiry = $this->getCreditmemo()->getOrder()->getInquiryById($this->getOrderInquiryId());
            }
            else {
                $this->_orderInquiry = Mage::getModel('magedoc/order_inquiry')
                    ->load($this->getOrderInquiryId());
            }
        }

        return $this->_orderInquiry;
    }

    protected function _afterSave()
    {
        if ($this->_orderInquiry !== null) {
            $this->_orderInquiry->save();
        }

        return parent::_afterSave();
    }

    public function getOrderItem()
    {
        return $this->getOrderInquiry();
    }
 
}
