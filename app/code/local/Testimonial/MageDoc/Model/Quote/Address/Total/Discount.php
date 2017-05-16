<?php

class Testimonial_MageDoc_Model_Quote_Address_Total_Discount extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $quote = $address->getQuote();

        $items = $address->getAllInquiries();
        if (!count($items)) {
            return $this;
        }

        $totalDiscountAmount = 0;
        $subtotalWithDiscount = 0;
        $baseTotalDiscountAmount = 0;
        $baseSubtotalWithDiscount = 0;

        
        foreach($address->getQuote()->getVehiclesCollection() as $vehicle){
            foreach($vehicle->getInquiriesCollection() as $inquiry){
                $totalDiscountAmount += $inquiry->getDiscountAmount();
                $baseTotalDiscountAmount += $inquiry->getBaseDiscountAmount();
                $inquiry->setRowTotalWithDiscount($inquiry->getRowTotal()-$inquiry->getDiscountAmount());
                $inquiry->setBaseRowTotalWithDiscount($inquiry->getBaseRowTotal()-$inquiry->getBaseDiscountAmount());
                $subtotalWithDiscount+=$inquiry->getRowTotalWithDiscount();
                $baseSubtotalWithDiscount+=$inquiry->getBaseRowTotalWithDiscount();
            }
        }
        $address->setDiscountAmount($totalDiscountAmount + $address->getDiscountAmount());
        $address->setSubtotalWithDiscount($subtotalWithDiscount + $address->getSubtotalWithDiscount());
        $address->setBaseDiscountAmount($baseTotalDiscountAmount + $address->getBaseDiscountAmount());
        $address->setBaseSubtotalWithDiscount($baseSubtotalWithDiscount + $address->getBaseSubtotalWithDiscount());

        $address->setGrandTotal($address->getGrandTotal() - $address->getDiscountAmount());
        $address->setBaseGrandTotal($address->getBaseGrandTotal()-$address->getBaseDiscountAmount());
        return $this;
    }
}
