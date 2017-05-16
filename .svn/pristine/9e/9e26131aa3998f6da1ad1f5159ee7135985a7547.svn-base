<?php

class Testimonial_MageDoc_Block_Adminhtml_Notification_Window extends Mage_Adminhtml_Block_Notification_Toolbar
{
    protected function _construct()
    {
        parent::_construct();

        $this->setHeaderText(addslashes($this->__('Product Inforamtion')));
        $this->setCloseText(addslashes($this->__('close')));
    }

    public function canShow()
    {
        return true;
    }

    
    public function getRequestUrl() 
    {
        return $this->getUrl('magedoc/adminhtml_product_information/request');
    }
}
