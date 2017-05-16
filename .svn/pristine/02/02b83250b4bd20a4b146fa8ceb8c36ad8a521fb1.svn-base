<?php

class Testimonial_MageDoc_Block_Adminhtml_Widget_Combobox 
    extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element
{
    protected $_containerId;
    
    public function __construct() 
    {
        parent::__construct();
        
        $this->setTemplate('magedoc/widget/combobox.phtml');
    }
    
    public function prepareElementName($vehicleId = null)
    {
        if(is_null($vehicleId)){
            $vehicleId = '#{_vehicle_id}';
        }
        $this->setInputName("inquiry[{$vehicleId}][vehicle][{$this->getName()}]");
        $this->setSelectName("inquiry[{$vehicleId}][vehicle][{$this->getName()}_id]");
        
        return $this;
    }

    
    
    public function getContainerId()
    {
        if($this->getData('container_id') === null){
            $this->setContainerId($this->getId());
        }

        return $this->getData('container_id');
    }
        
    public function getValues()
    {
        if ($this->hasData('options')){
            return $this->getData('options');
        }elseif(!$this->hasData('values')){
            return array();
        }
        $options = $this->getData('values');
        $optionArray = array();
        foreach($options as $option){
            if(!is_array($option)){
                return $options;
            }
            $optionArray[$option['value']] = $option['label'];
        }
        return $optionArray;
    }

    public function getDisabled()
    {
        if($this->getData('disabled')){
            return 'disabled="disabled"';
        }
        return '';
    }

    public function getTextValue()
    {
        if(!$this->hasData('text_value')){
            return $this->getDefaultText();
        }
            return $this->getData('text_value');
    }
       
    public function getInputName()
    {
        if(!$this->hasData('input_name')){
            $this->setInputName("text_{$this->getName()}");
        }
        
        return $this->getData('input_name');
    }

    public function getSelectName()
    {
        if(!$this->hasData('select_name')){
            $this->setSelectName("{$this->getName()}");
        }
        
        return $this->getData('select_name');
    }

    
    public function getSelectSize()
    {
        if($this->getData('select_size') === null){
            $this->setSelectSize(Mage::helper('magedoc')->getDefaultComboboxSelectSize());
        }
        return $this->getData('select_size');
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this->toHtml();
    }
   
    public function getWithJs()
    {
        if(!$this->hasData('with_js')){
            return false;
        }
        return $this->getData('with_js');
    }

    public function getHtmlAttributes()
    {
        return array('style', 'onclick', 'onchange', 'disabled', 'readonly', 'tabindex');
    }

    public function getChangeCallback()
    {
        return $this->hasData('change_callback')
            ? $this->getData('change_callback')
            : "''";
    }

    public function getInputRequired()
    {
        return $this->hasData('input_required')
            ? $this->getData('input_required')
            : $this->getRequired();
    }

    public function getSelectRequired()
    {
        return $this->hasData('select_required')
            ? $this->getData('select_required')
            : $this->getRequired();
    }
}