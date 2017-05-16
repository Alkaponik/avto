<?php
/**
 * Phoenix SMS Gateway
 *
 * NOTICE OF LICENSE
 * 
 * This source file is subject to license that is bundled with
 * this package in the file LICENSE.txt.
 *
 * @category   Phoenix
 * @package    Phoenix_SmsGateway
 * @copyright  Copyright (c) 2009 by Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 */

class Phoenix_SmsGateway_Model_Api extends Mage_Core_Model_Abstract
{
    const XML_PATH_SMSGATEWAY_COMMON = 'smsgateway/common/';

    /**
     * Current API model
     * @var Phoenix_SmsGateway_Model_Api_Abstract
     */
    protected $_api = array();
    protected $_storeId = Mage_Core_Model_App::ADMIN_STORE_ID;

    /**
     * @param $storeId mixed
     * @return $this Phoenix_SmsGateway_Model_Api
     */

    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }


    /**
     * Sends SMS via the configured API.
     *
     * @param   string  160 characters or more (splited automaticly)
     * @param   string/array    mobil number of recipients
     */
    public function sendMessage($message, $recipients, $storeId = null)
    {
        try {
                // check that module is enabled
            if (!$this->isEnabled($storeId)) {
                Mage::throwException('Disabled by configuration.');
            }

                // convert recipient into array
            if (!is_array($recipients)) {
                $recipients = array($recipients);
            }

                // check gateway and configuration
            if (!$this->getApi($storeId)->canSend()) {
                Mage::throwException('Gateway is not configured properly.');
            }

                // send SMS for each recipient
            $sender = $this->getConfigData('sender');
            foreach ($recipients as $recipient) {
                $this->getApi($storeId)->sendMessage($sender, $recipient, $message);
            }

        } catch (Mage_Core_Exception $e) {
            if (Mage::getStoreConfigFlag(self::XML_PATH_SMSGATEWAY_COMMON.'debug')) {
                Mage::log('Exception: '.$e->getMessage(), null, 'sms_gateway.log');
            }
            return false;
        }

        return true;
    }

    /**
     * Creates API model for gateway
     *
     * @return Phoenix_SmsGateway_Model_Api_Abstract
     */
    protected function getApi($storeId = null)
    {
        if (is_null($storeId)){
            $storeId = $this->_storeId;
        }
        if (!isset($this->_api[$storeId])) {
            $api = Mage::getModel('smsgateway/api_'.strtolower($this->getConfigData('gateway', $storeId)));
            $api->setStoreId($storeId);
            if (!$api) {
                Mage::throwException('Gateway "'.$this->getConfigData('gateway', $storeId).'" not found.');
            }
            $this->_api[$storeId] = $api;
        }
        return $this->_api[$storeId];
    }

    /**
     * Check if SMS Gateway is enabled in configuration.
     *
     * @param int $storeId
     * @return bool
     */
    protected function isEnabled($storeId = null)
    {
        if (!Mage::getStoreConfigFlag(self::XML_PATH_SMSGATEWAY_COMMON.'enabled', $storeId)) {
            return false;
        }
        return true;
    }

    /**
     * Retrieve information from gateway configuration
     *
     * @param   string $field
     * @return  mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        if (is_null($storeId)){
            $storeId = $this->_storeId;
        }
        return Mage::getStoreConfig(self::XML_PATH_SMSGATEWAY_COMMON.$field, $storeId);
    }
}