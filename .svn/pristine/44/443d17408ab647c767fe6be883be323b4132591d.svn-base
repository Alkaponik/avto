<?php

class Testimonial_SugarCRM_Model_Observer
{

    public function customer_save_after(Varien_Event_Observer $observer)
    {
        $customer = $observer->getCustomer();
        Mage::helper('sugarcrm/customer')->exportCustomerToSugarcrm($customer);
    }

    function sales_order_save_after(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        Mage::helper('sugarcrm/order')->exportOrderToSugarcrm($order);
    }

}
