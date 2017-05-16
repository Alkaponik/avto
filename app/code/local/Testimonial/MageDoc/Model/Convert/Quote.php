<?php

class Testimonial_MageDoc_Model_Convert_Quote extends Mage_Sales_Model_Convert_Quote
{

    public function toOrder(Mage_Sales_Model_Quote $quote, $order=null)
    {
        if (!($order instanceof Mage_Sales_Model_Order)) {
            $order = Mage::getModel('magedoc/order');
        }
        /* @var $order Mage_Sales_Model_Order */

        $order->setIncrementId($quote->getReservedOrderId())
            ->setStoreId($quote->getStoreId())
            ->setQuoteId($quote->getId())
            ->setQuote($quote)
            ->setCustomer($quote->getCustomer());

        Mage::helper('core')->copyFieldset('sales_convert_quote', 'to_order', $quote, $order);
        Mage::dispatchEvent('sales_convert_quote_to_order', array('order'=>$order, 'quote'=>$quote));
        return $order;
    }

    
    public function vehicleToOrderVehicle(Testimonial_MageDoc_Model_Quote_Vehicle $vehicle)
    {
        $orderVehicle = Mage::getModel('magedoc/order_vehicle')
            ->setStoreId($vehicle->getStoreId());

        Mage::helper('core')->copyFieldset('magedoc_convert_quote_vehicle', 'to_order_vehicle', $vehicle, $orderVehicle);


        Mage::dispatchEvent('magedoc_convert_quote_vehicle_to_order_vehicle',
            array('order_vehicle' => $orderVehicle, 'vehicle' => $vehicle)
        );
        $vehicle->setOrderVehicle($orderVehicle);
        return $orderVehicle;
    }
    
    public function inquiryToOrderInquiry(Testimonial_MageDoc_Model_Quote_Inquiry $inquiry)
    {
        $orderInquiry = Mage::getModel('magedoc/order_inquiry')
            ->setStoreId($inquiry->getStoreId())
            ->setQuoteInquiryId($inquiry->getId());

        Mage::helper('core')->copyFieldset('magedoc_convert_quote_inquiry', 'to_order_inquiry', $inquiry, $orderInquiry);


        Mage::dispatchEvent('magedoc_convert_quote_inquiry_to_order_inquiry',
            array('order_inquiry' => $orderInquiry, 'inquiry' => $inquiry)
        );
        return $orderInquiry;
    }

    
    
}
