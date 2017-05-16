<?php

class Testimonial_MageDoc_Block_Catalog_Layer_Filter_Type extends Mage_Catalog_Block_Layer_Filter_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('magedoc/catalog/layer/filter/vehicle.phtml');
        $this->_filterModelName = 'magedoc/catalog_layer_filter_type';
    }

    /**
     * Initialize filter model object
     *
     * @return Mage_Catalog_Block_Layer_Filter_Abstract
     */
    public function init()
    {
        $this->_initFilter();
        $this->_initSessionVehicleHistory();
        return $this;
    }
    
    protected function _initSessionVehicleHistory()
    {
        $postVehicleData = $this->getRequest()->getParam($this->_filter->getRequestVar(), false);
        $session = Mage::getModel('customer/session');
        if($postVehicleData){
            if($session->getCustomerVehicle()){
                if(is_array($session->getCustomerVehicle())){
                    if(!in_array($postVehicleData, $session->getCustomerVehicle())){
                        $session->setCustomerVehicle($postVehicleData . ',' . $session->getCustomerVehicle());
                    }
                }else{
                    if(strpos($session->getCustomerVehicle(), (string)$postVehicleData) === false){
                       $session->setCustomerVehicle($postVehicleData . ',' . $session->getCustomerVehicle());
                    }            
                }
            }else{
                $session->setCustomerVehicle($postVehicleData);
            }
            if($session->isLoggedIn() 
                && Mage::helper('magedoc')->getCustomerVehicleSaveRule() == 'new_choice'
            ){
                if(strpos($session->getCustomer()->getVehicle(), (string)$postVehicleData) === false){
                    $session->getCustomer()->setVehicle(
                        $session->getCustomer()->getVehicle() . ',' . $postVehicleData)
                        ->save();
                }
                $session->unsetData('customer_vehicle');
            }
        }
        return $this;
    }
    
    
    public function getChooserHtml()
    {
        return $this->getLayout()->createBlock('magedoc/catalog_layer_chooser')->toHtml();
    }
    
    public function getFilterParams()
    {
        return $this->getRequest()->getQuery();
    }
    
    public function getCurrentCategoryUrl()
    {
        $query = array(
            $this->_filter->getRequestVar()=>null,
            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
        );
        $params['_current']     = true;
        $params['_use_rewrite'] = true;
        $params['_query']       = $query;
        $params['_escape']      = true;
        return Mage::getUrl('*/*/*', $params);
    }
}
