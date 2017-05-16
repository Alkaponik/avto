<?php
class Testimonial_MageDoc_Model_Retailer_Config 
    extends Mage_Core_Model_Abstract
{
    const RETAILER_REQUEST_ADAPTERS_PATH = 'global/retailer_adapter/request';
    protected $_retailer = null;
    protected $_sourceParam;
    protected $_requestAdapter;
    protected $_currentDate;
    
    protected function _construct()
    {
        $this->_init('magedoc/retailer_config', 'retailer_id');
        $this->_currentDate = Mage::getModel('core/date')->date();
    }    
    
    public function getCurrentDate()
    {
        return $this->_currentDate;
    }
    

    public function getRetailer()
    {
        if(is_null($this->_retailer)) {
            $this->_retailer = Mage::getModel('magedoc/retailer')->load( $this->getRetailerId() );
        };
    }
    
    public function getLoginParamString()
    {
        if(!$this->hasData('login_param_string')){
            return '';
        }
        $dummyParamString = $this->getData('login_param_string');
        preg_match_all('/\{\{([^\}]+)\}\}/', $dummyParamString, $keyParamString);
        if (!empty($keyParamString)){
            for ($index = 0, $num = count($keyParamString[1]); $index < $num; $index++){
                $param = $this->getDataUsingMethod($keyParamString[1][$index]);
                $dummyParamString = str_replace('{{' . $keyParamString[1][$index] . '}}', $param, $dummyParamString);
            }
        }
        return $dummyParamString;
    }
    
    public function getSourceParamString()
    {
        $dummyParamString = $this->getData('source_param_string');
        $param = $this->getSourceParam();
        if(is_array($param)){
            for($i = 0; $i < count($param); $i++){
                $paramNumber = $i + 1;
                $dummyParamString = str_replace("{{param{$paramNumber}}}", $param[$i], $dummyParamString);
            }
            $paramString = $dummyParamString;
        }else{
            $paramString = str_replace('{{param}}', $param, $dummyParamString);
        }
        return $paramString;
    }

    public function getCheckParamString()
    {
        $dummyParamString = $this->getData('check_param_string');
        return '';
    }

    
    public function getCookieExpression()
    {        
        $expression = $this->getData('cookie_expression');
        preg_match_all('/\{\{([^\}]+)\}\}/', $expression, $keyParamArray);
        if (!empty($keyParamArray)){
            for ($index = 0, $num = count($keyParamArray[1]); $index < $num; $index++){
                $expression = str_replace("{{" . $keyParamArray[1][$index] . "}}", $this->getRetailer()->getSessionData()->getDataUsingMethod($keyParamArray[1][$index]), $expression);
            }
        }
        return $expression;
    }
    
    public function getUpdateRetailerModel()
    {
        return $this->getRetailer()->getUpdateModel();
    }
    
    public function getRequestAdapter()
    {
        if(!isset($this->_requestAdapter)){
            $name = $this->getRetailer()->getRequestAdapter();
            $model = (string) Mage::getConfig()->getNode(self::RETAILER_REQUEST_ADAPTERS_PATH . '/' . $name . "/class");            
            $this->_requestAdapter = Mage::getModel($model);
            $this->_requestAdapter->setConfig($this);
        }
        return $this->_requestAdapter;
    }
}
