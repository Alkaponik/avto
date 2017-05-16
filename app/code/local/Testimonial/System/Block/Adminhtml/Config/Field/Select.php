<?php

class Testimonial_System_Block_Adminhtml_Config_Field_Select extends Mage_Core_Block_Abstract
{
    protected $_renderer;
    protected $_types = array();

    /*public function __construct($attributes=array())
    {

    }*/

    /*public function __construct($attributes=array())
    {
        return;
        parent::__construct($attributes);
        $this->setType('select');
        $this->setExtType('combobox');
        $this->_prepareOptions();
    }*/
    protected function _toHtml()
    {
        return preg_replace('/[\r\n]/', '', $this->jsQuoteEscape($this->getRenderer()->getElementHtml()));
    }

    public function getRenderer()
    {
        if (!isset($this->_renderer)){
            $config = $this->getColumn();
            $elementId = $this->getColumnName();
            $type = $config['type'];
            if (isset($this->_types[$type])) {
                $className = $this->_types[$type];
            } else {
                $className = 'Varien_Data_Form_Element_' . ucfirst(strtolower($type));
            }
            $config['name'] = $this->getInputName();
            $suffix = $this->getForm()->getFieldNameSuffix();
            if (strpos($config['name'], $suffix) === 0){
                $config['name'] = substr($config['name'], strlen($suffix)+1);
                $config['name'] = substr_replace($config['name'], '', strpos($config['name'], ']'), 1);
            }
            $element = new $className($config);
            $element->setId($elementId);
            $element->setForm($this->getForm());
            $element->setValue("#{' . $elementId . '}");
            $this->_renderer = $element;
        }
        return $this->_renderer;
    }
}
