<?php

class Testimonial_MageDoc_Model_Order_Create extends Mage_Adminhtml_Model_Sales_Order_Create
{
    
    public function __construct()
    {
        $this->_session = Mage::getSingleton('magedoc/session_quote');
    }
    
    public function createOrder()
    {
        $this->_prepareCustomer();
        $this->_validate();
        $quote = $this->getQuote();
        $this->_prepareQuoteItems();

        /* @var $service Testimonial_MageDoc_Model_Service_Quote */
        $service = Mage::getModel('magedoc/service_quote', $quote);
        $order = null;
        $isEditMode = false;
        if ($this->getSession()->getOrder()->getId()) {
            $oldOrder = $this->getSession()->getOrder();
            if ($oldOrder->getState() != Mage_Sales_Model_Order::STATE_NEW) {
                $originalId = $oldOrder->getOriginalIncrementId();
                if (!$originalId) {
                    $originalId = $oldOrder->getIncrementId();
                }
                $orderData = array(
                    'original_increment_id' => $originalId,
                    'relation_parent_id' => $oldOrder->getId(),
                    'relation_parent_real_id' => $oldOrder->getIncrementId(),
                    'edit_increment' => $oldOrder->getEditIncrement() + 1,
                    'increment_id' => $originalId . '-' . ($oldOrder->getEditIncrement() + 1)
                );
                $quote->setReservedOrderId($orderData['increment_id']);

                $service->setOrderData($orderData);
            } else {
                $isEditMode = true;
                $order = $oldOrder;
                $quote->setReservedOrderId($order->getIncrementId());
            }
        }
        
        $order = $service->submit($order);
        
        if ((!$quote->getCustomer()->getId() || !$quote->getCustomer()->isInStore($this->getSession()->getStore()))
            && !$quote->getCustomerIsGuest()
        ) {
            $quote->getCustomer()->setCreatedAt($order->getCreatedAt());
            $quote->getCustomer()
                ->save()
                ->sendNewAccountEmail('registered', '', $quote->getStoreId());;
        }
        if ($this->getSession()->getOrder()->getId() && !$isEditMode) {
            $oldOrder = $this->getSession()->getOrder();

            $this->getSession()->getOrder()->setRelationChildId($order->getId());
            $this->getSession()->getOrder()->setRelationChildRealId($order->getIncrementId());
            $this->getSession()->getOrder()->cancel()
                ->save();
            $order->save();
        }
        if ($this->getSendConfirmation()) {
            $order->sendNewOrderEmail();
        }

        Mage::dispatchEvent('checkout_submit_all_after', array('order' => $order, 'quote' => $quote));

        return $order;
    }

    public function _prepareCustomer()
    {
        parent::_prepareCustomer();
        $quoteVehicles = $this->getQuote()->getAllVehicles();
        $customer = $this->getQuote()->getCustomer();
        foreach($quoteVehicles as $vehicle){
            $customerVehicle = $vehicle->exportCustomerVehicle();
            $customerVehicle->setQuoteVehicle($vehicle);
            $customerVehicleId = $vehicle->getCustomerVehicleId();
            /**
             * @todo: fix order edit
             * $customer->getVehicleItemById($customerVehicleId) returns null
             */
            if ($customerVehicleId
                && $customer->getVehicleItemById($customerVehicleId)){
                $customer->getVehicleItemById($customerVehicleId)->addData($customerVehicle->getData());
            } else {
                $customer->addVehicle($customerVehicle);
            }
        }

        return $this;
    }
    
    public function initFromOrder(Mage_Sales_Model_Order $order)
    {
        if (!$order->getReordered()) {
            $this->getSession()->setOrderId($order->getId());
        } else {
            $this->getSession()->setReordered($order->getId());
        }

        /**
         * Check if we edit quest order
         */
        $this->getSession()->setCurrencyId($order->getOrderCurrencyCode());
        if ($order->getCustomerId()) {
            $this->getSession()->setCustomerId($order->getCustomerId());
        } else {
            $this->getSession()->setCustomerId(false);
        }

        $this->getSession()->setStoreId($order->getStoreId());

        /**
         * Initialize catalog rule data with new session values
         */
        $this->initRuleData();
        foreach ($order->getItemsCollection(
            array_keys(Mage::getConfig()->getNode('adminhtml/sales/order/create/available_product_types')->asArray()),
            true
            ) as $orderItem) {
            /* @var $orderItem Mage_Sales_Model_Order_Item */
            if (!$orderItem->getParentItem()) {
                if ($order->getReordered()) {
                    $qty = $orderItem->getQtyOrdered();
                } else {
                    //$qty = $orderItem->getQtyOrdered() - $orderItem->getQtyShipped() - $orderItem->getQtyInvoiced();
                    $qty = $orderItem->getQtyOrdered();
                }
                if ($qty > 0) {
                    $item = $this->initFromOrderItem($orderItem, $qty);
                    $item->setRetailerId($orderItem->getRetailerId());
                    if (!$order->getReordered()) {
                        $item->setSupplyStatus($orderItem->getSupplyStatus());
                        $item->setQtyReserved($orderItem->getQtyReserved());
                        $item->setQtySupplied($orderItem->getQtySupplied());
                        $item->setSupplyDate($orderItem->getSupplyDate());
                    }
                    if (is_string($item)) {
                        Mage::throwException($item);
                    }
                }
            }
        }
        
        foreach ($order->getVehiclesCollection() as $orderVehicle) {
            $quoteVehicle = $this->getQuote()->addVehicle($orderVehicle);
            foreach($orderVehicle->getInquiriesCollection() as $orderInquiry){
                if ($order->getReordered()) {
                    $qty = $orderInquiry->getQtyOrdered();
                } else {
                    //$qty = $orderInquiry->getQtyOrdered() - $orderInquiry->getQtyShipped() - $orderInquiry->getQtyInvoiced();
                    $qty = $orderInquiry->getQtyOrdered();
                }
                if ($qty > 0) {
                    if ($order->getReordered()) {
                        $orderInquiry->setReordered(true);
                    }
                    $quoteVehicle->addInquiry($orderInquiry, $qty);
                }
            }
        }
        
        $this->_initBillingAddressFromOrder($order);
        $this->_initShippingAddressFromOrder($order);

        if (!$this->getQuote()->isVirtual() && $this->getShippingAddress()->getSameAsBilling()) {
            $this->setShippingAsBilling(1);
        }

        $this->setShippingMethod($order->getShippingMethod());
        $this->getQuote()->getShippingAddress()->setShippingDescription($order->getShippingDescription());

        $this->getQuote()->getPayment()->addData($order->getPayment()->getData());


        $orderCouponCode = $order->getCouponCode();
        if ($orderCouponCode) {
            $this->getQuote()->setCouponCode($orderCouponCode);
        }

        if ($this->getQuote()->getCouponCode()) {
            $this->getQuote()->collectTotals();
        }

        Mage::helper('core')->copyFieldset(
            'sales_copy_order',
            'to_edit',
            $order,
            $this->getQuote()
        );

        Mage::dispatchEvent('sales_convert_order_to_quote', array(
            'order' => $order,
            'quote' => $this->getQuote()
        ));

        if (!$order->getCustomerId()) {
            $this->getQuote()->setCustomerIsGuest(true);
        }

        if ($this->getSession()->getUseOldShippingMethod(true)) {
            /*
             * if we are making reorder or editing old order
             * we need to show old shipping as preselected
             * so for this we need to collect shipping rates
             */
            $this->collectShippingRates();
        } else {
            /*
             * if we are creating new order then we don't need to collect
             * shipping rates before customer hit appropriate button
             */
            $this->collectRates();
        }

        if($order->getShippingDate()){
            $this->getQuote()->setShippingDate($order->getShippingDate());
        }

        if($order->getSupplyDate()){
            $this->getQuote()->setSupplyDate($order->getSupplyDate());
        }


        // Make collect rates when user click "Get shipping methods and rates" in order creating
        // $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        // $this->getQuote()->getShippingAddress()->collectShippingRates();

        $this->getQuote()->save();

        return $this;
    }

   /**
     * Update quantity of order quote items
     *
     * @param   array $data
     * @return  Mage_Adminhtml_Model_Sales_Order_Create
     */
    public function updateQuoteItems($data)
    {
        if (is_array($data)) {
            try {
                foreach ($data as $itemId => $info) {
                    if (!empty($info['configured'])) {
                        $item = $this->getQuote()->updateItem($itemId, new Varien_Object($info));
                        $itemQty = (float)$item->getQty();
                    } else {
                        $item       = $this->getQuote()->getItemById($itemId);
                        $itemQty    = (float)$info['qty'];
                    }

                    if ($item) {
                        if (isset($info['retailer_id'])){
                            $item->setData('retailer_id', $info['retailer_id']);
                        }
                        
                        if ($item->getProduct()->getStockItem()) {
                            if (!$item->getProduct()->getStockItem()->getIsQtyDecimal()) {
                                $itemQty = (int)$itemQty;
                            } else {
                                $item->setIsQtyDecimal(1);
                            }
                        }
                        
                        $itemQty    = $itemQty > 0 ? $itemQty : 1;
                        if (isset($info['custom_price'])) {
                            $itemPrice  = $this->_parseCustomPrice($info['custom_price']);
                        } else {
                            $itemPrice = null;
                        }
                        if (isset($info['custom_cost'])) {
                            $itemCost  = $this->_parseCustomPrice($info['custom_cost']);
                        } else {
                            $itemCost = null;
                        }
                        $noDiscount = !isset($info['use_discount']);

                        if (empty($info['action']) || !empty($info['configured'])) {
                            $item->setQty($itemQty);
                            $item->setCustomPrice($itemPrice);
                            $item->setOriginalCustomPrice($itemPrice);
                            $item->setCustomCost($itemCost);
                            $item->setNoDiscount($noDiscount);
                            $item->getProduct()->setIsSuperMode(true);
                            $item->getProduct()->unsSkipCheckRequiredOption();
                            $item->checkData();
                        } else {
                            if ($info['action'] == 'copy'){
                                $this->copyQuoteItem($item->getId(), $info['action'], $itemQty);
                            }else{
                                $this->moveQuoteItem($item->getId(), $info['action'], $itemQty);
                            }
                        }
                    }
                            
                }
            } catch (Mage_Core_Exception $e) {
                $this->recollectCart();
                throw $e;
            } catch (Exception $e) {
                Mage::logException($e);
            }
            $this->recollectCart();
        }
        return $this;
    }

        /**
     * Validate quote data before order creation
     *
     * @return Mage_Adminhtml_Model_Sales_Order_Create
     */
    protected function _validate()
    {
        $customerId = $this->getSession()->getCustomerId();
        if (is_null($customerId)) {
            Mage::throwException(Mage::helper('adminhtml')->__('Please select a customer.'));
        }

        if (!$this->getSession()->getStore()->getId()) {
            Mage::throwException(Mage::helper('adminhtml')->__('Please select a store.'));
        }
        $items = $this->getQuote()->getAllItems();
        $inquiries = $this->getQuote()->getAllInquiries();

        if (count($items) == 0 && count($inquiries) == 0) {
            $this->_errors[] = Mage::helper('adminhtml')->__('You need to specify order items.');
        }

        foreach ($items as $item) {
            $messages = $item->getMessage(false);
            if ($item->getHasError() && is_array($messages) && !empty($messages)) {
                $this->_errors = array_merge($this->_errors, $messages);
            }
        }

        if (!$this->getQuote()->isVirtual()) {
            if (!$this->getQuote()->getShippingAddress()->getShippingMethod()) {
                $this->_errors[] = Mage::helper('adminhtml')->__('Shipping method must be specified.');
            }
        }

        if (!$this->getQuote()->getPayment()->getMethod()) {
            $this->_errors[] = Mage::helper('adminhtml')->__('Payment method must be specified.');
        } else {
            $method = $this->getQuote()->getPayment()->getMethodInstance();
            if (!$method) {
                $this->_errors[] = Mage::helper('adminhtml')->__('Payment method instance is not available.');
            } else {
                if (!$method->isAvailable($this->getQuote())) {
                    $this->_errors[] = Mage::helper('adminhtml')->__('Payment method is not available.');
                } else {
                    try {
                        $method->validate();
                    } catch (Mage_Core_Exception $e) {
                        $this->_errors[] = $e->getMessage();
                    }
                }
            }
        }

        if (!empty($this->_errors)) {
            foreach ($this->_errors as $error) {
                $this->getSession()->addError($error);
            }
            Mage::throwException('');
        }
        return $this;
    }

    public function copyQuoteItem($item, $moveTo, $qty)
    {
        $item = $this->_getQuoteItem($item);
        if ($item) {
            $config = array();
            $config['qty'] = $qty;
            $config['retailer_id'] = Mage::helper('magedoc')->getDefaultRetailerId();
            try {
                $this->addProduct($item->getProductId(), $config);
                $this->setRecollect(true);
            }
            catch (Mage_Core_Exception $e){
                $this->getSession()->addError($e->getMessage());
            }
            catch (Exception $e){
                return $e;
            }
        }
    }

    public function createQuote()
    {
        $this->_prepareCustomer();
        $quote = $this->getQuote();
        $transaction = Mage::getModel('core/resource_transaction');
        if ($quote->getCustomerId()) {
            $transaction->addObject($quote->getCustomer());
        }
        $transaction->addObject($quote);
        $transaction->save();
        return $quote;
    }
}
