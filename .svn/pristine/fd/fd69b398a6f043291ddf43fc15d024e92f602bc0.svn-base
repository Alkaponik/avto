<?php

class Testimonial_MageDoc_Block_Adminhtml_Order_Create_Shipping_Method_Form
    extends Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Method_Form
{
    public function getShippingDate()
    {
        $shippingDate = $this->getQuote()->getShippingDate();
        $supplyDate = $this->getQuote()->getSupplyDate();
        $nowDateModel = Mage::app()->getLocale()->date();

        if($shippingDate || $supplyDate){
            $shippingDateModel = Mage::app()->getLocale()->date($shippingDate);
            $supplyDateModel = Mage::app()->getLocale()->date($supplyDate);

            $dateModel = $shippingDateModel->getTimestamp() >= $supplyDateModel->getTimestamp()
                ? $shippingDateModel
                : $supplyDateModel;

            $dateModel = $dateModel->getTimestamp() >= $nowDateModel->getTimestamp()? $dateModel : $nowDateModel;
        }else{
            $dateModel = $nowDateModel;
        }


        $shippingDateStr = $dateModel->toString(
            Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        );

        $this->getQuote()->setGmtShippingDate($shippingDateStr);

        return $shippingDateStr;
    }

    public function getShippingDateFormat()
    {
        $outputFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        return Varien_Date::convertZendToStrFtime($outputFormat, true, true);
    }
}
