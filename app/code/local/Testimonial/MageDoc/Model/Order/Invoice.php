<?php

class Testimonial_MageDoc_Model_Order_Invoice extends Mage_Sales_Model_Order_Invoice
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
            $this->_inquiries = Mage::getResourceModel('magedoc/order_invoice_inquiry_collection')
                ->setInvoiceFilter($this);

            if ($this->getId()) {
                foreach ($this->_inquiries as $item) {
                    $item->setInvoice($this);
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

    public function addInquiry(Testimonial_MageDoc_Model_Order_Invoice_Inquiry $inquiry)
    {
        $inquiry->setInvoice($this)
            ->setParentId($this->getId())
            ->setStoreId($this->getStoreId());

        if (!$inquiry->getId()) {
            $this->getInquiriesCollection()->addItem($inquiry);
        }
        return $this;
    }

    /**
     * Checking if the invoice is last
     *
     * @return bool
     */
    public function isLast()
    {
        foreach ($this->getAllItemsAndInquiries() as $item) {
            if (!$item->isLast()) {
                return false;
            }
        }
        return true;
    }

    public function register()
    {
        if ($this->getId()) {
            Mage::throwException(Mage::helper('sales')->__('Cannot register existing invoice'));
        }

        foreach ($this->getAllInquiries() as $inquiry) {
            if ($inquiry->getQty()>0) {
                $inquiry->register();
            }
            else {
                $inquiry->isDeleted(true);
            }
        }

        return parent::register();
    }

    public function cancel()
    {
        foreach ($this->getAllInquiries() as $inquiry) {
            $inquiry->cancel();
        }

        return parent::cancel();
    }

    protected function _afterSave()
    {

        if (null !== $this->_inquiries) {
            foreach ($this->_inquiries as $inquiry) {
                $inquiry->setOrderInquiry($inquiry->getOrderInquiry());
                $inquiry->save();
            }
        }

        return parent::_afterSave();
    }

    public function getAllItemsAndInquiries()
    {
        return array_merge(parent::getAllItems(), $this->getAllInquiries());
    }

}
