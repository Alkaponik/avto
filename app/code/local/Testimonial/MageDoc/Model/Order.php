<?php

class Testimonial_MageDoc_Model_Order extends Mage_Sales_Model_Order
{
    const SUPPLY_STATUS_TYPES_PATH = 'global/order_supply_status/types';
    protected $_vehicles = null;
    protected $_manager;
    protected $_hasStatusChangeReason;

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('magedoc/order');
    }
       
    public function getVehiclesCollection()
    {
        if (is_null($this->_vehicles)) {
            $this->_vehicles = Mage::getResourceModel('magedoc/order_vehicle_collection');
        }
        $this->_vehicles->setOrderFilter($this);
        if ($this->getId()) {
            foreach ($this->_vehicles as $vehicle) {                
                $vehicle->setOrder($this);
            }
        }
        return $this->_vehicles;
    }

    public function getSupplyStatusLabel()
    {
        return Mage::helper('magedoc/supply')->getSupplyStatusLabel($this->getSupplyStatus());
    }

    public function addVehicle(Testimonial_MageDoc_Model_Order_Vehicle $vehicle)
    {
        if($this->getId() !== null){
            $vehicle->setOrder($this);
        }
        $this->getVehiclesCollection()->addItem($vehicle);

        return $this;
    }

    public function getAllInquiries()
    {
        $inquiries = array();
        foreach ($this->getAllVehicles() as $vehicle) {
            $inquiries = array_merge($inquiries, $vehicle->getAllInquiries());
        }
        return $inquiries;
    }

    public function getAllVehicles()
    {
        $vehicles = array();
        foreach ($this->getVehiclesCollection() as $vehicle) {

            if (!$vehicle->isDeleted()) {
                $vehicles[] = $vehicle;
            }
        }
        return $vehicles;
    }
    
    
    
    protected function _afterSave()
    {
        $this->getVehiclesCollection()->save();
        return parent::_afterSave();
    }

    
    public function isAllItemsReserved()
    {
        foreach($this->getAllItems() as $item){
            if ($item->getSupplyStatus() == Testimonial_MageDoc_Model_Source_SuppliedType::UNRESERVED) {
                return false;
            }            
        }
        foreach ($this->getAllInquiries() as $inquiry) {
            if ($inquiry->getSupplyStatus() == Testimonial_MageDoc_Model_Source_SuppliedType::UNRESERVED) {
                return false;
            }
        }
        return true;
    }

    public function isAllItemsShipped()
    {
        foreach($this->getAllItems() as $item){
            if ($item->getSupplyStatus() !== Testimonial_MageDoc_Model_Source_SuppliedType::SHIPPED) {
                return false;
            }            
        }
        foreach ($this->getAllInquiries() as $inquiry) {
            if ($inquiry->getSupplyStatus() !== Testimonial_MageDoc_Model_Source_SuppliedType::SHIPPED) {
                return false;
            }
        }
        return true;
    }

    public function isAllItemsAssembled()
    {
        foreach($this->getAllItems() as $item){
            if ($item->getQtySupplied() < $item->getQtyOrdered()) {
                return false;
            }            
        }
        foreach ($this->getAllInquiries() as $inquiry) {
            if ($inquiry->getQtySupplied() < $inquiry->getQtyOrdered()) {
                return false;
            }
        }
        return true;
    }

    public function hasAssembledItems()
    {
        foreach($this->getAllItemsAndInquiries() as $item){
            if ($item->getQtySupplied() > 0) {
                return true;
            }
        }
        return false;
    }

    public function isAllItemsQtyShipped()
    {
        foreach($this->getAllItems() as $item){
            if ($item->getQtyShipped() != $item->getQtyOrdered()) {
                return false;
            }            
        }
        foreach ($this->getAllInquiries() as $inquiry) {
            if ($inquiry->getQtyShipped() != $inquiry->getQtyOrdered()) {
                return false;
            }
        }
        return true;
    }

     /**
     * Retrieve order invoice availability
     *
     * @return bool
     */
    public function canInvoice()
    {
        if ($this->canUnhold() || $this->isPaymentReview()) {
            return false;
        }
        $state = $this->getState();
        if ($this->isCanceled() || $state === self::STATE_COMPLETE || $state === self::STATE_CLOSED) {
            return false;
        }

        if ($this->getActionFlag(self::ACTION_FLAG_INVOICE) === false) {
            return false;
        }

        foreach ($this->getAllItemsAndInquiries() as $item) {
            if ($item->getQtyToInvoice()>0 && !$item->getLockedDoInvoice()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve order shipment availability
     *
     * @return bool
     */
    public function canShip()
    {
        if ($this->canUnhold() || $this->isPaymentReview() || $this->canInvoice()) {
            return false;
        }

        if ($this->getIsVirtual() || $this->isCanceled()) {
            return false;
        }

        if ($this->getActionFlag(self::ACTION_FLAG_SHIP) === false) {
            return false;
        }

        foreach ($this->getAllItemsAndInquiries() as $item) {
            if ($item->getQtyToShip()>0 && !$item->getIsVirtual()
                && !$item->getLockedDoShip())
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Prepare order totals to cancellation
     * @param string $comment
     * @param bool $graceful
     * @return Mage_Sales_Model_Order
     * @throws Mage_Core_Exception
     */
    public function registerCancellation($comment = '', $graceful = true)
    {
        if ($this->canCancel()) {
            $cancelState = self::STATE_CANCELED;
            foreach ($this->getAllItemsAndInquiries() as $item) {
                if ($cancelState != self::STATE_PROCESSING && $item->getQtyToRefund()) {
                    if ($item->getQtyToShip() > $item->getQtyToCancel()) {
                        $cancelState = self::STATE_PROCESSING;
                    } else {
                        $cancelState = self::STATE_COMPLETE;
                    }
                }
                $item->cancel();
            }

            $this->setSubtotalCanceled($this->getSubtotal() - $this->getSubtotalInvoiced());
            $this->setBaseSubtotalCanceled($this->getBaseSubtotal() - $this->getBaseSubtotalInvoiced());

            $this->setTaxCanceled($this->getTaxAmount() - $this->getTaxInvoiced());
            $this->setBaseTaxCanceled($this->getBaseTaxAmount() - $this->getBaseTaxInvoiced());

            $this->setShippingCanceled($this->getShippingAmount() - $this->getShippingInvoiced());
            $this->setBaseShippingCanceled($this->getBaseShippingAmount() - $this->getBaseShippingInvoiced());

            $this->setDiscountCanceled(abs($this->getDiscountAmount()) - $this->getDiscountInvoiced());
            $this->setBaseDiscountCanceled(abs($this->getBaseDiscountAmount()) - $this->getBaseDiscountInvoiced());

            $this->setTotalCanceled($this->getGrandTotal() - $this->getTotalPaid());
            $this->setBaseTotalCanceled($this->getBaseGrandTotal() - $this->getBaseTotalPaid());

            $this->_setState($cancelState, true, $comment);
        } elseif (!$graceful) {
            Mage::throwException(Mage::helper('sales')->__('Order does not allow to be canceled.'));
        }
        return $this;
    }

    /**
     * Retrieve order cancel availability
     *
     * @return bool
     */
    public function canCancel()
    {
        if ($this->canUnhold()) {  // $this->isPaymentReview()
            return false;
        }

        $allInvoiced = true;
        foreach ($this->getAllItemsAndInquiries() as $item) {
            if ($item->getQtyToInvoice()) {
                $allInvoiced = false;
                break;
            }
        }
        if ($allInvoiced) {
            return false;
        }

        $state = $this->getState();
        if ($this->isCanceled() || $state === self::STATE_COMPLETE || $state === self::STATE_CLOSED) {
            return false;
        }

        if ($this->getActionFlag(self::ACTION_FLAG_CANCEL) === false) {
            return false;
        }

        if (!$this->hasStatusChangeReason()){
            return false;
        }

        /**
         * Use only state for availability detect
         */
        /*foreach ($this->getAllItems() as $item) {
            if ($item->getQtyToCancel()>0) {
                return true;
            }
        }
        return false;*/
        return true;
    }


    /*
    public function getAllItems()
    {
        $trace = debug_backtrace(false);
        if (1 || $trace[0]['class'] != $trace[1]['class']){
            return parent::getAllItems();
        }
        return array_merge(parent::getAllItems(), $this->getAllInquiries());
    }
    */
    public function getAllItemsAndInquiries()
    {
        return array_merge(parent::getAllItems(), $this->getAllInquiries());
    }

    public function getManager()
    {
        if (!isset($this->_manager)){
            $this->_manager = Mage::getModel('admin/user')->load($this->getManagerId());
        }
        return $this->_manager;
    }

    public function setManager($user)
    {
        $this->_manager = $user;
        $this->setManagerId($user->getId());
        $this->setManagerName($user->getName());
        return $this;
    }

    /*
     * Add a comment to order
     * Different or default status may be specified
     *
     * @param string $comment
     * @param string $status
     * @return Mage_Sales_Order_Status_History
     */
    public function addStatusHistoryComment($comment, $status = false, $supplyStatus = false, $reason = null)
    {
        if (false === $status) {
            $status = $this->getStatus();
        } elseif (true === $status) {
            $status = $this->getConfig()->getStateDefaultStatus($this->getState());
        } else {
            $this->setStatus($status);
        }
        if ($supplyStatus === false){
            $supplyStatus = $this->getSupplyStatus();
        }else{
            $this->setSupplyStatus($supplyStatus);
        }
        $this->setLastStatusHistoryComment($comment);
        $history = Mage::getModel('sales/order_status_history')
            ->setStatus($status)
            ->setSupplyStatus($supplyStatus)
            ->setStatusChangeReason($reason)
            ->setComment($comment)
            ->setEntityName($this->_historyEntityName);
        $this->addStatusHistory($history);
        if (!is_null($reason)){
            $this->_hasStatusChangeReason = true;
        }
        return $history;
    }

    public function getSupplyStatus()
    {
        $supplyStatus = $this->getData('supply_status');
        if (!$supplyStatus){
            $supplyStatus = $this->getDefaultSupplyStatus();
        }
        return $supplyStatus;
    }

    public function getDefaultSupplyStatus()
    {
        return Testimonial_MageDoc_Model_Source_Order_Supply_Status::PENDING;
    }

    public function getShippingCarrierCode()
    {
        return $this->getShippingCarrier()->getCarrierCode();
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();
        /**
         * Process inquiries dependency for new order
         */
        if (!$this->getId()) {
            $inquiriesCount = 0;
            foreach ($this->getAllInquiries() as $inquiry) {
                $parent = $inquiry->getQuoteParentInquiryId();
                if ($parent && !$inquiry->getParentInquiry()) {
                    //$inquiry->setParentInquiry($this->getInquiryByQuoteInquiryId($parent));
                } elseif (!$parent) {
                    $inquiriesCount++;
                }
            }
            // Set items count
            $this->setTotalItemCount($this->getTotalItemCount()+$inquiriesCount);
        }

        $this->_checkSupplyStatus();
        $this->_checkSupplyDate();
    }

    protected function _checkSupplyStatus()
    {
        $order = $this;
        if ($order->getGrandTotal() == $order->getTotalInvoiced()
            && $order->getSupplyStatus() == Testimonial_MageDoc_Model_Source_Order_Supply_Status::PENDING){
            $order->addStatusHistoryComment('', false, Testimonial_MageDoc_Model_Source_Order_Supply_Status::ARRANGED);
        }
        if ($order->getSupplyStatus() == Testimonial_MageDoc_Model_Source_Order_Supply_Status::ARRANGED
            && $order->isAllItemsReserved()
        ) {
            $order->addStatusHistoryComment('', false, Testimonial_MageDoc_Model_Source_Order_Supply_Status::RESERVED);
        }
        if (($order->getSupplyStatus() == Testimonial_MageDoc_Model_Source_Order_Supply_Status::ARRANGED
            || $order->getSupplyStatus() == Testimonial_MageDoc_Model_Source_Order_Supply_Status::RESERVED
            || $order->getSupplyStatus() == Testimonial_MageDoc_Model_Source_Order_Supply_Status::ASSEMBLING)
        ) {
            if ($order->isAllItemsAssembled()){
                $order->addStatusHistoryComment('', false, Testimonial_MageDoc_Model_Source_Order_Supply_Status::ASSEMBLED);
            } elseif ($order->getSupplyStatus() != Testimonial_MageDoc_Model_Source_Order_Supply_Status::ASSEMBLING
                && $order->hasAssembledItems()){
                $order->addStatusHistoryComment('', false, Testimonial_MageDoc_Model_Source_Order_Supply_Status::ASSEMBLING);
            }
        }
        if ($order->isAllItemsQtyShipped()
            && ($order->getSupplyStatus() == Testimonial_MageDoc_Model_Source_Order_Supply_Status::PENDING
                || $order->getSupplyStatus() == Testimonial_MageDoc_Model_Source_Order_Supply_Status::ARRANGED
                || $order->getSupplyStatus() == Testimonial_MageDoc_Model_Source_Order_Supply_Status::RESERVED
                || $order->getSupplyStatus() == Testimonial_MageDoc_Model_Source_Order_Supply_Status::ASSEMBLING
                || $order->getSupplyStatus() == Testimonial_MageDoc_Model_Source_Order_Supply_Status::ASSEMBLED)){
            $order->addStatusHistoryComment('', false, Testimonial_MageDoc_Model_Source_Order_Supply_Status::SHIPPED);
            if ($order->dataHasChangedFor('supply_status')){
                Mage::dispatchEvent('magedoc_order_shipped', array('order' => $order));
            }
        }
    }

    public function hasReservedItems()
    {
        foreach ($this->getAllItemsAndInquiries() as $item){
            if (($item->getSupplyStatus() == Testimonial_MageDoc_Model_Source_SuppliedType::RESERVED
                || $item->getSupplyStatus == Testimonial_MageDoc_Model_Source_SuppliedType::WAREHOUSE_DELIVERY)){
                return true;
            }
        }
        return false;
    }

    public function updateSupplyStatus()
    {
        $this->_checkSupplyStatus();
        $this->_checkSupplyDate();
        if ($this->dataHasChangedFor('supply_status')) {
            $this->getResource()->saveAttribute($this, 'supply_status');
            $this->getResource()->saveAttribute($this, 'last_supply_status');
            $this->getStatusHistoryCollection()->save();
            Mage::dispatchEvent('magedoc_order_supply_status_change', array('order' => $this));
        }
        if ($this->dataHasChangedFor('supply_date')) {
            $this->getResource()->saveAttribute($this, 'supply_date');
        }
        return $this;
    }

    protected function _checkSupplyDate()
    {
        $supplyDate = Mage::app()->getLocale()->date();
        $itemSupplyDate = Mage::app()->getLocale()->date();
        $isFirst = true;

        foreach ($this->getAllItemsAndInquiries() as $item){
            if (!$item->getSupplyDate()){
                return $this;
            }
            $itemSupplyDate->set($item->getSupplyDate(), Zend_Date::DATES);
            if ($isFirst || $itemSupplyDate->compare($supplyDate, Zend_Date::DATES) == 1){
                $supplyDate->set($item->getSupplyDate(), Zend_Date::DATES);
                $isFirst = false;
            }
        }
        if (!$isFirst){
            $this->setSupplyDate($supplyDate->toString('YYYY-MM-dd 00:00:00'));
        }
    }

    /**
     * Retrieve order invoices collection
     *
     * @return unknown
     */
    public function getInvoiceCollection()
    {
        if (is_null($this->_invoices)) {
            $this->_invoices = Mage::getResourceModel('sales/order_invoice_collection')
                ->setModel('magedoc/order_invoice')
                ->setOrderFilter($this);

            if ($this->getId()) {
                foreach ($this->_invoices as $invoice) {
                    $invoice->setOrder($this);
                }
            }
        }
        return $this->_invoices;
    }

    /**
     * Retrieve order shipments collection
     *
     * @return unknown
     */
    public function getShipmentsCollection()
    {
        if (empty($this->_shipments)) {
            if ($this->getId()) {
                $this->_shipments = Mage::getResourceModel('sales/order_shipment_collection')
                    ->setModel('magedoc/order_shipment')
                    ->setOrderFilter($this)
                    ->load();
            } else {
                return false;
            }
        }
        return $this->_shipments;
    }

    public function getCreditmemosCollection()
    {
        if (empty($this->_creditmemos)) {
            if ($this->getId()) {
                $this->_creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')
                    ->setModel('magedoc/order_creditmemo')
                    ->setOrderFilter($this)
                    ->load();
            } else {
                return false;
            }
        }
        return $this->_creditmemos;
    }

    public function setSupplyStatus($supplyStatus)
    {
        $lastSupplyStatus = $this->getSupplyStatus();
        $this->setData('supply_status', $supplyStatus);
        if ($this->dataHasChangedFor('supply_status')){
            $this->setLastSupplyStatus($lastSupplyStatus);
        }
        return $this;
    }

    public function getInquiryById($inquiryId)
    {
        $inquiry = null;
        foreach ($this->getVehiclesCollection() as $vehicle) {
            if (!$vehicle->isDeleted()) {
                if ($inquiry = $vehicle->getInquiriesCollection()->getItemById($inquiryId)){
                    break;
                }
            }
        }

        return $inquiry;
    }

    public function getBackendUrl()
    {
        return  Mage::getModel('adminhtml/url')->getUrl('*/sales_order/view', array('order_id' => $this->getId()));
    }

    public function canChangeManager()
    {
        return Mage::getSingleton('admin/session')->isLoggedIn() &&
        ($this->getManagerId() == Mage::getSingleton('admin/session')->getUser()->getId()
        || Mage::getSingleton('admin/session')->getUser()->getId() == Mage::helper('magedoc')->getDefaultAdminUserId() );
    }

    public function getItemById($itemId)
    {
        if (($position = strpos($itemId, 'inquiry_')) === 0){
            return $this->getInquiryById(substr($itemId, 8));
        } else {
            return parent::getItemById($itemId);
        }
    }
    public function setGmtShippingDate($date)
    {
        $date = Mage::getModel('core/date')->gmtDate($date);
        $this->setShippingDate($date);
    }

    public function hasStatusChangeReason()
    {
        if (!isset($this->_hasStatusChangeReason)){
            $historyCollection = $this->getStatusHistoryCollection(true);
            $historyCollection->addFieldToFilter('status_change_reason', array('notnull' => true));
            $this->_hasStatusChangeReason = (bool)$historyCollection->getSize();
        }
        return $this->_hasStatusChangeReason;
    }
}
