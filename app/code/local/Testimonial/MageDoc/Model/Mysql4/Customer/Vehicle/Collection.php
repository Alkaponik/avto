<?php

class Testimonial_MageDoc_Model_Mysql4_Customer_Vehicle_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('magedoc/customer_vehicle');
    }

    public function setCustomerFilter($customer)
    {
        if ($customer->getId()) {
            $this->addFieldToFilter('customer_id', $customer->getId());
        }
        return $this;
    }
}

