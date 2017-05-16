<?php

abstract class Testimonial_MageDoc_Block_Adminhtml_Widget_Chooser extends Mage_Adminhtml_Block_Template
{
    protected $_comboboxes = array();
    protected $_containerId;
    
    public function _construct()
    {
        $this->setTemplate('magedoc/widget/chooser.phtml');
    }

    public function getContainerId()
    {
        if(!isset($this->_containerId)){
            $this->_containerId = $this->getId();
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
        $combobox = $this->getLayout()->createBlock('magedoc/adminhtml_widget_combobox');
        $combobox->setData($options);
        $combobox->setId($comboId);
        $this->_comboboxes[$comboId]=$combobox;
        return $this;
    }
    
    protected function _beforeToHtml() 
    {
        $this->prepareCombos();
        parent::_beforeToHtml();
    }

    /**
     * @todo   Make this method abstract
     * @return Testimonial_MageDoc_Block_Adminhtml_Widget_Chooser
     */

    abstract public function prepareCombos();

    public function getComboboxes()
    {
        return $this->_comboboxes;
    }
    
    public function setComboboxes(array $comboboxes)
    {
        foreach($comboboxes as $combobox){
            if($combobox instanceof Testimonial_MageDoc_Block_Adminhtml_Widget_Combobox){
                $this->addCombobox($combobox->getId(), $combobox->getData());
            }
        }
        return $this;
    }
    
    public function clearComboboxes()
    {
        $this->_comboboxes = array();
    }

    public function getCurrentVehicleData()
    {
        $data = array();
        $vehicle = $this->getVehicle();
        foreach($this->getComboboxes() as $combo){
            if($vehicle->getTypeId() !== null || $combo->getIsDefault()){
                $data['select_'.$combo->getName()] = $combo->getOptions();
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
        return $this->getUrl('magedoc/adminhtml_order/request/');
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }
}
