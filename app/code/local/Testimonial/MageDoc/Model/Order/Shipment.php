<?php

class Testimonial_MageDoc_Model_Order_Shipment extends Mage_Sales_Model_Order_Shipment
{
    protected $_inquiries = null;
    
    public function getOrder()
    {
        if (!$this->_order instanceof Testimonial_MageDoc_Model_Order) {
            $this->_order = Mage::getModel('magedoc/order')->load($this->getOrderId());
        }
        return $this->_order->setHistoryEntityName(self::HISTORY_ENTITY_NAME);
    }
    
    public function getInquiriesCollection()
    {
        if (empty($this->_inquiries)) {
            $this->_inquiries = Mage::getResourceModel('magedoc/order_shipment_inquiry_collection')
                ->setShipmentFilter($this);

            if ($this->getId()) {
                foreach ($this->_inquiries as $inquiry) {
                    $inquiry->setShipment($this);
                }
            }
        }
        return $this->_inquiries;
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

    public function getInquiriesByVehicleId($vehicleId)
    {
        $inquiries = array();
        foreach($this->getAllInquiries() as $inquiry){
            if($inquiry->getOrderInquiry()->getVehicleId() == $vehicleId){
                $inquiries[] = $inquiry;
            }
        }
        return $inquiries;
    }

    public function addInquiry(Testimonial_MageDoc_Model_Order_Shipment_Inquiry $inquiry)
    {
        $inquiry->setShipment($this)
            ->setParentId($this->getId())
            ->setStoreId($this->getStoreId());

        if (!$inquiry->getId()) {
            $this->getInquiriesCollection()->addItem($inquiry);
        }
        return $this;
    }

    public function register()
    {
        parent::register();
        $totalQty = $this->getTotalQty();
        
        foreach ($this->getAllInquiries() as $inquiry) {
            if ($inquiry->getQty()>0) {
                $inquiry->register();
                $totalQty += $inquiry->getQty();
            }
            else {
                $inquiry->isDeleted(true);
            }
        }
        $this->setTotalQty($totalQty);
        return $this;
    }

    protected function _afterSave()
    {
        if (null !== $this->_inquiries) {
            foreach ($this->_inquiries as $inquiry) {
                $inquiry->save();
            }
        }
        return parent::_afterSave();
    }

    /**
     * Before object save
     *
     * @return Mage_Sales_Model_Order_Shipment
     */
    protected function _beforeSave()
    {
        if ((!$this->getId() || null !== $this->_items) && !count($this->getAllItemsAndInquiries())) {
            Mage::throwException(
                Mage::helper('sales')->__('Cannot create an empty shipment.')
            );
        }

        if (!$this->getOrderId() && $this->getOrder()) {
            $this->setOrderId($this->getOrder()->getId());
            $this->setShippingAddressId($this->getOrder()->getShippingAddress()->getId());
        }
        if ($this->getPackages()) {
            $this->setPackages(serialize($this->getPackages()));
        }
        return Mage_Sales_Model_Abstract::_beforeSave();
    }

    public function getAllItemsAndInquiries()
    {
        return array_merge(parent::getAllItems(), $this->getAllInquiries());
    }
}
