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

abstract class Phoenix_SmsGateway_Model_Api_Abstract extends Mage_Core_Model_Abstract
{
    const XML_PATH_SMSGATEWAY = 'smsgateway/';

    protected $_code;
    protected $_requiredConfigValues;
    protected $_storeId = null;

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
     * Connects SMS Gateway and sends message
     *
     * @param string    sender mobile number
     * @param string    recipient mobile number
     * @param string    SMS message
     * @return bool
     */
    public function sendMessage($sender, $recipient, $message)
    {
        return true;
    }

    /**
     * Checks configuration for required values.
     *
     * @return bool
     */
    public function canSend()
    {
        if (is_array($this->_requiredConfigValues)) {
            foreach ($this->_requiredConfigValues as $value) {
                $val = $this->getConfigData($value);
                if (empty($val)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Convert mobile number to the gateway format
     *
     * @param string  Mobile number in format +491234567 or +49(123)4567
     * @param string  Converted number 00491234567
     */
    public function convertNumber($number)
    {
        return trim($number);
    }

    /**
     * Retrieve API code
     *
     * @return string
     */
    public function getCode()
    {
        if (empty($this->_code)) {
            Mage::throwException('Can not retrieve API code');
        }
        return $this->_code;
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
        return Mage::getStoreConfig(self::XML_PATH_SMSGATEWAY.$this->getCode().'/'.$field, $storeId);
    }
}