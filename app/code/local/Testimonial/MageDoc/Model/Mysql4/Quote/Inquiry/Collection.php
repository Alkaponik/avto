<?php

class Testimonial_MageDoc_Model_Mysql4_Quote_Inquiry_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected $_quote = null;
    protected $_vehicle = null;

    protected function _construct()
    {
        $this->_init('magedoc/quote_inquiry');
    }

    public function getStoreId()
    {
        return (int)$this->_quote->getStoreId();
    }

    public function setQuote($quote)
    {
        $this->_quote = $quote;
        $quoteId      = $quote->getId();
        if ($quoteId) {
            $this->addFieldToFilter('quote_id', $quote->getId());
        } else {
            $this->_totalRecords = 0;
            $this->_setIsLoaded(true);
        }
        return $this;
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        foreach ($this as $item) {
            if (!is_null($this->_quote)) {
                $item->setQuote($this->_quote);
            }
            if (!is_null($this->_vehicle)){
                $item->setVehicle($this->_vehicle);
            }
        }

        return $this;
    }
    
    public function setVehicle($vehicle)
    {
        $this->_vehicle = $vehicle;
        $vehicleId      = $vehicle->getId();
        if ($vehicleId) {
            $this->addFieldToFilter('quote_vehicle_id', $vehicleId);
        } else {
            $this->_totalRecords = 0;
            $this->_setIsLoaded(true);
        }
        return $this;
    }

}

