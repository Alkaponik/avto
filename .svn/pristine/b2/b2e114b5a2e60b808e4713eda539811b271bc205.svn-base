<?php

class Testimonial_MageDoc_Block_Adminhtml_Form_Element_Combobox extends Varien_Data_Form_Element_Abstract
{
    public function __construct($attributes=array())
    {
        Varien_Data_Form_Abstract::__construct($attributes);
        $this->_renderer = Mage::app()->getLayout()->createBlock('magedoc/adminhtml_widget_combobox');
        $this->_renderer->setData($attributes);
        $this->_renderer->setTemplate('magedoc/widget/form/element/combobox.phtml');  
        $this->setType('combobox');
    }    
    
    public function setValue($value)
    {
        $this->getRenderer()->setValue($value);
        return $this;
    }
    
    public function setTextValue($value)
    {
        $this->getRenderer()->setTextValue($value);
        return $this;        
    }
    
    public function setId($id) 
    {
        $this->_renderer->setId($id);
        return parent::setId($id);
    }
}
