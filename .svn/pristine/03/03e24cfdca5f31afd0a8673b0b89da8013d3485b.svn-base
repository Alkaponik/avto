<?php

class Testimonial_MageDoc_Block_Adminhtml_Form_Element_Fieldset extends Varien_Data_Form_Element_Fieldset
{    
    public function __construct($attributes = array()) {
        parent::__construct($attributes);
        $this->setData($attributes);
        $this->addType('combobox', 'Testimonial_MageDoc_Block_Adminhtml_Form_Element_Combobox');
        $this->addType('chooser', 'Testimonial_MageDoc_Block_Adminhtml_Form_Element_Chooser');
        $this->addType('grid', 'Testimonial_MageDoc_Block_Adminhtml_Form_Element_Grid');
    }
    
    public function addField($elementId, $type, $config, $after=false)
    {
        $element = Varien_Data_Form_Abstract::addField($elementId, $type, $config, $after);
        if (!$element->getRenderer() instanceof Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element){
            $element->setRenderer(Varien_Data_Form::getFieldsetElementRenderer());
        }
        return $element;
    }

    
    public function addFieldset($elementId, $config, $after=false)
    {
        $element = new Testimonial_MageDoc_Block_Adminhtml_Form_Element_Fieldset($config);
        $element->setId($elementId);
        $this->addElement($element, $after);
        return $element;
    }
    
    public function addRowFieldset($elementId, $config, $after=false)
    {
        $fieldset = $this->addFieldset($elementId, $config, $after);
        $fieldset->setRenderer(Mage::app()->getLayout()
           ->createBlock('magedoc/adminhtml_widget_form_renderer_fieldset_row'));
    }
}
