<?php

class Testimonial_MageDoc_Model_Convert_Order extends Mage_Sales_Model_Convert_Order
{
    public function inquiryToInvoiceInquiry(Testimonial_MageDoc_Model_Order_Inquiry $inquiry)
    {
        $invoiceInquiry = Mage::getModel('magedoc/order_invoice_inquiry');
        $invoiceInquiry->setOrderInquiry($inquiry);
        Mage::helper('core')->copyFieldset('magedoc_convert_order_inquiry', 'to_invoice_inquiry', $inquiry, $invoiceInquiry);
        return $invoiceInquiry;
    }

    public function inquiryToShipmentInquiry(Testimonial_MageDoc_Model_Order_Inquiry $inquiry)
    {
        $shipmentInquiry = Mage::getModel('magedoc/order_shipment_inquiry');
        $shipmentInquiry->setOrderInquiry($inquiry);
        Mage::helper('core')->copyFieldset('magedoc_convert_order_inquiry', 'to_shipment_inquiry', $inquiry, $shipmentInquiry);
        return $shipmentInquiry;
    }

    public function toInvoice(Mage_Sales_Model_Order $order)
    {
        $invoice = Mage::getModel('magedoc/order_invoice');
        $invoice->setOrder($order)
            ->setStoreId($order->getStoreId())
            ->setCustomerId($order->getCustomerId())
            ->setBillingAddressId($order->getBillingAddressId())
            ->setShippingAddressId($order->getShippingAddressId());

        Mage::helper('core')->copyFieldset('sales_convert_order', 'to_invoice', $order, $invoice);
        return $invoice;
    }

    public function toShipment(Mage_Sales_Model_Order $order)
    {
        $shipment = Mage::getModel('magedoc/order_shipment');
        $shipment->setOrder($order)
            ->setStoreId($order->getStoreId())
            ->setCustomerId($order->getCustomerId())
            ->setBillingAddressId($order->getBillingAddressId())
            ->setShippingAddressId($order->getShippingAddressId());

        Mage::helper('core')->copyFieldset('sales_convert_order', 'to_shipment', $order, $shipment);
        return $shipment;
    }

    /**
     * Convert order object to creditmemo
     *
     * @param   Mage_Sales_Model_Order $order
     * @return  Testimonial_MageDoc_Model_Order_Creditmemo
     */
    public function toCreditmemo(Mage_Sales_Model_Order $order)
    {
        $creditmemo = Mage::getModel('magedoc/order_creditmemo');
        $creditmemo->setOrder($order)
            ->setStoreId($order->getStoreId())
            ->setCustomerId($order->getCustomerId())
            ->setBillingAddressId($order->getBillingAddressId())
            ->setShippingAddressId($order->getShippingAddressId());

        Mage::helper('core')->copyFieldset('sales_convert_order', 'to_cm', $order, $creditmemo);
        return $creditmemo;
    }

    /**
     * Convert order inquiry object to Creditmemo inquiry
     *
     * @param   Testimonial_MageDoc_Model_Order_Inquiry $inquiry
     * @return  Testimonial_MageDoc_Model_Order_Creditmemo_Inquiry
     */
    public function inquiryToCreditmemoInquiry(Testimonial_MageDoc_Model_Order_Inquiry $inquiry)
    {
        $creditmemoInquiry = Mage::getModel('magedoc/order_creditmemo_inquiry');
        $creditmemoInquiry->setOrderInquiry($inquiry)
            ->setProductId($inquiry->getProductId());

        Mage::helper('core')->copyFieldset('sales_convert_order_item', 'to_cm_item', $inquiry, $creditmemoInquiry);
        return $creditmemoInquiry;
    }

    /**
     * Retrieve
     *
     * @param Testimonial_MageDoc_Model_Order_Inquiry $inquiry
     * @return unknown
     */
    public function inquiryToQuoteInquiry(Testimonial_MageDoc_Model_Order_Inquiry $inquiry)
    {
        $quoteInquiry = Mage::getModel('magedoc/quote_inquiry')
            ->setStoreId($inquiry->getOrder()->getStoreId())
            ->setQuoteInquiryId($inquiry->getId())
            ->setProductId($inquiry->getProductId())
            ->setParentProductId($inquiry->getParentProductId());

        Mage::helper('core')->copyFieldset('magedoc_convert_order_inquiry', 'to_quote_inquiry', $inquiry, $quoteInquiry);
        return $quoteInquiry;
    }

    public function vehicleToQuoteVehicle(Testimonial_MageDoc_Model_Order_Vehicle $vehicle)
    {
        $quoteVehicle = Mage::getModel('magedoc/quote_vehicle')
            ->setStoreId($vehicle->getStoreId());

        Mage::helper('core')->copyFieldset('magedoc_convert_order_vehicle', 'to_quote_vehicle', $vehicle, $quoteVehicle);

        return $quoteVehicle;
    }
}
