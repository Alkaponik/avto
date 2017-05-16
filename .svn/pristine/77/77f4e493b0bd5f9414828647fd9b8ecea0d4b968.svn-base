<?php
class Testimonial_MageDoc_Block_Adminhtml_Form_Extended extends Varien_Data_Form
{
    public function __construct($attributes = array()) 
    {
        parent::__construct($attributes);
        $this->addType('combobox', 'Testimonial_MageDoc_Block_Adminhtml_Form_Element_Combobox');
        $this->addType('chooser', 'Testimonial_MageDoc_Block_Adminhtml_Form_Element_Chooser');
        $this->addType('grid', 'Testimonial_MageDoc_Block_Adminhtml_Form_Element_Grid');
    }
    public function setValues($values)
    {
        foreach ($this->_allElements as $element) {
            if (isset($values[$element->getId()])) {
                $element->setValue($values[$element->getId()]);
            }
            else {
                $element->setValue(null);
            }
            if($element->getType() == 'combobox'){
                $textIndex = $element->getTextIndex();
                if(isset($values[$textIndex])){
                    $element->setTextValue($values[$textIndex]);
                }else{
                    $element->setTextValue(null);
                }
            }            
        }
        return $this;
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
        return $fieldset;
    }

    
}
