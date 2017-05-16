<?php

class Testimonial_MageDoc_Block_Adminhtml_Form_Element_Chooser extends Varien_Data_Form_Element_Abstract
{

    public function __getElementHtml()
    {
        $choposer = Mage::getModel('core/layout')->createBlock('magedoc/adminhtml_widget_chooser_vehicle');
        
        $choposer->setData(array(
            'id'            => 'vehicle'));
        
        return $choposer->toHtml();
    }

    public function getJsObjectName() {
         return $this->getHtmlId() . 'Vehicle';
    }
}
