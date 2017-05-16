<?php

class Testimonial_MageDoc_Block_Adminhtml_Order_View_Tab_Info
    extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Info
{
    public function getShippingDate()
    {
        $date = $this->getOrder()->getShippingDate();
        return Mage::app()->getLocale()->date($date)->toString(
            Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        );
    }

    public function getShippingDateFormat()
    {
        return Varien_Date::convertZendToStrFtime(Mage::helper('magedoc')->getShippingDateFormat(), true, true);
    }

    public function getSaveShippingDateUrl()
    {
        return $this->getUrl('*/*/saveShippingDate', array('order_id'=>$this->getOrder()->getId()));
    }
}
