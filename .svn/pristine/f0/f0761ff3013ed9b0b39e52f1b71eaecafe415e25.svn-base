<?php

class MageDoc_OrderExport_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CONFIG_XML_PATH_ENABLED = 'magedoc_orderexport/settings/enabled';
    const CONFIG_XML_PATH_SANDBOX_MODE = 'magedoc_orderexport/settings/sandbox_mode';
    const CONFIG_XML_PATH_SPECIFIC_PAYMENT_METHODS = 'magedoc_orderexport/orders/specific_payment_methods';
    const CONFIG_XML_PATH_ALLOWED_PAYMENT_METHODS = 'magedoc_orderexport/orders/payment_methods';
    const CONFIG_XML_PATH_SPECIFIC_ORDER_STATUSES = 'magedoc_orderexport/orders/specific_statuses';
    const CONFIG_XML_PATH_ALLOWED_ORDER_STATUSES = 'magedoc_orderexport/orders/statuses';
    const CONFIG_XML_PATH_SPECIFIC_ORDER_SUPPLY_STATUSES = 'magedoc_orderexport/orders/specific_supply_statuses';
    const CONFIG_XML_PATH_ALLOWED_ORDER_SUPPLY_STATUSES = 'magedoc_orderexport/orders/supply_statuses';

    protected $_allowedPaymentMethods;

    public function isEnabled($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_ENABLED, $storeId);
    }

    public function isSandboxMode($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_SANDBOX_MODE, $storeId);
    }

    public function getAllowedPaymentMethods($storeId = null)
    {
        if (!isset($this->_allowedPaymentMethods)){
            $this->_allowedPaymentMethods = array();
            $specific = Mage::getStoreConfig(self::CONFIG_XML_PATH_SPECIFIC_PAYMENT_METHODS, $storeId);
            $methods = Mage::getStoreConfig(self::CONFIG_XML_PATH_ALLOWED_PAYMENT_METHODS, $storeId);
            if ($specific && $methods){
                $this->_allowedPaymentMethods = explode(',', $methods);
            }
        }
        return $this->_allowedPaymentMethods;
    }

    public function getAllowedOrderStatuses($storeId = null)
    {
        if (!isset($this->_allowedOrderStatuses)){
            $this->_allowedOrderStatuses = array();
            $specific = Mage::getStoreConfig(self::CONFIG_XML_PATH_SPECIFIC_ORDER_STATUSES, $storeId);
            $statuses = Mage::getStoreConfig(self::CONFIG_XML_PATH_ALLOWED_ORDER_STATUSES, $storeId);
            if ($specific && $statuses){
                $this->_allowedOrderStatuses = explode(',', $statuses);
            }
        }
        return $this->_allowedOrderStatuses;
    }

    public function getAllowedOrderSupplyStatuses($storeId = null)
    {
        if (!isset($this->_allowedOrderSupplyStatuses)){
            $this->_allowedOrderSupplyStatuses = array();
            $specific = Mage::getStoreConfig(self::CONFIG_XML_PATH_SPECIFIC_ORDER_SUPPLY_STATUSES, $storeId);
            $statuses = Mage::getStoreConfig(self::CONFIG_XML_PATH_ALLOWED_ORDER_SUPPLY_STATUSES, $storeId);
            if ($specific && $statuses){
                $this->_allowedOrderSupplyStatuses = explode(',', $statuses);
            }
        }
        return $this->_allowedOrderSupplyStatuses;
    }
}
