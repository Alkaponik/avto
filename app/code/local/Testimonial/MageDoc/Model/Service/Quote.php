<?php
class Testimonial_MageDoc_Model_Service_Quote extends Mage_Sales_Model_Service_Quote
{
    public function __construct(Mage_Sales_Model_Quote $quote)
    {
        $this->_quote       = $quote;
        $this->_convertor   = Mage::getModel('magedoc/convert_quote');
    }

    /**
     * @deprecated after 1.4.0.1
     * @see submitOrder()
     * @see submitAll()
     */
    public function submit($order = null)
    {
        return $this->submitOrder($order);
    }
    
    public function submitOrder($order = null)
    {
        $isEditMode = !is_null($order);
        $this->_deleteNominalItems();
        $this->_validate();
        $quote = $this->_quote;
        $isVirtual = $quote->isVirtual();

        $transaction = Mage::getModel('core/resource_transaction');
        if ($quote->getCustomerId()) {
            $transaction->addObject($quote->getCustomer());
        }
        $transaction->addObject($quote);

        if ($isEditMode){
            $this->_prepareOrderForEdit($order);
            $this->_convertor->toOrder($quote, $order);
        } else {
            $quote->reserveOrderId();
        }
        if ($isVirtual) {
            $order = $this->_convertor->addressToOrder($quote->getBillingAddress(), $order);
        } else {
            $order = $this->_convertor->addressToOrder($quote->getShippingAddress(), $order);
        }

        $order->setBillingAddress($this->_convertor->addressToOrderAddress($quote->getBillingAddress()));
        if ($quote->getBillingAddress()->getCustomerAddress()) {
            $order->getBillingAddress()->setCustomerAddress($quote->getBillingAddress()->getCustomerAddress());
        }
        if (!$isVirtual) {
            $order->setShippingAddress($this->_convertor->addressToOrderAddress($quote->getShippingAddress()));
            if ($quote->getShippingAddress()->getCustomerAddress()) {
                $order->getShippingAddress()->setCustomerAddress($quote->getShippingAddress()->getCustomerAddress());
            }
        }
        $order->setPayment($this->_convertor->paymentToOrderPayment($quote->getPayment()));

        foreach($this->_quote->getVehiclesCollection() as $vehicle){
            $orderVehicle = $this->_convertor->vehicleToOrderVehicle($vehicle);
            foreach($vehicle->getInquiriesCollection() as $inquiry){
                $orderInquiry = $this->_convertor->inquiryToOrderInquiry($inquiry);
                $orderVehicle->addInquiry($orderInquiry);
            }
            $order->addVehicle($orderVehicle);
        }
        
        foreach ($this->_orderData as $key => $value) {
            $order->setData($key, $value);
        }

        foreach ($quote->getAllItems() as $item) {
            $orderItem = $this->_convertor->itemToOrderItem($item);
            if ($item->getParentItem()) {
                $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
            }
            $order->addItem($orderItem);
        }

        $order->setQuote($quote);

        $transaction->addObject($order);
        $transaction->addCommitCallback(array($order, 'place'));
        $transaction->addCommitCallback(array($order, 'save'));

        /**
         * We can use configuration data for declare new order status
         */
        Mage::dispatchEvent('checkout_type_onepage_save_order', array('order'=>$order, 'quote'=>$quote));
        Mage::dispatchEvent('sales_model_service_quote_submit_before', array('order'=>$order, 'quote'=>$quote));
        try {
            
            $transaction->save();
                    
            $this->_inactivateQuote();
            Mage::dispatchEvent('sales_model_service_quote_submit_success', array('order'=>$order, 'quote'=>$quote));
        } catch (Exception $e) {

            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                // reset customer ID's on exception, because customer not saved
                $quote->getCustomer()->setId(null);
            }

            //reset order ID's on exception, because order not saved
            $order->setId(null);
            /** @var $item Mage_Sales_Model_Order_Item */
            foreach ($order->getItemsCollection() as $item) {
                $item->setOrderId(null);
                $item->setItemId(null);
            }

            Mage::dispatchEvent('sales_model_service_quote_submit_failure', array('order'=>$order, 'quote'=>$quote));
            throw $e;
        }
        Mage::dispatchEvent('sales_model_service_quote_submit_after', array('order'=>$order, 'quote'=>$quote));
        $this->_order = $order;
        return $order;
    }

    protected function _prepareOrderForEdit($order)
    {
        foreach ($order->getAllItemsAndInquiries() as $item) {
            $item->isDeleted(true);
        }
        foreach ($order->getVehiclesCollection() as $vehicle) {
            $vehicle->isDeleted(true);
        }
        $order->setSupplyStatus($order->getDefaultSupplyStatus());
    }
}
