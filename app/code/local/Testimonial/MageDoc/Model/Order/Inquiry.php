<?php

class Testimonial_MageDoc_Model_Order_Inquiry 
        extends Mage_Sales_Model_Order_Item 
{
    protected $_vehicle;
    protected $_parentInquiry = null;
    protected $_eventPrefix = 'magedoc_order_inquiry';
    protected $_eventObject = 'inquiry';
    
    protected function _construct()
    {        
        $this->_init('magedoc/order_inquiry');
    } 
    
    public function setVehicle(Testimonial_MageDoc_Model_Order_Vehicle $vehicle)
    {
        $this->_vehicle = $vehicle;
        return $this;
    }


    public function getVehicle()
    {
        if (!isset($this->_vehicle)) {
            $this->_vehicle = Mage::getModel('magedoc/order_vehicle');
        }
        return $this->_vehicle;
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();

        if ($this->getVehicle()->getId() !== null) {
            if($this->getVehicle()->getOrderId() !== null){
                $this->setOrderId($this->getVehicle()->getOrderId());
            }
            $this->setVehicleId($this->getVehicle()->getId());
            if ($this->getParentInquiry()) {
                $this->setParentInquiryId($this->getParentInquiry()->getId());
            }

        }

        return $this;
    }
    
    
    public function isDummy($shipment = false){
        if ($shipment) {
            if ($this->getHasChildren() && $this->isShipSeparately()) {
                return true;
            }

            if ($this->getHasChildren() && !$this->isShipSeparately()) {
                return false;
            }

            if ($this->getParentInuqiry() && $this->isShipSeparately()) {
                return false;
            }

            if ($this->getParentInuqiry() && !$this->isShipSeparately()) {
                return true;
            }
        } else {
            if ($this->getHasChildren() && $this->isChildrenCalculated()) {
                return true;
            }

            if ($this->getHasChildren() && !$this->isChildrenCalculated()) {
                return false;
            }

            if ($this->getParentInuqiry() && $this->isChildrenCalculated()) {
                return false;
            }

            if ($this->getParentInuqiry() && !$this->isChildrenCalculated()) {
                return true;
            }
        }
        return false;
    }

    public function setParentInquiry($inquiry)
    {
        if ($inquiry) {
            $this->_parentInquiry = $inquiry;
            $inquiry->setHasChildren(true);
            $inquiry->addChildInquiry($this);
        }
        return $this;
    }

    public function getParentInquiry()
    {
        return $this->_parentInquiry;
    }

    
    public function addChildInquiry($inquiry)
    {
        if ($inquiry instanceof Testimonial_MageDoc_Model_Order_Inquiry) {
            $this->_children[] = $inquiry;
        } else if (is_array($inquiry)) {
            $this->_children = array_merge($this->_children, $inquiry);
        }
    }

    public function getName()
    {
        $name = $this->getData('name');
        if ($this->getArticleId()){
            if ($this->getSupplier() && strpos($name, $this->getSupplier()) === false){
                $name .= ' ' . $this->getSupplier();
            }
            if ($this->getCode() && strpos($name, $this->getCode()) === false){
                $name .= ' ' . $this->getCode();
            }
        }
        return $name;
    }
}

