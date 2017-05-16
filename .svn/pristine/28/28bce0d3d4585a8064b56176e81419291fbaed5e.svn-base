<?php

class Testimonial_Avtoto_Model_Observer
{
    public function sales_order_invoice_register(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if (($order->getPayment()->getMethod() == 'checkmo'
               || $order->getPayment()->getMethod() == 'bankpayment')
                && bccomp($order->getBaseGrandTotal(), $order->getBaseTotalPaid(), 4) == 1){
            $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, 'pending_payment');
        }
    }

    public function sales_order_invoice_cancel(Varien_Event_Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();
        if (bccomp($order->getBaseTotalInvoiced(), 0, 4) == 0
            && in_array($order->getSupplyStatus(),
                array(
                    Testimonial_MageDoc_Model_Source_Order_Supply_Status::PENDING,
                    Testimonial_MageDoc_Model_Source_Order_Supply_Status::ARRANGED,
                    Testimonial_MageDoc_Model_Source_Order_Supply_Status::RESERVED,
                    )
            )){
            $order->setState(Mage_Sales_Model_Order::STATE_NEW, true);
            $invoice->getOrder()->setIsInProcess(false);
        }
    }

    public function sales_order_payment_cancel_invoice(Varien_Event_Observer $observer)
    {
        /* @var $payment Mage_Sales_Model_Order_Payment */
        $payment = $observer->getEvent()->getPayment();
        $invoice = $observer->getEvent()->getInvoice();
        if ($invoice->getState() != Mage_Sales_Model_Order_Invoice::STATE_PAID){
            $this->_updateObjectTotals($payment, array(
                'amount_paid' => 1 * $invoice->getGrandTotal(),
                'base_amount_paid' => 1 * $invoice->getBaseGrandTotal(),
                'shipping_captured' => 1 * $invoice->getShippingAmount(),
                'base_shipping_captured' => 1 * $invoice->getBaseShippingAmount(),
            ));
        }
    }

    public function magedoc_category_edit_prepare_form(Varien_Event_Observer $observer)
    {
        $form = $observer->getEvent()->getForm();

        if ($element = $form->getElement('search_tree_category_id')){
            $url = Mage::getModel('core/url')->getUrl('category.php', array('_type' => Mage_Core_Model_Store::URL_TYPE_WEB));
            $element->setSourceUrl($url);
            $element->getRenderer()->setSourceUrl($url);
        }
    }

    public function novaposhta_warehouse_update_complete(Varien_Event_Observer $observer)
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_write');
        $query = <<<SQL
        TRUNCATE TABLE  `shipping_multipletablerates`;
SQL;
        $adapter->query($query);
        foreach (Mage::app()->getWebsites() as $website) {
            $query = <<<SQL
        INSERT INTO `shipping_multipletablerates` (
        SELECT NULL , {$website->getId()},  'UA', region_id,  '',  'package_value_incl_tax', 0,  'value', CONCAT(  'all_customers_npt',
        '_', w.id,  '_', REPLACE( name_ua,  ' ',  '' ) ,  '_', number_in_city ) , CONCAT( c.name_ua,  ' ', w.address_ua
        ) AS method_name,  'Новая почта', 0, 0, 0, 0, 0, 0
        FROM novaposhta_warehouse AS w
        INNER JOIN novaposhta_city AS c ON c.id = w.city_id
        ORDER BY name_ua, number_in_city);
SQL;
            $adapter->query($query);
        }
        Mage::getSingleton('adminhtml/session')->addSuccess('Novaposhta shipping method options were updated');
    }

    protected function _updateObjectTotals($object, $data)
    {
        foreach ($data as $key => $amount) {
            if (null !== $amount) {
                $was = $object->getDataUsingMethod($key);
                $object->setDataUsingMethod($key, $was + $amount);
            }
        }
        return $object;
    }

    public function magedoc_order_supply_status_change(Varien_Event_Observer $observer)
    {
        $order = $observer->getOrder();
        $this->_processAssembledOrder($order);
    }

    public function sales_order_save_after(Varien_Event_Observer $observer)
    {
        $order = $observer->getOrder();
        $this->_processAssembledOrder($order);
    }

    /**
     * @param $order Testimonial_MageDoc_Model_Order
     */
    protected function _processAssembledOrder($order)
    {
        if ($order->dataHasChangedFor('supply_status')
            && $order->getSupplyStatus() == Testimonial_MageDoc_Model_Source_Order_Supply_Status::ASSEMBLED
            && $order->getShippingMethod() == 'freeshipping_freeshipping'
            && Mage::helper('avtoto')->getCreateCrmCallOnOrderAssembly($order->getStoreId())
            && $statusHistory = $order->getStatusHistoryCollection()->getLastItem()
        ){
            Mage::helper('sugarcrm/call')->exportCallToSugarcrm( $statusHistory );
        }
    }
}