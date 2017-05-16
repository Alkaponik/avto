<?php
class Testimonial_Intime_WarehousesController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Intime Poshta Warehouses'));

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('intime/adminhtml_warehouses'))
            ->renderLayout();

        return $this;
    }

    public function synchronizeAction()
    {
        try {
            Mage::getModel('intime/import')->runWarehouseAndCityMassImport();
            $this->_getSession()->addSuccess($this->__('City and Warehouse API synchronization finished'));
        }
        catch (Exception $e) {
            $this->_getSession()->addError($this->__('Error during synchronization: %s', $e->getMessage()));
        }

        $this->_redirect('*/*/index');

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
            ->_setActiveMenu('sales/intime/warehouses')
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('Nova Poshta Warehouses'), $this->__('Nova Poshta Warehouses'))
        ;
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
