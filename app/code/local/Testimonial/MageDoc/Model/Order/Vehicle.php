<?php

class Testimonial_MageDoc_Model_Order_Vehicle extends Mage_Core_Model_Abstract 
{
    protected $_order;
    protected $_inquiries = null;
    
    protected function _construct()
    {
        $this->_init('magedoc/order_vehicle');
    } 
    
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
        return $this;
    }


    public function getOrder()
    {
        if (!isset($this->_order)) {
            $this->_order = Mage::getModel('sales/order');
        }
        return $this->_order;
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();

        if ($this->getOrder()->getId() !== null) {
            $this->setOrderId($this->getOrder()->getId());
        }

        return $this;
    }
       
    public function getInquiriesCollection()
    {
        if (is_null($this->_inquiries)) {
            $this->_inquiries = Mage::getResourceModel('magedoc/order_inquiry_collection');
        }
        $this->_inquiries->setVehicleFilter($this);
        if ($this->getId()) {
            foreach ($this->_inquiries as $inquiry) {                
                $inquiry->setVehicle($this);
            }
        }
        
        return $this->_inquiries;
    }

    
    
    public function addInquiry(Testimonial_MageDoc_Model_Order_Inquiry $inquiry)
    {
        if($this->getId() !== null){
            $inquiry->setVehicle($this);
        }
        if($inquiry->getId() === null){
            $this->getInquiriesCollection()->addItem($inquiry);
        }
        return $this;
    }
    
    public function getAllInquiries()
    {
        $inquiries = array();
        foreach ($this->getInquiriesCollection() as $inquiry) {
            if (!$inquiry->isDeleted()) {
                $inquiries[] =  $inquiry;
            }
        }
        return $inquiries;
    }

    
    
    protected function _afterSave()
    {
        $this->getInquiriesCollection()->save();
        return parent::_afterSave();
    }

    protected function _afterDelete()
    {
        foreach ($this->getInquiriesCollection() as $inquiry){
            $inquiry->delete();
        }
    }
}

