<?php

class Testimonial_MageDoc_Block_Adminhtml_Supplier_Helper_Image extends Varien_Data_Form_Element_Image
{
    protected function _getUrl()
    {
        $url = false;
        if ($this->getValue()) {
            $prefix = strpos($this->getValue(), '/') === false
                ? 'magedoc/logos/'
                : '';
            $url = Mage::getBaseUrl('media').$prefix. $this->getValue();
        }
        return $url;
    }
}
