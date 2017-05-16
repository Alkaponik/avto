<?php

class Testimonial_MageDoc_Model_Mysql4_Order_Vehicle_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    
    
    protected function _construct()
    {
        $this->_init('magedoc/order_vehicle');
    }  
    
    
    public function setOrderFilter($order)
    {   
        if ($order instanceof Mage_Sales_Model_Order) {
            $orderId = $order->getId();
            if ($orderId) {
                $this->addFieldToFilter('order_id', $orderId);
            }else{ 
                $this->_totalRecords = 0;
                $this->_setIsLoaded(true);
            }
        } else {
            $this->addFieldToFilter('order_id', $order);
        }
        return $this;
    }

}
