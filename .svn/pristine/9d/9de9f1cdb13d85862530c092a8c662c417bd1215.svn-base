<?php

class Testimonial_Avtoto_Block_Adminhtml_Messages extends Mage_Core_Block_Template
{
    protected function _toHtml()
    {
        if ($this->getEnabled() || Mage::getStoreConfig('avtoto/general/enable_message_popup')){
            return parent::_toHtml();
        }
        return '';
    }
}
