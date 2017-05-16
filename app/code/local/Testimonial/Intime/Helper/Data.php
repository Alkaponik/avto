<?php

class Testimonial_Intime_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_CONFIG_PATH_MIN_UPDATE_TIMEOUT = 'carriers/intime/min_update_timeout';
    protected $_logFile = 'intime.log';

    /**
     * @param $string
     *
     * @return Testimonial_Intime_Helper_Data
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
        return Mage::getStoreConfig("carriers/intime/$key", $storeId);
    }

    public function getTitle($storeId = null)
    {
        return Mage::getStoreConfig("carriers/intime/title", $storeId);
    }

    public function getRedeliveryPaymentMethods($storeId = null)
    {
        return Mage::getStoreConfig("carriers/intime/payment_method", $storeId);
    }

    public function getPaymentType($storeId = null)
    {
        return Mage::getStoreConfig("carriers/intime/pay_type", $storeId);
    }

    public function getMinUpdateTimeout($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_PATH_MIN_UPDATE_TIMEOUT, $storeId);
    }

    public function getSenderCompany($storeId = null)
    {
        return Mage::getStoreConfig("carriers/intime/sender_company", $storeId);
    }

    public function getSenderAddress($storeId = null)
    {
        return Mage::getStoreConfig("carriers/intime/sender_address", $storeId);
    }

    public function getSenderContact($storeId = null)
    {
        return Mage::getStoreConfig("carriers/intime/sender_contact", $storeId);
    }

    public function getSenderPhone($storeId = null)
    {
        return Mage::getStoreConfig("carriers/intime/sender_phone", $storeId);
    }

    public function getPayer($storeId = null)
    {
        return Mage::getStoreConfig("carriers/intime/payer", $storeId);
    }

    public function getAdditionalInfo($storeId = null)
    {
        return Mage::getStoreConfig("carriers/intime/additional_info", $storeId);
    }

    public function getDocuments($storeId = null)
    {
        return Mage::getStoreConfig("carriers/intime/documents", $storeId);
    }

    public function getPackType($storeId = null)
    {
        return Mage::getStoreConfig("carriers/intime/pack_type", $storeId);
    }

    public function getDescription($storeId = null)
    {
        return Mage::getStoreConfig("carriers/intime/description", $storeId);
    }

    public function getFloorCount($storeId = null)
    {
        return Mage::getStoreConfig("carriers/intime/description", $storeId);
    }

    public function getSaturdayDelivery($storeId = null)
    {
        return Mage::getStoreConfig("carriers/intime/saturday", $storeId);
    }

    public function getSenderCity($storeId = null)
    {
        return Mage::getStoreConfig("carriers/intime/sender_city", $storeId);
    }

    public function getApiKey($storeId = null)
    {
        return Mage::getStoreConfig("carriers/intime/api_key", $storeId);
    }

    public function getApiUserId($storeId = null)
    {
        return Mage::getStoreConfig("carriers/intime/api_user_id", $storeId);
    }

    public function getApiUri($storeId = null)
    {
        return Mage::getStoreConfig("carriers/intime/api_url", $storeId);
    }
}
