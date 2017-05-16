<?php

class MageDoc_AdminMessenger_Adminhtml_AdminMessenger_MessageController extends Mage_Adminhtml_Controller_action
{
    protected function _isAllowed()
    {
        $action = strtolower($this->getRequest()->getActionName());

        switch ($action) {
            case 'massMerge':
            default:
                $aclResource = 'customer/merge';
                break;
        }
        return true;
        return Mage::getSingleton('admin/session')->isAllowed($aclResource);
    }

    /**
     * Initialize action
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/message')
            ->_addBreadcrumb(Mage::helper('admin_messenger')->__('Sales'), Mage::helper('bookkeeping')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('admin_messenger')->__('Messages'), Mage::helper('bookkeeping')->__('Messages'))
        ;
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Messages'));

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('admin_messenger/adminhtml_message'))
            ->renderLayout();
        return $this;
    }
}