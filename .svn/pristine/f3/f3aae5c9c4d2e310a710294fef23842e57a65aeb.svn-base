<?php

class Testimonial_MageDoc_Helper_Supply extends Mage_Core_Helper_Abstract
{
    const CONFIG_XML_PATH_SUPPLY_ORDER_STATUSES = 'magedoc/supply_management/supply_order_statuses';
    const CONFIG_XML_PATH_VISIBLE_ORDER_SUPPLY_STATUSES = 'magedoc/supply_management/visible_order_supply_statuses';

    protected $_currentDate;
    protected $_date;

    public function getVisibleOrderStatuses($storeId = null)
    {
        if (Mage::getSingleton('admin/session')->isLoggedIn() &&
            !($statuses = Mage::getSingleton('admin/session')->getUser()->getRole()->getVisibleOrderStatuses()))
        {
            $statuses = Mage::getStoreConfig(self::CONFIG_XML_PATH_SUPPLY_ORDER_STATUSES, $storeId);
        }

        return $statuses ? explode(',', $statuses) : array();
    }

    public function getVisibleOrderSupplyStatuses($storeId = null)
    {
        if (Mage::getSingleton('admin/session')->isLoggedIn() &&
            !($statuses = Mage::getSingleton('admin/session')->getUser()->getRole()->getVisibleOrderSupplyStatuses()))
        {
            $statuses = Mage::getStoreConfig(self::CONFIG_XML_PATH_VISIBLE_ORDER_SUPPLY_STATUSES, $storeId);
        }
        return $statuses ? explode(',', $statuses) : array();
    }

    public function getItemSupplyStatus($item)
    {
        if (!is_object($item)){
            $item = new Vairen_Object($item);
        }
        $deliveryType = Mage::helper('magedoc/price')
            ->getRetailerById($item->getRetailerId())
            ->getSupplyConfig()
            ->getDeliveryType() ;
        return strpos($deliveryType, 'delivery') === 0
            ? Testimonial_MageDoc_Model_Source_SuppliedType::WAREHOUSE_DELIVERY
            : Testimonial_MageDoc_Model_Source_SuppliedType::RESERVED;
    }

    public function getSupplyStatusLabel($supplyStatus)
    {
        return $this->__((string) Mage::getConfig()
            ->getNode(Testimonial_MageDoc_Model_Order::SUPPLY_STATUS_TYPES_PATH . '/'
            . $supplyStatus . "/label"));
    }

    public function hasItemSupplyDataChanged($item)
    {
        $supplyAttributeCodes = array(
            'supply_status',
            'retailer_id',
            'qty_reserved',
            'qty_supplied',
            'supply_date',
            'receipt_reference',
            'return_reference',
        );
        foreach ($supplyAttributeCodes as $attributeCode){
            if ($item->dataHasChangedFor($attributeCode)){
                return true;
            }
        }
        return false;
    }

    public function validateItemSupplySettings($item, $supplySettings)
    {
        $date = $this->_getCurrentDate();
        if ($supplySettings->getSupplyDate()){
            $supplyDate = Mage::app()->getLocale()->date($supplySettings->getSupplyDate());
            $itemSupplyDate = Mage::app()->getLocale()->date($item->getSupplyDate());
            if (!$item->getSupplyDate()
                || $itemSupplyDate->compare($supplyDate, Zend_Date::DATES) != 0){
                if ($date->compare($supplyDate, Zend_Date::DATES) == 1){
                    //Mage::throwException($this->__('Supply date can\'t be in the past', $item->getSku()));
                    $supplySettings->unsSupplyDate();
                    throw Mage::exception('Testimonial_MageDoc', $this->__('Supply date can\'t be in the past', $item->getSku()));
                }
            }else{
                $supplySettings->unsSupplyDate();
            }
        }
        if ($supplySettings->getSupplyStatus() == Testimonial_MageDoc_Model_Source_SuppliedType::SHIPPED
            && (!$supplySettings->getQtySupplied() > 0
                || $supplySettings->getQtySupplied() < $item->getQtyOrdered())) {
            $supplySettings->unsSupplyStatus();
            throw Mage::exception('Testimonial_MageDoc', $this->__('Item %s is not completely shipped yet', $item->getSku()));
        } elseif (($supplySettings->getSupplyStatus() == Testimonial_MageDoc_Model_Source_SuppliedType::RESERVED
            || $supplySettings->getSupplyStatus() == Testimonial_MageDoc_Model_Source_SuppliedType::WAREHOUSE_DELIVERY)
            && $supplySettings->getQtySupplied() == $item->getQtyOrdered()) {
            $supplySettings->setSupplyStatus(Testimonial_MageDoc_Model_Source_SuppliedType::SHIPPED);
        } elseif ($supplySettings->getSupplyStatus() == Testimonial_MageDoc_Model_Source_SuppliedType::UNRESERVED
            && $supplySettings->getQtyReserved() == $item->getQtyOrdered()){
            $supplySettings->setSupplyStatus($this->getItemSupplyStatus($item));
        }
    }

    protected function _getCurrentDate()
    {
        if (!isset($this->_currentDate)){
            $this->_currentDate = Mage::app()->getLocale()->date();
        }
        return $this->_currentDate;
    }

    /**
     * @return Zend_Date
     */

    public function getDate()
    {
        if (!isset($this->_date)){
            $this->_date = Mage::app()->getLocale()->date();
        }
        return $this->_date;
    }
}