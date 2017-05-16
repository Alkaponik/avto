<?php

class Testimonial_MageDoc_Model_Mysql4_Order_Shipment_Inquiry_Collection 
        extends Mage_Sales_Model_Resource_Order_Shipment_Item_Collection
{

    protected function _construct()
    {
        $this->_init('magedoc/order_shipment_inquiry');
    }

    public function setShipmentFilter($shipment)
    {
        if ($shipment instanceof Testimonial_MageDoc_Model_Order_Shipment) {
            $shipmentId = $shipment->getId();
            if ($shipmentId) {
                $this->addFieldToFilter('parent_id', $shipmentId);
            }else{ 
                $this->_totalRecords = 0;
                $this->_setIsLoaded(true);
            }
        } else {
            $this->addFieldToFilter('parent_id', $shipment);
        }
        return $this;
    }
}
