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

class Phoenix_SmsGateway_Model_Api_Hostserver extends Phoenix_SmsGateway_Model_Api_Abstract
{
	protected $_code = 'hostserver';
    protected $_requiredConfigValues = array('account', 'password', 'url');

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
        try {
            $parameter = $this->_prepareParameter($sender, $recipient, $message);

            // send message
            $client = new Varien_Http_Client();
            $client->setUri($this->getConfigData('url'))
                ->setConfig(array('timeout' => 30))
                ->setParameterGet($parameter)
                ->setMethod(Zend_Http_Client::GET);
            $response = $client->request();

            // verify transmission
            if (!$response->isSuccessful()) {
                Mage::throwException('Unknown transmission error');
            }

            return true;

        } catch (Exception $e) {
            Mage::throwException('Message transmission was not successfull:');
        }
    }

    protected function _prepareParameter($sender, $recipient, $message)
    {
        // prepare parameters
        $parameter = array(
                        'user'      =>  $this->getConfigData('account'),
                        'pass'      =>  $this->getConfigData('password'),
                        'text'      =>  $message,
                        'to'        =>  $this->convertNumber($recipient),
                        'split'    =>  'true',
                     );
        return $parameter;
    }

    /**
     * Convert mobile number to the gateway format
     *
     * @param string  Mobile number in format +491234567 or +49(123)4567
     * @param string  Converted number 491234567
     */
    public function convertNumber($number)
    {
        // remove leading plus sign
        if (substr($number, 0, 1) == '+') {
            $number = substr($number, 1);
        }
        // remove foreign country prefix
        if (substr($number, 0, 2) == '00') {
            $number = substr($number, 2);
        }
        // remove non numerical characters
        $number = preg_replace('/[^0-9]/', '', $number);
        return trim($number);
    }
}