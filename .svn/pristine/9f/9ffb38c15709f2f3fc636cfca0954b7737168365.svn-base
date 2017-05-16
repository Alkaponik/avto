<?php

class Ak_NovaPoshta_Adminhtml_Novaposhta_ConsignmentController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Nova Poshta consignments'));

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('novaposhta/adminhtml_consignments'))
            ->renderLayout();

        return $this;
    }


    /**
     * Initialize action
     *
     * @return Testimonial_NovaPoshta_WarehousesController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/novaposhta/checkstatus')
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('Nova Poshta check status'), $this->__('Nova Poshta check status'))
        ;
        return $this;
    }

    public function checkstatusAction()
    {
        try {
            $import = Mage::getModel('novaposhta/import');
            $import->runCheckStatusConsignments();
            $this->_getSession()->addSuccess($this->__('Status updated %s сonsignments', $import->numConsignmentsUpdate));
        }
        catch (Exception $e) {
            $this->_getSession()->addError($this->__('Error during : %s', $e->getMessage()));
        }

        $this->_redirect('*/*/index/');

        return $this;
    }
}