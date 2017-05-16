<?php

class Testimonial_MageDoc_Block_Vehicle extends Mage_Core_Block_Template
{
    protected $_choosers = array();
    protected $_chooserBlockTemplate;
    protected $_customerVehicles = array();
    protected $_submitButtonId;
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getSubmitButtonId()
    {
        if(!isset($this->_submitButtonId)){
            $this->_submitButtonId = Mage::helper('core')->uniqHash('id_');
        }
        return $this->_submitButtonId;
    }

    public function addChooser($chooserId, $vehicle = null)
    {
        $chooser = $this->getLayout()->createBlock('magedoc/vehicle_chooser');
        $chooser->setId($chooserId);

        if (!is_null($vehicle)) {
            $chooser->setCustomerVehicle($vehicle);
        }
        $this->_choosers[$chooserId] = $chooser;
        
        return $this;        
        
    }
    
    protected function _beforeToHtml() 
    {    
        $this->_prepareChoosers();
        parent::_beforeToHtml();
    }

    public function getCustomerVehicles()
    {
        if(empty($this->_customerVehicles)){
            if(Mage::registry('customer')->getVehiclesCollection() !== null){
                $this->_customerVehicles = Mage::registry('customer')->getVehiclesCollection();
            }
        }
        return $this->_customerVehicles;
    }
    
    protected function _prepareChoosers()
    {
        $hasType = false;
        foreach($this->getCustomerVehicles() as $vehicle){
            $this->addChooser($vehicle->getId(), $vehicle);
            $hasType = true;
        }
        if (!$hasType){
            $this->addChooser('my_vehicle');
        }
    }

    public function getChoosers()
    {
        return $this->_choosers;
    }
   
    public function getChooserBlockTemplate()
    {
        if (!isset($this->_chooserBlockTemplate)){
            $this->_chooserBlockTemplate = Mage::getBlockSingleton('magedoc/vehicle_chooser')
                    ->prepareCombos();
                    $this->_chooserBlockTemplate->setContainerId('#{_container_id}')
                    ->setTemplate('magedoc/vehicle/dummy.phtml');
        }
        return $this->_chooserBlockTemplate;
    }
}
