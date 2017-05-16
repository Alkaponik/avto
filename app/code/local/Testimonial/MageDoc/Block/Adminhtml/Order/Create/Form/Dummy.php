<?php

class Testimonial_MageDoc_Block_Adminhtml_Order_Create_Form_Dummy extends Testimonial_MageDoc_Block_Adminhtml_Order_Create_Form_Inquiry
{
    protected function _prepareForm()
    {      
        $this->_form = new Varien_Data_Form();
        $this->addInquiry();        
    }
 
}
