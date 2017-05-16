<?php

class MageDoc_AdminMessenger_Block_Adminhtml_Message extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'admin_messenger';
        $this->_controller = 'adminhtml_message';
        $this->_headerText      = Mage::helper('bookkeeping')->__('Manage Sales Messages');
        $this->_addButtonLabel  = Mage::helper('bookkeeping')->__('New Message');
        parent::__construct();
        if (true || !Mage::getSingleton('admin/session')->isAllowed('bookkeeping/journal/actions/create'))
        {
            $this->removeButton('add');
        }
    }
}
