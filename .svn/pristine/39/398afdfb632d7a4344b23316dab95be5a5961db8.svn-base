<?php

class Testimonial_MageDoc_Model_Mysql4_Order_Inquiry_Collection 
        extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('magedoc/order_inquiry');
    }  
    
    public function setVehicleFilter($vehicle)
    {   
        if ($vehicle instanceof Testimonial_MageDoc_Model_Order_Vehicle) {
            $vehicleId = $vehicle->getId();
            if ($vehicleId) {
                $this->addFieldToFilter('vehicle_id', $vehicleId);
            }else{ 
                $this->_totalRecords = 0;
                $this->_setIsLoaded(true);
            }
        } else {
            $this->addFieldToFilter('vehicle_id', $vehicle);
        }
        return $this;
    }
    
    public function setOrderModel($order)
    {
        $this->_order = $order;
        $orderId      = $order->getId();
        if ($orderId) {
            $this->addFieldToFilter('order_id', $orderId);
        } else {
            $this->_totalRecords = 0;
            $this->_setIsLoaded(true);
        }
        return $this;
    }

    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->reset(Zend_Db_Select::GROUP);

        $countSelect->columns("COUNT(DISTINCT {$this->getResource()->getIdFieldName()})");

        return $countSelect;
    }

    public function addAttributeToSort($attribute, $dir = 'asc')
    {
        $this->addOrder($this->_attributeToField($attribute), $dir);
        return $this;
    }

    protected function _attributeToField($attribute)
    {
        return $attribute;
    }
}
