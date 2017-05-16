<?php

class Testimonial_MageDoc_Block_Adminhtml_Widget_Form_Renderer_Fieldset_Row extends Mage_Adminhtml_Block_Template 
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_element;

    protected function _construct()
    {
        $this->setTemplate('magedoc/widget/form/renderer/fieldset/row.phtml');
    }

    public function getElement()
    {
        return $this->_element;
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this->toHtml();
    }

    public function getHtmlAttributes()
    {
        return array('style', 'onclick', 'onchange');
    }
}
