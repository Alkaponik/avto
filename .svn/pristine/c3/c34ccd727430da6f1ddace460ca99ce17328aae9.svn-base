<?php

class Testimonial_CustomerNotification_Block_Adminhtml_Message extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'customernotification';
        $this->_controller = 'adminhtml_message';
        $this->_headerText      = Mage::helper('customernotification')->__('Manage Messages');
        $this->_addButtonLabel  = Mage::helper('customernotification')->__('Add New Message');
        parent::__construct();
        if (!Mage::getSingleton('admin/session')->isAllowed('customernotification/message/actions/create'))
        {
            $this->removeButton('add');
        }
    }
}
