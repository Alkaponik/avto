<?php

class Testimonial_MageDoc_Block_Vehicle_Combobox extends Mage_Core_Block_Template       
{
    protected $_containerId;
    
    public function __construct() 
    {
        parent::__construct();
        
        $this->setTemplate('magedoc/vehicle/combobox.phtml');
    }

    public function setId($id)
    {
        parent::setId($id);
        $this->setData('html_id', $id);
        return $this;
    }

    public function getId()
    {
        if ($this->getData('id') === null) {
            $this->setData('id', Mage::helper('core')->uniqHash('id_'));
        }
        return $this->getData('id');
    }

    public function getContainerId()
    {
        if($this->getChooser() && $this->getChooser()->getHtmlIdPrefix()){
            return $this->getChooser()->getHtmlIdPrefix().
            $this->getData('html_id') .
            $this->getChooser()->getHtmlIdSuffix();
        }
        if($this->getData('container_id') === null){
            $this->setContainerId($this->getId());
        }

        return $this->getData('container_id');
    }
        
    public function getTextValue()
    {
        return $this->getDefaultText();
    }
    
    public function getSelectSize()
    {
        if($this->getData('select_size') === null){
            $this->setSelectSize(Mage::helper('magedoc')->getDefaultComboboxSelectSize());
        }
        return $this->getData('select_size');
    }

    public function getDisabled()
    {
        if(!$this->hasData('disabled')){
            $this->setDisabled('');
        }
        return $this->getData('disabled') == 'disabled' 
                            ? 'disabled="disabled"' : '';
    }

    public function getName($name = null)
    {
        if (is_null($name)){
            $name = $this->getData('name');
        }
        if ($this->getChooser()
            && ($suffix = $this->getChooser()->getFieldNameSuffix())) {
            $name = $this->getChooser()->addSuffixToName($name, $suffix);
        }
        return $name;
    }

    public function getInputName()
    {
        return $this->getName();
    }

    public function getSelectName()
    {
        return $this->getName($this->getData('name').'_id');
    }
}
