<?php

class Testimonial_MageDoc_Model_Quote_Vehicle extends Mage_Core_Model_Abstract
{
    protected $_quote = null;
    protected $_inquiries = null;

    protected function _construct()
    {
        $this->_init('magedoc/quote_vehicle');
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();
        if ($this->getQuote()) {
            $this->setQuoteId($this->getQuote()->getId());
        }
        return $this;
    }

    public function setQuote(Mage_Sales_Model_Quote $quote)
    {
        $this->_quote = $quote;
        $this->setQuoteId($quote->getId());
        return $this;
    }

    public function getQuote()
    {
        return $this->_quote;
    }

    public function setVehicleData($vehicle)
    {
        $this->setData('vehicle', $vehicle);
        $this->setManufacturer($vehicle->getManufacturer());
        $this->setProductionStartYear($vehicle->getProductionStartYear());
        $this->setModel($vehicle->getModel());
        $this->setType($vehicle->getType());
        $this->setManufacturerId($vehicle->getManufacturerId());
        $this->setModelId($vehicle->getModelId());
        $this->setTypeId($vehicle->getTypeId());        
        return $this;
    }

    protected function _afterSave() {
        parent::_afterSave();
        $this->getInquiriesCollection()->save();
    }
    
    public function getInquiriesCollection()
    {
        if (is_null($this->_inquiries)) {
            $this->_inquiries = Mage::getResourceModel('magedoc/quote_inquiry_collection')
                ->setVehicle($this);
            if ($this->getQuote()){
                $this->_inquiries->setQuote($this->getQuote());
            }
        }
        return $this->_inquiries;
    }

    /**
     * Get all available vehicle inquiries
     *
     * @return array
     */
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
    
    public function addInquiry(Varien_Object $inquiry, $qty=null)
    {
        if ($inquiry instanceof Testimonial_MageDoc_Model_Order_Inquiry) {
            $oldInquiryData = $inquiry->getData();
            $inquiry = Mage::getModel('magedoc/quote_inquiry')
                    ->setData($inquiry->getData())
                    ->unsInquiryId()
                    ->setRowTotalWithDiscount($inquiry->getRowTotal());
            if ($inquiry->getParentItemId()) {
                return $this;
            }
            if ($inquiry->getReordered()){
                $inquiry->unsSupplyStatus();
                $inquiry->unsQtyReserved();
                $inquiry->unsQtySupplied();
                $inquiry->unsSupplyDate();
            }
            $inquiry->setVehicle($this)
                    ->setQuote($this->getQuote());
            $this->getInquiriesCollection()->addItem($inquiry);
            //$this->getQuote()->getInquiriesCollection()->addItem($inquiry);

            if ($inquiry->getHasChildren()) {
                foreach ($inquiry->getChildren() as $child) {
                    $child->setVehicle($this)
                            ->setQuote($this->getQuote())
                            ->setParentItem($inquiry);
                    $this->getInquiriesCollection()->addItem($child);
                }
            }
        }
        else {
            $inquiry->setVehicle($this)
                    ->setQuote($this->getQuote());
            if (!$inquiry->getId()) {
                $this->getInquiriesCollection()->addItem($inquiry);
                //$this->getQuote()->getInquiriesCollection()->addItem($inquiry);
            }
        }

        if ($qty) {
            $inquiry->setQty($qty);
        }
        return $this;
    }

    public function exportCustomerVehicle()
    {
        $vehicle = Mage::getModel('magedoc/customer_vehicle');
        Mage::helper('core')->copyFieldset('magedoc_convert_quote_vehicle', 'to_customer_vehicle', $this, $vehicle);
        return $vehicle;
    }
}
