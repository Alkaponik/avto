<?php
require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml').DS.'Sales'.DS.'Order'.DS.'InvoiceController.php';

class Testimonial_MageDoc_Sales_Order_InvoiceController extends Mage_Adminhtml_Sales_Order_InvoiceController
{
    protected function _setActiveMenu($menuPath)
    {
        parent::_setActiveMenu('magedoc/order');
        return $this;
        
    }
 
    protected function _getInquiryQtys()
    {
        $qtys = array();
        $data = $this->getRequest()->getParam('invoice');
        if (isset($data['inquiries'])) {
            foreach($data['inquiries'] as $inquiryId => $inquiry)
            $qtys[$inquiryId] = $inquiry['qty'];
        } 
        return $qtys;
    }

    protected function _getInquiryRetailerIds()
    {
        $ids = array();
        $data = $this->getRequest()->getParam('invoice');
        if (isset($data['inquiries'])) {
            foreach($data['inquiries'] as $inquiryId => $inquiry){
                if (isset($inquiry['retailer'])){
                    $ids[$inquiryId] = $inquiry['retailer'];
                }
            }
        } 
        return $ids;
    }

    
    protected function _initInvoice($update = false)
    {
        $this->_title($this->__('Sales'))->_title($this->__('Invoices'));

        $invoice = false;
        $itemsToInvoice = 0;
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        $orderId = $this->getRequest()->getParam('order_id');
        if ($invoiceId) {
            $invoice = Mage::getModel('magedoc/order_invoice')->load($invoiceId);
            if (!$invoice->getId()) {
                $this->_getSession()->addError($this->__('The invoice no longer exists.'));
                return false;
            }
        } elseif ($orderId) {
            $order = Mage::getModel('magedoc/order')->load($orderId);
            /**
             * Check order existing
             */
            if (!$order->getId()) {
                $this->_getSession()->addError($this->__('The order no longer exists.'));
                return false;
            }
            /**
             * Check invoice create availability
             */
            if (!$order->canInvoice()) {
                $this->_getSession()->addError($this->__('The order does not allow creating an invoice.'));
                return false;
            }
            $itemSavedQtys = $this->_getItemQtys();
            $inquirySavedQtys = $this->_getInquiryQtys();
            $inquirySavedRetailerIds = $this->_getInquiryRetailerIds();
            $invoice = Mage::getModel('magedoc/service_order', $order)
                    ->setRetailerIds($inquirySavedRetailerIds)
                    ->prepareInvoice($itemSavedQtys, $inquirySavedQtys);
            if (!$invoice->getTotalQty()) {
                Mage::throwException($this->__('Cannot create an invoice without products.'));
            }
        }
        
        Mage::register('current_invoice', $invoice);
        return $invoice;
    }

    public function saveAction()
    {
        parent::saveAction();

        $invoice = Mage::registry('current_invoice');
        $data = $this->getRequest()->getPost('invoice');

        $hasErrors = false;
        foreach ($this->_getSession()->getMessages() as $message){
            if ($message instanceof Mage_Core_Model_Message_Error){
                $hasErrors = true;
            }
        }
        if (Mage::helper('core')->isModuleEnabled('Testimonial_CustomerNotification')) {
            $helper = Mage::helper('customernotification');
            if ($invoice && !$hasErrors && !empty($data['send_sms']) && $helper->canSendInvoiceSms($invoice)) {
                try {
                    $helper->sendPaymentDetails($invoice);
                    $invoice->setSmsSent(true);
                    $this->_getSession()->addSuccess($this->__('SMS notification was sent successfully'));
                    $invoice->save();
                } catch (Exception $e) {
                    $this->_getSession()->addError($this->__('SMS notification failed (%s)', $e->getMessage()));
                    Mage::logException($e);
                }
            }
        }
    }

    /**
     * Save data for invoice and related order
     *
     * @param   Mage_Sales_Model_Order_Invoice $invoice
     * @return  Mage_Adminhtml_Sales_Order_InvoiceController
     */
    protected function _saveInvoice($invoice)
    {
        if (!$invoice->getOrder()->hasIsInProcess()){
            $invoice->getOrder()->setIsInProcess(true);
        }
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();

        return $this;
    }
}
