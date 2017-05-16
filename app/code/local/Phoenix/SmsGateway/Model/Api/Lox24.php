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

class Phoenix_SmsGateway_Model_Api_Lox24 extends Phoenix_SmsGateway_Model_Api_Abstract
{
	protected $_code = 'lox24';
    protected $_requiredConfigValues = array('account', 'password', 'service', 'url');

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
                ->setHeaders('accept-encoding', '')
                ->setParameterPost($parameter)
                ->setMethod(Zend_Http_Client::POST);
            $response = $client->request();

            // verify transmission
            $result = $response->getRawBody();
            $this->_verifyTransmission($result);
            return true;

        } catch (Exception $e) {
            Mage::throwException('Message transmission was not successfull:'.$e->getMessage());
        }
    }

    protected function _prepareParameter($sender, $recipient, $message)
    {
        // prepare parameters
        $parameter = array(
                        'konto'     =>  $this->getConfigData('account'),
                        'password'  =>  md5($this->getConfigData('password')),
                        'service'   =>  $this->getConfigData('service'),
                        'text'      =>  $message,
                        'from'      =>  $this->convertNumber($sender),
                        'to'        =>  $this->convertNumber($recipient),
                        'timestamp' =>  0,
                        'return'    =>  'text',
                        'httphead'  =>  0,
                        'action'    =>  'send',
                     );
        return $parameter;
    }

    protected function _verifyTransmission($response)
    {
        $resLines = explode("\r\n", $response);
        if (empty($resLines)) {
            Mage::throwException('Result unknown.');
        }

        switch (substr($resLines[0],0,1)) {
            case '1':
                // everything okay
                break;
            case '2':
                Mage::throwException('Input error '.$resLines[0].': '.$resLines[1]);
                break;
            case '3':
                Mage::throwException('System error '.$resLines[0].': '.$resLines[1]);
                break;
            default:
                Mage::throwException('Unknown error '.$resLines[0].': '.$resLines[1]);
        }

    }
}