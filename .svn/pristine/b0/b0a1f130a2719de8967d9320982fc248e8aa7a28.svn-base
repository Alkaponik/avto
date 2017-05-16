<?php

class Testimonial_MageDoc_Block_Adminhtml_Manufacturer_Helper_Image extends Varien_Data_Form_Element_Image
{
    protected function _getUrl()
    {
        $url = false;
        if ($this->getValue()) {
            $url = Mage::getBaseUrl('media').'magedoc/avtomarks/'. $this->getValue();
        }
        return $url;
    }
}
