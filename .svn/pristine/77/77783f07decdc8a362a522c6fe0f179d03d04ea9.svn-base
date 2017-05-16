<?php

class Testimonial_Intime_ConsignmentController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Intime consignments'));

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('intime/adminhtml_consignments'))
            ->renderLayout();

        return $this;
    }


    /**
     * Initialize action
     *
     * @return Testimonial_Intime_WarehousesController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/intime/checkstatus')
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('Intime check status'), $this->__('Intime check status'))
        ;
        return $this;
    }

    public function checkstatusAction()
    {
        try {
            $import = Mage::getModel('intime/import');
            $import->runCheckStatusConsignments();

            $this->_getSession()->addSuccess($this->__('Updated %s consignments.', $import->numConsignmentsUpdate));
        }
        catch (Exception $e) {
            $this->_getSession()->addError($this->__('Error during : %s', $e->getMessage()));
        }

        $this->_redirect('*/*/index/');

        return $this;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/intime');
    }
}