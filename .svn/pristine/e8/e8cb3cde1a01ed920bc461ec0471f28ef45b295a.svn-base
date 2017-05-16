<?php

class Testimonial_MageDoc_Block_Adminhtml_Form_Element_Edit extends Varien_Data_Form_Element_Abstract
{
    protected $_attributes = array();
    
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setData($attributes); 
        $this->setType('edit');
    }

    public function getName()
    {
        $name = parent::getName();
        if (strpos($name, '[]') === false) {
            $name.= '[]';
        }
        return $name;
    }

    public function getElementHtml()
    {
        $html = "<input type='text' name={$this->getName()}/>";
        return $html;
    }

}
