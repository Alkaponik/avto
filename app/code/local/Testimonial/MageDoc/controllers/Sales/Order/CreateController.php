<?php
require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml').DS.'Sales'.DS.'Order'.DS.'CreateController.php';

class Testimonial_MageDoc_Sales_Order_CreateController extends Mage_Adminhtml_Sales_Order_CreateController
{
    protected function _setActiveMenu($menuPath)
    {
        parent::_setActiveMenu('magedoc/order');
        return $this;
    }

    public function loadBlockAction()
    {
        $request = $this->getRequest();
        try {
            $this->_initSession()
                ->_processData();
        }
        catch (Mage_Core_Exception $e){
            $this->_reloadQuote();
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e){
            $this->_reloadQuote();
            $this->_getSession()->addException($e, $e->getMessage());
        }

        $asJson= $request->getParam('json');
        $block = $request->getParam('block');

        $update = $this->getLayout()->getUpdate();
        if ($asJson) {
            $update->addHandle('magedoc_sales_order_create_load_block_json');
        } else {
            $update->addHandle('magedoc_sales_order_create_load_block_plain');
        }

        if ($block) {
            $blocks = explode(',', $block);            
            if ($asJson && !in_array('message', $blocks)) {
                $blocks[] = 'message';
            }
            foreach ($blocks as $block) {
                $update->addHandle('magedoc_sales_order_create_load_block_' . $block);
            }
        }
        $this->loadLayoutUpdates()->generateLayoutXml()->generateLayoutBlocks();
        $result = $this->getLayout()->getBlock('content')->toHtml();
        if ($request->getParam('as_js_varname')) {
            Mage::getSingleton('adminhtml/session')->setUpdateResult($result);
            $this->_redirect('*/*/showUpdateResult');
        } else {
            $this->getResponse()->setBody($result);
        }
    }

    /**
     * Retrieve order create model
     *
     * @return Testimonial_MageDoc_Model_Order_Create
     */

    protected function _getOrderCreateModel()
    {   
        $model = Mage::getSingleton('magedoc/order_create');
        return $model;
    }

    protected function _getSession()
    {
        return Mage::getSingleton('magedoc/session_quote');
    }
    
    public function reorderAction()
    {
//        $this->_initSession();
        $this->_getSession()->clear();
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('magedoc/order')->load($orderId);
        if (!Mage::helper('sales/reorder')->canReorder($order)) {
            return $this->_forward('noRoute');
        }

        if ($order->getId()) {
            $order->setReordered(true);
            $this->_getSession()->setUseOldShippingMethod(true);
            $this->_getOrderCreateModel()->initFromOrder($order);

            $this->_redirect('*/*');
        }
        else {
            $this->_redirect('*/sales_order/');
        }
    }

    public function validateVehicle($vehicleData)
    {
        if(empty($vehicleData) || !is_array($vehicleData)){
            return false;
        }
        foreach ($vehicleData as $key => $value){
            if (strlen($vehicleData[$key]) == 0){
                unset($vehicleData[$key]);
            }
        }
        return true;
        if(!$vehicleData['manufacturer'] 
                || !$vehicleData['production_start_year']
                || !$vehicleData['model']
                || !$vehicleData['type']
        ){
            return false;
        }
        return true;
    }

    public function validateInquiry(&$inquiryData)
    {
        $inquiryData['base_cost'] = isset($inquiryData['cost'])
            ? $inquiryData['cost']
            : 0;
        $inquiryData['row_total'] =
            (isset($inquiryData['price']) ? $inquiryData['price'] : 0)
            * (isset($inquiryData['qty']) ? $inquiryData['qty'] : 0);
        if (!empty($inquiryData['discount_percent'])
            && $inquiryData['discount_percent'] > 0
            && $inquiryData['discount_percent'] < 100
        ) {
            $inquiryData['row_total_with_discount'] = bcmul($inquiryData['row_total'], (100 - $inquiryData['discount_percent']) / 100, 4);
        } else {
            $inquiryData['row_total_with_discount'] = $inquiryData['row_total'];
        }

        if (empty($inquiryData) || !is_array($inquiryData)) {
            return false;
        }
        if (//!$inquiryData['category']
            //|| !$inquiryData['supplier']
            empty($inquiryData['name'])
            //|| !$inquiryData['sku']
        ) {
            $this->_getSession()->addError($this->__('Please specify inquiry name'));
            return false;
        }
        foreach ($inquiryData as $key => $value) {
            if (strlen($inquiryData[$key]) == 0) {
                unset($inquiryData[$key]);
            }
        }
        if (1 || !isset($inquiryData['sku']) || strlen(($inquiryData['sku'])) == 0) {
            $supplier = isset($inquiryData['supplier'])
                ? preg_replace('/\s+/', '-', $inquiryData['supplier'])
                : '';
            $code = isset($inquiryData['code'])
                ? preg_replace('/\s+/', '-', $inquiryData['code'])
                : '';
            $inquiryData['sku'] = $supplier . '-' . $code;
        }
        return true;
    }
    
    
    protected function _processData()
    {
        
        if ($inquiries = $this->getRequest()->getPost('inquiry')) {
            $retailerArray = Mage::getModel('magedoc/source_retailer')
                    ->getOptionArray();
            $items = array();
            foreach($inquiries as $inquiryFormId => $inquiryForm){
                if(!$this->validateVehicle($inquiryForm['vehicle'])){
                    continue;
                }
                if(strpos($inquiryFormId, 'added') === 0){
                    $vehicle = Mage::getModel('magedoc/quote_vehicle');
                    $this->_getQuote()->addVehicle($vehicle->setData($inquiryForm['vehicle']));
                }else{
                    $vehicle = $this->_getQuote()->getVehiclesCollection()
                           ->getItemById($inquiryFormId);
                    if ($vehicle){
                        if (!isset($inquiryForm['is_deleted'])){
                            $vehicle->addData($inquiryForm['vehicle']);
                        }else{
                            $vehicle->isDeleted(true);
                        }
                    }else{
                        if (!isset($inquiryForm['is_deleted'])){
                            $vehicle = Mage::getModel('magedoc/quote_vehicle');
                            $this->_getQuote()->addVehicle($vehicle->setData($inquiryForm['vehicle']));
                        }else{
                            continue;
                        }
                    }
                }

                if (!isset($inquiryForm['inquiries'])){
                    continue;
                }
                if(isset($inquiryForm['inquiries']) && is_array($inquiryForm['inquiries'])){
                    foreach($inquiryForm['inquiries'] as $inquiryId => $inquiryData){
                        if (!empty($inquiryData['is_deleted'])) {
                            if (strpos($inquiryId, 'added') === false){
                                $inquiry = $vehicle->getInquiriesCollection()->getItemById($inquiryId);
                                if ($inquiry) {
                                    $inquiry->isDeleted(true);
                                }
                            }
                        } else {
                            if (!$this->validateInquiry($inquiryData)) {
                                continue;
                            }
                            if (strpos($inquiryId, 'added') === 0) {
                                if (!empty($inquiryData['product_id'])) {
                                    $items[$inquiryData['product_id']] = $inquiryData;
                                } else {
                                    $inquiry = Mage::getModel('magedoc/quote_inquiry')
                                        ->setData($inquiryData);
                                    $inquiry->setRetailer($retailerArray[$inquiryData['retailer_id']]);
                                    $vehicle->addInquiry($inquiry);
                                }
                            } else {
                                $inquiry = $vehicle->getInquiriesCollection()->getItemById($inquiryId);
                                if (!empty($inquiryData['product_id'])) {
                                    $items[$inquiryData['product_id']] = $inquiryData;
                                    $inquiryData['is_deleted'] = true;
                                }
                                if (empty($inquiryData['is_deleted'])) {
                                    $inquiry->addData($inquiryData);
                                    $inquiry->setRetailer($retailerArray[$inquiryData['retailer_id']]);
                                } else {
                                    $inquiry->isDeleted(true);
                                }
                            }
                        }
                    }
                }
            }
            $this->_getOrderCreateModel()->addProducts($items);
            //$this->_getQuote()->getVehiclesCollection()->save();
            $this->_getOrderCreateModel()->setRecollect(true);
        }
        if ($order = $this->getRequest()->getPost('order')) {
            if(!empty($order['shipping_date'])){
                $this->_getQuote()->setGmtShippingDate($order['shipping_date']);
            }
        }
        
        return $this->_processActionData();
    }

    /**
     * Start order create action
     */
    public function startAction()
    {
        $this->_getSession()->clear();
        $this->_redirect('*/*',
            array(
                'customer_id'   =>  $this->getRequest()->getParam('customer_id'),
                'filter'        =>  $this->getRequest()->getParam('filter')));
    }

    protected function _initSession()
    {
        $result = parent::_initSession();
        /**
         * Identify quote
         */
        if ($quoteId = $this->getRequest()->getParam('quote_id')) {
            $this->_getSession()->setQuoteId((int) $quoteId);
        }
        $this->_forwardFormData();
        return $result;
    }

    protected function _forwardFormData()
    {
        if (!$this->getRequest()->getParam('customer_id')
            && $filter = $this->getRequest()->getParam('filter')){
            $data = Mage::helper('adminhtml')->prepareFilterString($filter);
            if (!empty($data['Telephone'])){
                $address = $this->_getOrderCreateModel()->getBillingAddress();
                if (!$address->hasTelephone()){
                    $address->setTelephone(trim($data['Telephone']));
                }
            }
        }
    }

    /**
     * Cancel order create
     */
    public function saveQuoteAction()
    {
        try {
            $this->_processActionData('save');
            if ($paymentData = $this->getRequest()->getPost('payment')) {
                $this->_getOrderCreateModel()->setPaymentData($paymentData);
                $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
            }

            $quote = $this->_getOrderCreateModel()
                ->setIsValidate(true)
                ->importPostData($this->getRequest()->getPost('order'))
                ->createQuote();
            $this->_getSession()->setCustomerId($quote->getCustomerId());
            $this->_getSession()->setQuoteId($quote->getId());

            $this->_getSession()->clear();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Quote has been saved.'));
            $this->_redirect('*/*/');
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $this->_getOrderCreateModel()->saveQuote();
            $message = $e->getMessage();
            if( !empty($message) ) {
                $this->_getSession()->addError($message);
            }
            $this->_redirect('*/*/');
        } catch (Mage_Core_Exception $e){
            $message = $e->getMessage();
            if( !empty($message) ) {
                $this->_getSession()->addError($message);
            }
            $this->_redirect('*/*/');
        }
        catch (Exception $e){
            $this->_getSession()->addException($e, $this->__('Quote saving error: %s', $e->getMessage()));
            $this->_redirect('*/*/');
        }
    }
}
