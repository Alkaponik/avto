<?php

class Testimonial_MageDoc_Block_Adminhtml_Order_Shipment_Packaging_Grid
        extends Mage_Adminhtml_Block_Sales_Order_Shipment_Packaging_Grid
{
    /**
     * Return collection of shipment items
     *
     * @return array
     */
    public function getCollection()
    {
        if ($this->getShipment()->getId()) {
            $collection = Mage::getModel('sales/order_shipment_item')->getCollection()
                ->setShipmentFilter($this->getShipment()->getId());
        } else{
            $collection = $this->getShipment()->getAllItemsAndInquiries();
        }
        return $collection;
    }
}