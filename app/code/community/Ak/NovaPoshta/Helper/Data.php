<?php
class Ak_NovaPoshta_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_CONFIG_PATH_MIN_UPDATE_TIMEOUT = 'carriers/novaposhta/min_update_timeout';
    protected $_logFile = 'novaposhta.log';

    /**
     * @param $string
     *
     * @return Ak_NovaPoshta_Helper_Data
     */
    public function log($string)
    {
        if ($this->getStoreConfig('enable_log')) {
            Mage::log($string, null, $this->_logFile);
        }
        return $this;
    }

    /**
     * @param string $key
     * @param null $storeId
     *
     * @return mixed
     */
    public function getStoreConfig($key, $storeId = null)
    {
        return Mage::getStoreConfig("carriers/novaposhta/$key", $storeId);
    }

    public function getRedeliveryPaymentMethods($storeId = null)
    {
        return Mage::getStoreConfig("carriers/novaposhta/payment_method", $storeId);
    }

    public function getTitle($storeId = null)
    {
        return Mage::getStoreConfig("carriers/novaposhta/title", $storeId);
    }

    public function getPaymentType($storeId = null)
    {
        return Mage::getStoreConfig("carriers/novaposhta/pay_type", $storeId);
    }

    public function getMinUpdateTimeout($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_PATH_MIN_UPDATE_TIMEOUT, $storeId);
    }

    public function getSenderCompany($storeId = null)
    {
        return Mage::getStoreConfig("carriers/novaposhta/sender_company", $storeId);
    }

    public function getSenderAddress($storeId = null)
    {
        return Mage::getStoreConfig("carriers/novaposhta/sender_address", $storeId);
    }

    public function getSenderContact($storeId = null)
    {
        return Mage::getStoreConfig("carriers/novaposhta/sender_contact", $storeId);
    }

    public function getSenderPhone($storeId = null)
    {
        return Mage::getStoreConfig("carriers/novaposhta/sender_phone", $storeId);
    }

    public function getPayer($storeId = null)
    {
        return Mage::getStoreConfig("carriers/novaposhta/payer", $storeId);
    }

    public function getAdditionalInfo($storeId = null)
    {
        return Mage::getStoreConfig("carriers/novaposhta/additional_info", $storeId);
    }

    public function getDocuments($storeId = null)
    {
        return Mage::getStoreConfig("carriers/novaposhta/documents", $storeId);
    }

    public function getPackType($storeId = null)
    {
        return Mage::getStoreConfig("carriers/novaposhta/pack_type", $storeId);
    }

    public function getDescription($storeId = null)
    {
        return Mage::getStoreConfig("carriers/novaposhta/description", $storeId);
    }

    public function getFloorCount($storeId = null)
    {
        return Mage::getStoreConfig("carriers/novaposhta/floor_count", $storeId);
    }

    public function getSaturdayDelivery($storeId = null)
    {
        return Mage::getStoreConfig("carriers/novaposhta/saturday", $storeId);
    }

    public function getSenderCity($storeId = null)
    {
        return Mage::getStoreConfig("carriers/novaposhta/sender_city", $storeId);
    }

    public function getFreeShipmentAmount($storeId = null)
    {
        return Mage::getStoreConfig("carriers/novaposhta/free_shipment_amount", $storeId);
    }

    public function getFreeShipmentMethod($storeId = null)
    {
        return Mage::getStoreConfig("carriers/novaposhta/free_shiping_payment_method", $storeId);
    }
}