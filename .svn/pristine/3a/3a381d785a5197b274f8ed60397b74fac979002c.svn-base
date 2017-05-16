<?php

class Testimonial_MageDoc_Model_Order_Shipment_Inquiry extends Mage_Sales_Model_Order_Shipment_Item
{
    protected $_orderInquiry = null;
    
    function _construct()
    {
        $this->_init('magedoc/order_shipment_inquiry');
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
        if ($qty <= $this->getOrderInquiry()->getQtyToShip() || $this->getOrderInquiry()->isDummy(true)) {
            $this->setData('qty', $qty);
        }
        else {
            Mage::throwException(
                Mage::helper('sales')->__('Invalid qty to ship for item "%s"', $this->getName())
            );
        }
        return $this;
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
            if ($this->getShipment()) {
                $this->_orderInquiry = $this->getShipment()->getOrder()->getInquiryById($this->getOrderInquiryId());
            }
            else {
                $this->_orderInquiry = Mage::getModel('magedoc/order_inquiry')
                    ->load($this->getOrderInquiryId());
            }
        }
        return $this->_orderInquiry;
    }

    
    public function register()
    {
        $this->getOrderInquiry()->setQtyShipped(
            $this->getOrderInquiry()->getQtyShipped()+$this->getQty()
        );
        return $this;
    }

    protected function _afterSave()
    {
        if (null ==! $this->_orderInquiry) {
            $this->_orderInquiry->save();
        }
        parent::_afterSave();
        return $this;
    }

    public function getOrderItem()
    {
        return $this->getOrderInquiry();
    }

    public function getOrderItemId()
    {
        return 'inquiry_'.$this->getOrderInquiryId();
    }
}
