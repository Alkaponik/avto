<?php

class Testimonial_MageDoc_Block_Adminhtml_Widget_Grid_Column_Filter_Index extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text
{
    public function getCondition()
    {
        return array('like'=> $this->_escapeValue($this->getValue()).'%');
    }
}