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

class Phoenix_SmsGateway_Model_Api_Alphasms extends Phoenix_SmsGateway_Model_Api_Abstract
{
	protected $_code = 'alphasms';
    protected $_requiredConfigValues = array('key', 'url', 'from');

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
            Mage::throwException(Mage::helper('smsgateway')->__('Message transmission was not successfull: %s', $e->getMessage()));
        }
    }

    protected function _prepareParameter($sender, $recipient, $message)
    {
        // prepare parameters
        $parameter = array(
                        'version'   =>  'http',
                        //'login'     =>  $this->getConfigData('account'),
                        //'pass'      =>  $this->getConfigData('password'),
                        'key'      =>  $this->getConfigData('key'),
                        'from'      =>  $this->getConfigData('from'),
                        'to'        =>  $this->convertNumber($recipient),
                        'message'   =>  $message
                     );
        return $parameter;
    }

    protected function _verifyTransmission($response)
    {
        $resLines = explode("\r\n", $response);
        $hlp = Mage::helper('smsgateway');
        if (empty($resLines)) {
            Mage::throwException($hlp->__('Result unknown.'));
        }

        foreach ($resLines as $line){
            $message = explode(':', $line);
            switch ($message[0]) {
                case 'id':
                    // everything okay
                    break;
                case 'errors':
                    Mage::throwException($hlp->__('Error: %s', $message[1]));
                    break;
                default:
                    Mage::throwException($hlp->__('Unknown error :%s', $line));
            }
        }
    }
}