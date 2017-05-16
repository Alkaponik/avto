<?php

class Testimonial_MageDoc_Model_Quote_Address_Total_Tax_Subtotal extends Mage_Tax_Model_Sales_Total_Quote_Subtotal
{
    protected function _getAddressItems(Mage_Sales_Model_Quote_Address $address)
    {
        $quote = $address->getQuote();
        if (!$quote instanceof Testimonial_MageDoc_Model_Quote
            || $address->getAddressType() != Mage_Sales_Model_Quote_Address::TYPE_SHIPPING){
            return parent::_getAddressItems($address);
        }
        return array_merge(parent::_getAddressItems($address), $quote->getAllInquiries());
    }
}
