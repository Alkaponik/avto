<?php

class Testimonial_MageDoc_Model_Quote_Address_Total_Inquiry extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    public function __construct()
    {
        $this->setCode('inquiry_subtotal');
    }

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        $quote = $address->getQuote();
        if($address->getAddressType() != Mage_Sales_Model_Quote_Address::TYPE_SHIPPING 
                || !($quote instanceof Testimonial_MageDoc_Model_Quote)
        ){
            return $this;
        }
//        $inquiries = $quote->getAllInquiries();
        foreach($address->getQuote()->getVehiclesCollection() as $vehicle){
            /** Workaround to force vehicle save */
            $vehicle->setCalculateTotals(0);
            foreach($vehicle->getInquiriesCollection() as $inquiry){
                $this->_initInquiry($address, $inquiry);
            }
            $vehicle->setCalculateTotals(1);
        }        
        return $this;
    }

    protected function _initInquiry($address, $inquiry)
    {
        $inquiry->setBasePrice($inquiry->getBaseCalculationPrice());
        $inquiry->setBaseOriginalPrice($inquiry->getPrice());
        $inquiry->calcRowTotal();
        $this->_addSubtotalAmount($address, $inquiry);
        return true;
    }

    /**
     * Add row total item amount to subtotal
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @return  Mage_Tax_Model_Sales_Total_Quote_Subtotal
     */
    protected function _addSubtotalAmount(Mage_Sales_Model_Quote_Address $address, $item)
    {
        $address->setTotalAmount('subtotal', $address->getTotalAmount('subtotal')+$item->getRowTotal());
        $address->setBaseTotalAmount('subtotal', $address->getBaseTotalAmount('subtotal')+$item->getBaseRowTotal());
        return $this;
    }
}
