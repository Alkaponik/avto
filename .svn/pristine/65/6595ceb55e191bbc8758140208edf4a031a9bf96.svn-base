<?php

abstract class Testimonial_MageDoc_Model_Retailer_Data_Update_Abstract
    extends Mage_Core_Model_Abstract
{
    protected $_retailer;
    protected $_config;
    protected $_codeNormalizedExpression = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE({code},' ', ''), '+', ''), '.', ''), '-', ''), '=', ''), '\\\\', ''), '/', ''), '\\'', ''), '\"', ''), ')', ''), '(', ''), ']', ''), '[', '')";
    protected $_codeExpression = '';

    public function __construct()
    {
        parent::__construct();
        $this->_codeNormalizedExpression = str_replace('{code}', $this->_codeExpression, $this->_codeNormalizedExpression);
    }

    public function requestProductData($artId)
    {
        if($param = $this->_getSourceParam($artId)){
            $this->getConfig()->setSourceParam($param);
            $data = $this->getConfig()->getRequestAdapter()->getRequestData();
            if(!$this->_checkValidProductResponse($data)){
                $result = $this->getConfig()->getRequestAdapter()->auth();
                $this->_processAuthResponse($result);
                $data = $this->getConfig()->getRequestAdapter()->getRequestData();
                if(!$this->_checkValidProductResponse($data)){
                    return false;
                }
            }
            $productsData = $this->_processProductResponse($data);
            return (bool)$this->_insertData($productsData);
        }
        return false;
    }

    public function setRetailer(Testimonial_MageDoc_Model_Retailer $retailer)
    {
        return $this->_retailer = $retailer;
    }

    public function getRetailer()
    {
        return $this->_retailer;
    }

    public function getConfig()
    {
        if(!isset($this->_config)){
            $this->_config = $this->getRetailer()->getConfig();
        }
        return $this->_config;
    }

    public function setConfig(Testimonial_MageDoc_Model_Retailer_Config $config)
    {
        $this->_config = $config;
        return $this;
    }

    protected function _insertData($productsData)
    {
        $importTable = Mage::getResourceModel('magedoc/import_retailer_data')->getMainTable();
        $result = 0;
        if ($productsData) {
            $result = Mage::getSingleton('core/resource')->getConnection('write')->insertOnDuplicate(
                $importTable,
                $productsData,
                array(
                    'cost',
                    'price',
                    'domestic_stock_qty',
                    'general_stock_qty',
                    'qty',
                    'updated_at'
                ));
        }
        return $result;
    }

    public function getItemImportRetailerData($artId, $retailerId = NULL)
    {
        if (is_null($retailerId)){
            $retailerId = $this->getRetailer()->getId();
        }
        $collection = Mage::getResourceModel('magedoc/import_retailer_data_collection')
            ->addFieldToFilter('td_art_id', array('eq' => $artId))
            ->addFieldToFilter('retailer_id', array('eq' => $retailerId));
        return $collection->fetchItem();
    }

    abstract protected function _getSourceParam($artId);
    abstract protected function _processProductResponse($response);
    abstract protected function _checkValidProductResponse($response);
    abstract protected function _processAuthResponse($response);
}