<?php
require_once('CreateController.php');

class Testimonial_MageDoc_Sales_Order_EditController extends Testimonial_MageDoc_Sales_Order_CreateController
{
  /**
     * Additional initialization
     *
     */
    protected function _construct()
    {
        $this->setUsedModuleName('Testimonial_MageDoc');
    }

    /**
     * Start edit order initialization
     */
    public function startAction()
    {
        $this->_getSession()->clear();
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('magedoc/order')->load($orderId);
        if ($order->getId()) {
            $this->_getSession()->setUseOldShippingMethod(true);
            $this->_getOrderCreateModel()->initFromOrder($order);
            $this->_redirect('*/*');
        }
        else {
            $this->_redirect('*/sales_order/');
        }
    }

    /**
     * Index page
     */
    public function indexAction()
    {
        $this->_title($this->__('MageDoc'))->_title($this->__('Orders'))->_title($this->__('Edit Order'));
        $this->loadLayout();

        //print_r($this->getRequest()->getParam('customer_id')); die;
        $this->_initSession()
            ->_setActiveMenu('sales/order')
            ->renderLayout();
    }
    
    /**
     * Acl check for admin
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/edit');
    }    
       
}
