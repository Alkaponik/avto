<?php

class Testimonial_MageDoc_Block_Vehicle_Chooser extends Mage_Core_Block_Template       
{
    protected $_vehicle;
    protected $_comboboxes = array();
    protected $_containerId = null;
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('magedoc/vehicle/chooser.phtml');
    }
    
    /**
     *
     * @return Testimonial_MageDoc_Model_Type
     */
    public function getMagedocType()
    {
        if (!$this->hasData('magedoc_type')){
            $this->setMagedocType(Mage::getModel('magedoc/tecdoc_type'));
        }
        return $this->getData('magedoc_type');
    }

    public function getCustomerVehicle()
    {
        if (!$this->hasData('customer_vehicle')){
            $this->setCustomerVehicle(Mage::getModel('magedoc/customer_vehicle'));
        }
        return $this->getData('customer_vehicle');
    }

    public function getCustomerVehicleVin()
    {
        return $this->getCustomerVehicle()->getVin();
    }

    public function getCustomerVehicleMileage()
    {
        return $this->getCustomerVehicle()->getMileage();
    }

    public function getCustomerVehicleId()
    {
        return $this->getCustomerVehicle()->getId();
    }
    
    public function getContainerId()
    {
        if(is_null($this->_containerId)){
            if($this->getCustomerVehicle()->getId()){
                $this->_containerId = $this->getCustomerVehicle()->getId();
            }else{
                $this->_containerId = Mage::helper('core')->uniqHash('id_');
            }
        }
        return $this->_containerId;
    }
    
    public function setContainerId($containerId)
    {
        $this->_containerId = $containerId;
        return $this;
    }
    
    
    public function addCombobox($comboId, $options = array())
    {
        $combobox = $this->getLayout()->createBlock('magedoc/vehicle_combobox');
        $combobox->setData($options);
        $combobox->setChooser($this);
        $combobox->setId($comboId);
        $this->_comboboxes[$comboId]=$combobox;
        return $this;
    }
    
    protected function _beforeToHtml() 
    {
        $this->prepareCombos();
        parent::_beforeToHtml();
    }

    public function prepareCombos()
    {
        $this->setFieldNameSuffix("vehicle[{$this->getContainerId()}]");
        $this->setHtmlIdPrefix("vehicle_{$this->getContainerId()}_");
        $customerVehicle = $this->getCustomerVehicle();
        
        $this->addCombobox('manufacturer', array(
            'name'          => 'manufacturer',
            'title'         => $this->__('Manufacturer'),
            'default_text'  => $customerVehicle->getManufacturer(),
            'options'       => Mage::getModel('magedoc/source_manufacturer')
                                        ->setEnabledFilter(true)
                                        ->getExtendedOptionArray('sort_order'),
            'is_default'    => true,
            'placeholder'   => $this->__('Choose manufacturer')
        ));

        $disableDate = $customerVehicle->getProductionStartYear()? '' : 'disabled';
        $this->addCombobox('production_start_year', array(
            'name'          => 'production_start_year',
            'disabled'      => $disableDate,
            'options'       => Mage::getModel('magedoc/source_date')->getOptionArray(),
            'title'         => $this->__('Date'),
            'default_text'  => $customerVehicle->getProductionStartYear(),
            'placeholder'   => $this->__('Choose date')

        ));

        if(($customerVehicle->getProductionStartYear() !== null) && ($customerVehicle->getManufacturerId() !== null)) {
           $options = Mage::getModel('magedoc/source_model')
                    ->setYearStart($customerVehicle->getProductionStartYear())
                    ->setManufacturerId($customerVehicle->getManufacturerId())
                    ->getOptionArray();
        }else{
           $options = array();
        }

        $disableModel = $customerVehicle->getModel()? '' : 'disabled';
        $this->addCombobox('model', array(
            'name'          => 'model',
            'disabled'      => $disableModel,
            'options'       => $options,
            'title'         => $this->__('Model'),
            'default_text'  => $customerVehicle->getModel(),
            'placeholder'   => $this->__('Choose model')
        ));

        if($customerVehicle->getModelId() !== null) {
            $options = Mage::getModel('magedoc/source_type')
                    ->setModelId($customerVehicle->getModelId())
                    ->getOptionArray();

        }else{
           $options = array();
        }

        $disableType = $customerVehicle->getType()? '' : 'disabled';
        $this->addCombobox('type', array(
            'name'          => 'type',
            'disabled'      => $disableType,
            'options'       => $options,
            'title'         => $this->__('Type'),
            'default_text'  => $customerVehicle->getType(),
            'required'      => true,
            'placeholder'   => $this->__('Choose type')
        ));

        return $this;
    }
    
    public function getComboboxes()
    {
        return $this->_comboboxes;
    }
    
    public function setComboboxes(array $comboboxes)
    {
        foreach($comboboxes as $combobox){
            if($combobox instanceof Testimonial_MageDoc_Block_Vehicle_Combobox){
                $this->addCombobox($combobox->getId(), $combobox->getData());
            }
        }
        return $this;
    }
    
    public function clearComboboxes()
    {
        $this->_comboboxes = array();
    }
    
    public function getCurrentValuesJson()
    {
        $values = array();
        $customerVehicle = $this->getCustomerVehicle();
        if($customerVehicle->getId()){
            $values = array('manufacturer' => $customerVehicle->getManufacturerId(),
                    'production_start_year' => $customerVehicle->getProductionStartYear(),
                    'model' => $customerVehicle->getModelId(),
                    'type' => $customerVehicle->getTypeId()
                    );
        }
        
        return Mage::helper('core')->jsonEncode($values);
    }
    
    public function getCurrentVehicleData()
    {
        $data = array();
        $customerVehicle = $this->getCustomerVehicle();
        foreach($this->getComboboxes() as $combo){
            if($customerVehicle->getId() !== null || $combo->getIsDefault()){
                $data[$combo->getName()] = $combo->getOptions();
            }
        }
        
        return $data;
    }
    
    public function getControlsJson()
    {
        return Mage::helper('core')->jsonEncode($this->getCurrentVehicleData());
    }
        
    public function getJavasctiptObjectName()
    {
        return "chooser_" . $this->getId();   
    }
    
    public function getEditUrl()
    {
        return $this->getUrl('magedoc/vehicle/request/');
    }

    public function addSuffixToName($name, $suffix)
    {
        if (!$name) {
            return $suffix;
        }
        $vars = explode('[', $name);
        $newName = $suffix;
        foreach ($vars as $index=>$value) {
            $newName.= '['.$value;
            if ($index==0) {
                $newName.= ']';
            }
        }
        return $newName;
    }
}