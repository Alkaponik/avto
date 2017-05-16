<?php

class Testimonial_MageDoc_Model_Service_Order extends Mage_Sales_Model_Service_Order
{
    protected $_retailerIds = array();
    
    public function __construct(Mage_Sales_Model_Order $order)
    {
        $this->_order       = $order;
        $this->_convertor   = Mage::getModel('magedoc/convert_order');
    }   
    
    public function setRetailerIds($ids)
    {
        if(is_array($ids)){
            $this->_retailerIds = $ids;
        }
        return $this;
    }
    
    public function prepareInvoice($itemQtys = array(), $inquiryQtys = array())
    {
        
        $invoice = $this->_convertor->toInvoice($this->_order);   
        $totalQty = 0;
        foreach ($this->_order->getAllItems() as $orderItem) {
            if (!$this->_canInvoiceItem($orderItem, array())) {
                continue;
            }
            $item = $this->_convertor->itemToInvoiceItem($orderItem);
            if ($orderItem->isDummy()) {
                $qty = $orderItem->getQtyOrdered() ? $orderItem->getQtyOrdered() : 1;
            } else {
                if (isset($itemQtys[$orderItem->getId()])) {
                    $qty = (float) $itemQtys[$orderItem->getId()];
                } elseif (!count($itemQtys)) {
                    $qty = $orderItem->getQtyToInvoice();
                } else {
                    continue;
                }
            }
            $totalQty += $qty;
            $item->setQty($qty);
            $invoice->addItem($item);
        }
                
        foreach ($this->_order->getAllInquiries() as $orderInquiry) {
            if(!$this->_canInvoiceInquiry($orderInquiry, array())){
                continue;
            }
            $inquiry = $this->_convertor->inquiryToInvoiceInquiry($orderInquiry);
            if ($orderInquiry->isDummy()) {
                $qty = $orderInquiry->getQtyOrdered() ? $orderInquiry->getQtyOrdered() : 1;
            } else {
                if (isset($inquiryQtys[$orderInquiry->getId()])) {     
                    $qty = (float) $inquiryQtys[$orderInquiry->getId()];
                } elseif (!count($inquiryQtys)) {
                    $qty = $orderInquiry->getQtyToInvoice();
                } else {
                    continue;
                }
            }
            if(isset($this->_retailerIds[$orderInquiry->getId()])){
                $retailer = Mage::helper('magedoc/price')->getRetailerById($this->_retailerIds[$orderInquiry->getId()]);
                $orderInquiry->setRetailerId($this->_retailerIds[$orderInquiry->getId()]);
                $orderInquiry->setRetailer($retailer->getName());
                $inquiry->setRetailer($retailer->getName());
            }
            $totalQty += $qty;
            $inquiry->setQty($qty);
            $invoice->addInquiry($inquiry);
        }
        
        $invoice->setTotalQty($totalQty);
        $invoice->collectTotals();
        $this->_order->getInvoiceCollection()->addItem($invoice);
        return $invoice;
    }

    public function prepareInquiriesShipment($itemQtys = array(), $inquiryQtys = array())
    {
        $shipment = $this->prepareShipment($itemQtys);
        $totalQty = $shipment->getTotalQty();

        foreach ($this->_order->getAllInquiries() as $orderInquiry) {
            if(!$this->_canShipInquiry($orderInquiry, array())){
                continue;
            }
            $inquiry = $this->_convertor->inquiryToShipmentInquiry($orderInquiry);

            if ($orderInquiry->isDummy(true)) {
                    $qty = 1;
            } else {
                if (isset($inquiryQtys[$orderInquiry->getId()])) {
                    $qty = min($inquiryQtys[$orderInquiry->getId()], $orderInquiry->getQtyToShip());
                } elseif (!count($inquiryQtys)) {
                    $qty = $orderInquiry->getQtyToShip();
                } else {
                    continue;
                }
            }            
            $totalQty += $qty;
            $inquiry->setQty($qty);
            $shipment->addInquiry($inquiry);
        }
        
        $shipment->setTotalQty($totalQty);
        return $shipment;
    }

    protected function _canInvoiceInquiry($inquiry, $qtys=array())
    {
        if ($inquiry->getLockedDoInvoice()) {
            return false;
        }
        if ($inquiry->isDummy()) {
            if ($inquiry->getHasChildren()) {
                foreach ($inquiry->getChildrenItems() as $child) {
                    if (empty($qtys)) {
                        if ($child->getQtyToInvoice() > 0) {
                            return true;
                        }
                    } else {
                        if (isset($qtys[$child->getId()]) && $qtys[$child->getId()] > 0) {
                            return true;
                        }
                    }
                }
                return false;
            } else if($inquiry->getParentInquiry()) {
                $parent = $inquiry->getParentInquiry();
                if (empty($qtys)) {
                    return $parent->getQtyToInvoice() > 0;
                } else {
                    return isset($qtys[$parent->getId()]) && $qtys[$parent->getId()] > 0;
                }
            }
        } else {
            return $inquiry->getQtyToInvoice() > 0;
        }
    }

    protected function _canShipInquiry($inquiry, $qtys=array())
    {
        if ($inquiry->getLockedDoShip()) {
            return false;
        }
        if ($inquiry->isDummy(true)) {
            if ($inquiry->getHasChildren()) {
                if ($inquiry->isShipSeparately()) {
                    return true;
                }
                foreach ($inquiry->getChildrenItems() as $child) {
                    if ($child->getIsVirtual()) {
                        continue;
                    }
                    if (empty($qtys)) {
                        if ($child->getQtyToShip() > 0) {
                            return true;
                        }
                    } else {
                        if (isset($qtys[$child->getId()]) && $qtys[$child->getId()] > 0) {
                            return true;
                        }
                    }
                }
                return false;
            } else if($inquiry->getParentInquiry()) {
                $parent = $inquiry->getParentInquiry();
                if (empty($qtys)) {
                    return $parent->getQtyToShip() > 0;
                } else {
                    return isset($qtys[$parent->getId()]) && $qtys[$parent->getId()] > 0;
                }
            }
        } else {
            return $inquiry->getQtyToShip()>0;
        }
    }

    /**
     * Prepare order creditmemo based on order items and requested params
     *
     * @param array $data
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    public function prepareInquiriesCreditmemo($data = array())
    {
        $creditmemo = $this->prepareCreditmemo($data);
        $totalQty = $creditmemo->getTotalQty();
        $qtys = isset($data['inquiry_qtys']) ? $data['inquiry_qtys'] : array();

        foreach ($this->_order->getAllInquiries() as $orderInquiry) {
            if (!$this->_canRefundInquiry($orderInquiry, $qtys)) {
                continue;
            }

            $inquiry = $this->_convertor->inquiryToCreditmemoInquiry($orderInquiry);
            if ($orderInquiry->isDummy()) {
                $qty = 1;
                $orderInquiry->setLockedDoShip(true);
            } else {
                if (isset($qtys[$orderInquiry->getId()])) {
                    $qty = (float) $qtys[$orderInquiry->getId()];
                } elseif (!count($qtys)) {
                    $qty = $orderInquiry->getQtyToRefund();
                } else {
                    continue;
                }
            }
            $totalQty += $qty;
            $inquiry->setQty($qty);
            $creditmemo->addInquiry($inquiry);
        }
        $creditmemo->setTotalQty($totalQty);
        $creditmemo->setGrandTotal(0);
        $creditmemo->setBaseGrandTotal(0);

        $creditmemo->collectTotals();
        return $creditmemo;
    }

    /**
     * Prepare order creditmemo based on invoice items and requested requested params
     *
     * @param array $data
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    public function prepareInquiriesInvoiceCreditmemo($invoice, $data = array())
    {
        $creditmemo = $this->prepareInvoiceCreditmemo($invoice, $data);

        $totalQty = $creditmemo->getTotalQty();
        $qtys = isset($data['inquiry_qtys']) ? $data['inquiry_qtys'] : array();

        $invoiceQtysRefunded = array();
        foreach($invoice->getOrder()->getCreditmemosCollection() as $createdCreditmemo) {
            if ($createdCreditmemo->getState() != Mage_Sales_Model_Order_Creditmemo::STATE_CANCELED
                && $createdCreditmemo->getInvoiceId() == $invoice->getId()) {
                foreach($createdCreditmemo->getAllInquiries() as $createdCreditmemoInquiry) {
                    $orderInquiryId = $createdCreditmemoInquiry->getOrderInquiry()->getId();
                    if (isset($invoiceQtysRefunded[$orderInquiryId])) {
                        $invoiceQtysRefunded[$orderInquiryId] += $createdCreditmemoInquiry->getQty();
                    } else {
                        $invoiceQtysRefunded[$orderInquiryId] = $createdCreditmemoInquiry->getQty();
                    }
                }
            }
        }

        $invoiceQtysRefundLimits = array();
        foreach($invoice->getAllInquiries() as $invoiceInquiry) {
            $invoiceQtyCanBeRefunded = $invoiceInquiry->getQty();
            $orderInquiryId = $invoiceInquiry->getOrderInquiry()->getId();
            if (isset($invoiceQtysRefunded[$orderInquiryId])) {
                $invoiceQtyCanBeRefunded = $invoiceQtyCanBeRefunded - $invoiceQtysRefunded[$orderInquiryId];
            }
            $invoiceQtysRefundLimits[$orderInquiryId] = $invoiceQtyCanBeRefunded;
        }


        foreach ($invoice->getAllInquiries() as $invoiceInquiry) {
            $orderInquiry = $invoiceInquiry->getOrderInquiry();

            if (!$this->_canRefundInquiry($orderInquiry, $qtys, $invoiceQtysRefundLimits)) {
                continue;
            }

            $item = $this->_convertor->inquiryToCreditmemoInquiry($orderInquiry);
            if ($orderInquiry->isDummy()) {
                $qty = 1;
            } else {
                if (isset($qtys[$orderInquiry->getId()])) {
                    $qty = (float) $qtys[$orderInquiry->getId()];
                } elseif (!count($qtys)) {
                    $qty = $orderInquiry->getQtyToRefund();
                } else {
                    continue;
                }
                if (isset($invoiceQtysRefundLimits[$orderInquiry->getId()])) {
                    $qty = min($qty, $invoiceQtysRefundLimits[$orderInquiry->getId()]);
                }
            }
            $qty = min($qty, $invoiceInquiry->getQty());
            $totalQty += $qty;
            $item->setQty($qty);
            $creditmemo->addInquiry($item);
        }
        $creditmemo->setTotalQty($totalQty);
        $creditmemo->setGrandTotal(0);
        $creditmemo->setBaseGrandTotal(0);

        $creditmemo->collectTotals();
        return $creditmemo;
    }

    /**
     * Check if order item can be refunded
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @param array $qtys
     * @param array $invoiceQtysRefundLimits
     * @return bool
     */
    protected function _canRefundInquiry($inquiry, $qtys=array(), $invoiceQtysRefundLimits=array())
    {
        if ($inquiry->isDummy()) {
            if ($inquiry->getHasChildren()) {
                foreach ($inquiry->getChildrenItems() as $child) {
                    if (empty($qtys)) {
                        if ($this->_canRefundNoDummyInquiry($child, $invoiceQtysRefundLimits)) {
                            return true;
                        }
                    } else {
                        if (isset($qtys[$child->getId()]) && $qtys[$child->getId()] > 0) {
                            return true;
                        }
                    }
                }
                return false;
            } else if($inquiry->getParentItem()) {
                $parent = $inquiry->getParentItem();
                if (empty($qtys)) {
                    return $this->_canRefundNoDummyInquiry($parent, $invoiceQtysRefundLimits);
                } else {
                    return isset($qtys[$parent->getId()]) && $qtys[$parent->getId()] > 0;
                }
            }
        } else {
            return $this->_canRefundNoDummyInquiry($inquiry, $invoiceQtysRefundLimits);
        }
    }

    /**
     * Check if no dummy order item can be refunded
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @param array $invoiceQtysRefundLimits
     * @return bool
     */
    protected function _canRefundNoDummyInquiry($inquiry, $invoiceQtysRefundLimits=array())
    {
        if ($inquiry->getQtyToRefund() < 0) {
            return false;
        }

        if (isset($invoiceQtysRefundLimits[$inquiry->getId()])) {
            return $invoiceQtysRefundLimits[$inquiry->getId()] > 0;
        }

        return true;
    }

}
