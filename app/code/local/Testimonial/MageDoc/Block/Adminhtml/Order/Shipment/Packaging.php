<?php

class Testimonial_MageDoc_Block_Adminhtml_Order_Shipment_Packaging
        extends Mage_Adminhtml_Block_Sales_Order_Shipment_Packaging
{
    /**
     * Configuration for popup window for packaging
     *
     * @return string
     */
    public function getConfigDataJson()
    {
        $shipmentId = $this->getShipment()->getId();
        $orderId = $this->getRequest()->getParam('order_id');
        $urlParams = array();

        $itemsQty       = array();
        $itemsPrice     = array();
        $itemsName      = array();
        $itemsWeight    = array();
        $itemsProductId = array();

        if ($shipmentId) {
            $urlParams['shipment_id'] = $shipmentId;
            $createLabelUrl = $this->getUrl('*/sales_order_shipment/createLabel', $urlParams);
            $itemsGridUrl = $this->getUrl('magedoc/sales_order_shipment/getShippingItemsGrid', $urlParams);
            foreach ($this->getShipment()->getAllItemsAndInquiries() as $item) {
                $itemsQty[$item->getId()]           = $item->getQty();
                $itemsPrice[$item->getId()]         = $item->getPrice();
                $itemsName[$item->getId()]          = $item->getName();
                $itemsWeight[$item->getId()]        = $item->getWeight();
                $itemsProductId[$item->getId()]     = $item->getProductId();
                $itemsOrderItemId[$item->getId()]   = $item->getOrderItemId();
            }
        } else if ($orderId) {
            $urlParams['order_id'] = $orderId;
            $createLabelUrl = $this->getUrl('*/sales_order_shipment/save', $urlParams);
            $itemsGridUrl = $this->getUrl('*/sales_order_shipment/getShippingItemsGrid', $urlParams);

            foreach ($this->getShipment()->getAllItemsAndInquiries() as $item) {
                $itemsQty[$item->getOrderItemId()]          = $item->getQty()*1;
                $itemsPrice[$item->getOrderItemId()]        = $item->getPrice();
                $itemsName[$item->getOrderItemId()]         = $item->getName();
                $itemsWeight[$item->getOrderItemId()]       = $item->getWeight();
                $itemsProductId[$item->getOrderItemId()]    = $item->getProductId();
                $itemsOrderItemId[$item->getOrderItemId()]  = $item->getOrderItemId();
            }
        }
        $data = array(
            'createLabelUrl'            => $createLabelUrl,
            'itemsGridUrl'              => $itemsGridUrl,
            'errorQtyOverLimit'         => Mage::helper('sales')->__('The quantity you want to add exceeds the total shipped quantity for some of selected Product(s)'),
            'titleDisabledSaveBtn'      => Mage::helper('sales')->__('Products should be added to package(s)'),
            'validationErrorMsg'        => Mage::helper('sales')->__('The value that you entered is not valid.'),
            'shipmentItemsQty'          => $itemsQty,
            'shipmentItemsPrice'        => $itemsPrice,
            'shipmentItemsName'         => $itemsName,
            'shipmentItemsWeight'       => $itemsWeight,
            'shipmentItemsProductId'    => $itemsProductId,
            'shipmentItemsOrderItemId'  => $itemsOrderItemId,
            'customizable'              => $this->_getCustomizableContainers(),
        );
        return Mage::helper('core')->jsonEncode($data);
    }
}