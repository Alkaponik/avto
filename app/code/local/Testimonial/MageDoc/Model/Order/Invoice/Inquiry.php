<?php

class Testimonial_MageDoc_Model_Order_Invoice_Inquiry extends Mage_Sales_Model_Order_Invoice_Item
{
    protected $_orderInquiry = null;
    
    function _construct()
    {
        $this->_init('magedoc/order_invoice_inquiry');
    }

    public function setQty($qty)
    {
        if ($this->getOrderInquiry()->getIsQtyDecimal()) {
            $qty = (float) $qty;
        }
        else {
            $qty = (int) $qty;
        }
        $qty = $qty > 0 ? $qty : 0;
        /**
         * Check qty availability
         */
        $qtyToInvoice = sprintf("%F", $this->getOrderInquiry()->getQtyToInvoice());
        $qty = sprintf("%F", $qty);
        if ($qty <= $qtyToInvoice || $this->getOrderInquiry()->isDummy()) {
            $this->setData('qty', $qty);
        }
        else {
            Mage::throwException(
                Mage::helper('sales')->__('Invalid qty to invoice item "%s"', $this->getName())
            );
        }
        return $this;
    }
    
    public function isLast()
    {
        if ((string)(float)$this->getQty() == (string)(float)$this->getOrderInquiry()->getQtyToInvoice()) {
            return true;
        }
        return false;
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
            if ($this->getInvoice()) {
                $this->_orderInquiry = $this->getInvoice()->getOrder()->getInquiryById($this->getOrderInquiryId());
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
        parent::_afterSave();
        return $this;
    }

    public function getOrderItem()
    {
        return $this->getOrderInquiry();
    }
 
}
