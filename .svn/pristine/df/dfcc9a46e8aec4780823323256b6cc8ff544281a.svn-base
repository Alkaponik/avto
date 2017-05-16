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

class Phoenix_SmsGateway_Model_Api_Turbosms extends Phoenix_SmsGateway_Model_Api_Abstract
{
	protected $_code = 'turbosms';
    protected $_requiredConfigValues = array('login', 'password', 'wsdl', 'from');

    /**
     * Debug mode
     *
     * @var bool
     */
    protected $_debug = false;
    
    /**
     * @var SoapClient
     */
    protected $_client;

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
            $params = $this->_prepareParameters($sender, $recipient, $message);
            // send message
            $result = $this->getClient()->SendSMS($params);
            $this->_verifyTransmission($result);
            return true;

        } catch (Exception $e) {
            Mage::throwException(Mage::helper('smsgateway')->__('Message transmission was not successfull: %s', $e->getMessage()));
        }
    }

    protected function _prepareParameters($sender, $recipient, $message)
    {
        $parameters = array(
            'sender'        =>  $this->getConfigData('from'),
            'destination'   =>  $this->convertNumber($recipient),
            'text'          =>  $message
            );
        return $parameters;
    }

    public function convertNumber($number)
    {
        $number = trim($number);
        return strlen($number) == 10
            ? '+38'.$number
            : $number;
    }

    protected function _verifyTransmission($result)
    {
        $hlp = Mage::helper('smsgateway');
        if (empty($result->SendSMSResult->ResultArray[0])){
            Mage::throwException($hlp->__('Unknown error occured while sending SMS using Turboseo API'));
        }
        if ($result->SendSMSResult->ResultArray[0] != 'Сообщения успешно отправлены') {
            Mage::throwException($hlp->__($result->SendSMSResult->ResultArray[0]));
        }
    }


    /**
     * Connetc to Turbosms by Soap
     *
     * @return SoapClient
     * @throws InvalidConfigException
     */
    protected function connect()
    {
        $hlp = Mage::helper('smsgateway');
        if ($this->_client) {
            return $this->_client;
        }
        if (!($wsdl = $this->getConfigData('wsdl'))){
            Mage::throwException($hlp->__('Please WSDL url to connect Turbosms API'));
        }
        $client = new SoapClient($wsdl);
        if (!($login = $this->getConfigData('login'))
            || !($password = $this->getConfigData('password'))) {
            Mage::throwException($hlp->__('Please specify login and password from Turbosms API'));
        }
        $result = $client->Auth(array(
            'login' => $login,
            'password' => $password,
        ));
        if (mb_strpos((string)$result->AuthResult, 'Вы успешно авторизировались', null, 'UTF-8') !== 0) {
            Mage::throwException($hlp->__((string)$result->AuthResult));
        }
        $this->_client = $client;
        return $this->_client;
    }
    
    /**
     * Get balance
     *
     * @return int
     */
    public function getBalance()
    {
        return $this->_debug ? 0 : intval($this->getClient()->GetCreditBalance()->GetCreditBalanceResult);
    }

    /**
     * Get message status
     *
     * @param $messageId
     *
     * @return string
     */
    public function getMessageStatus($messageId)
    {
        if ($this->_debug || !$messageId) {
            return'';
        }
        $result = $this->getClient()->GetMessageStatus(['MessageId' => $messageId]);
        return $result->GetMessageStatusResult;
    }

    /**
     * Get Soap client
     *
     * @return SoapClient
     * @throws InvalidConfigException
     */
    protected function getClient()
    {
        if (!$this->_client) {
            return $this->connect();
        }
        return $this->_client;
    }

}