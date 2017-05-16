<?php

class Testimonial_SugarCRM_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CONFIG_XML_PATH_CUSTOMER_EXPORT_ENABLED = 'sugarcrm/general/customer_export_enabled';
    const CONFIG_XML_PATH_DEFAULT_USER_ID = 'bookkeeping/general/default_user_id';
    const CONFIG_XML_PATH_SUGARCRM_API_URL = 'sugarcrm/general/api_url';
    const CONFIG_XML_PATH_SUGARCRM_API_USER = 'sugarcrm/general/api_user';
    const CONFIG_XML_PATH_SUGARCRM_API_PASSWORD = 'sugarcrm/general/api_password';
    const CONFIG_XML_PATH_SUGARCRM_LOG_ENABLED = 'sugarcrm/general/log_enabled';
    const CONFIG_XML_PATH_DEFAULT_CALL_DURATION = 'sugarcrm/general/default_call_duration';
    const CONFIG_XML_PATH_DEFAULT_CALL_INTERVAL = 'sugarcrm/general/default_call_interval';
    const SOAP_WAIT_TIMEOUT = 10;

    protected $_user;
    protected $_soapClient;
    protected $_soapSessionId;


    public function isCustomerExportEnabled($store = null)
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_CUSTOMER_EXPORT_ENABLED, $store);
    }

    public function getSoapClient($trace = false, $store = null)
    {
        if (!isset($this->_soapClient)){
            $credentials = array(
                'user_name' => $this->getApiUser($store),
                'password'  => MD5($this->getApiPassword($store)),
                'version'   => '1'
            );

            $this->_soapClient = new SoapClient(/*$this->getApiWsdlUrl($store)*/null,
                array(
                    'trace' => true,
                    'location' => $this->getApiWsdlUrl($store),
                    'uri' => $this->getApiWsdlUrl($store)));

            $response = $this->_soapClient->login($credentials, base64_encode('magento_'.microtime()));

            $this->_soapSessionId = $response->id;
            if (empty($this->_soapSessionId)){
                Mage::throwException($this->__('Unable to connect SugarCRM API %s', $this->getApiWsdlUrl($store)));
            }
            /*$userGUID = $this->_soapClient->call('get_user_id', array(
                $this->_soapSessionId
            ));*/
        }
        return $this->_soapClient;
    }

    public function getSoapSessionId()
    {
        return $this->_soapSessionId;
    }

    public function getApiUrl($store = null)
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_SUGARCRM_API_URL, $store);
    }

    public function getApiUser($store = null)
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_SUGARCRM_API_USER, $store);
    }

    public function getApiPassword($store = null)
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_SUGARCRM_API_PASSWORD, $store);
    }

    public function getApiWsdlUrl($store = null)
    {
        return $this->getApiUrl($store) . 'soap.php?wsdl';
    }


    public function getDefaultUserId($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_DEFAULT_USER_ID, $storeId);
    }

    public function getDefaultCallDuration($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_DEFAULT_CALL_DURATION, $storeId);
    }

    public function getDefaultCallInterval($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_DEFAULT_CALL_INTERVAL, $storeId);
    }

    public function isLogEnabled($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_SUGARCRM_LOG_ENABLED, $storeId);
    }

    public function getCurrentUserId()
    {
        if (Mage::getSingleton('admin/session')->isLoggedIn()){
            return Mage::getSingleton('admin/session')->getUser()->getId();
        }
        return $this->getDefaultUserId();
    }

    public function getCurrentUser()
    {
        if (!isset($this->_user)){
            if (Mage::getSingleton('admin/session')->isLoggedIn()){
                $user = Mage::getSingleton('admin/session')->getUser();
            }else{
                $userId = $this->getCurrentUserId();
                $user = Mage::getSingleton('admin/user')->load($userId);
            }
            $this->_user = $user;
        }
        return $this->_user;
    }

    public function log($message)
    {
        if ($this->isLogEnabled()){
            Mage::log($message, null, 'sugarcrm.log');
        }
    }

    public function saveStaticAttributes($object, $attributes = array())
    {
        if (!is_array($attributes)){
            $attributes = array($attributes);
        }
        $values = array();
        foreach ($attributes as $attributeCode){
            $values[$attributeCode] = $object->getData($attributeCode);
        }

        $adapter = $object->getResource()->getWriteConnection();
        $tableName = $object->getResource() instanceof Mage_Eav_Model_Entity_Abstract
            ? $object->getResource()->getEntityTable()
            : $object->getResource()->getMainTable();

        $adapter->update($tableName, $values, $adapter->quoteInto('entity_id = ?', $object->getId()));
        return $this;
    }


    /*public function __destruct()
    {
        if (isset($this->_soapClient)){

        }
    }*/
}
