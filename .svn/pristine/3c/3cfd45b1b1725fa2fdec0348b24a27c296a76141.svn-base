<?php

class Testimonial_MageDoc_Block_Adminhtml_Helper_Image extends Varien_Data_Form_Element_Image
{
    protected function _getUrl()
    {
        $url = false;
        if ($this->getValue()) {
            if ($this->hasData('path_prefix')){
                $url = $this->getData('path_prefix') . $this->getValue();
            }else{
                $url = $this->getValue();
            }
        }
        return $url;
    }
}
