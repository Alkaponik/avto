<?php

require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml').DS.'Sales'.DS.'OrderController.php';


class Testimonial_MageDoc_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    protected function _setActiveMenu($menuPath)
    {
        parent::_setActiveMenu('magedoc/order');
        return $this;
        
    }
    
    public function requestAction()
    {
        
        if($item = $this->getRequest()->getPost('item')){
            if($value = $this->getRequest()->getPost('value')){
                switch ($item) {
                    case 'manufacturer':
                        Mage::getSingleton('core/session')->setManufacturerId($value);
                        $source = Mage::getModel('magedoc/source_date')->getOptionArray();
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($source));
                        break;
                   case 'date':
                        $manufacturerId = Mage::getSingleton('core/session')->getManufacturerId();
                        $source = Mage::getModel('magedoc/source_model')
                           ->setYearStart($value)->setManufacturerId($manufacturerId)->getOptionArray();
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($source));
                        break;
                    case 'model':
                        $source = Mage::getModel('magedoc/source_type')->setModelId($value)->getOptionArray();
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($source));
                        
                        break;
                    case 'category':
                        $source = Mage::getModel('magedoc/source_type_supplier')->setStrId($value);
                        if($typeId = $this->getRequest()->getPost('type_id')){
                            $source->setTypeId($typeId);
                        }
                        
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($source->getOptionArray()));
                        break;
                    case 'supplier':
                        $source = Mage::getModel('magedoc/source_type_article')
                            ->setStrId($this->getRequest()->getPost('str_id'))
                            ->setSupplierId($value);
                        if($typeId = $this->getRequest()->getPost('type_id')){
                            $source->setTypeId($typeId);
                        }
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($source->getExtendedOptionArray(array('code', 'product_id', 'cost', 'price' ,'retailer_id'))));
                        break;
                    case 'code':
                        $source = Mage::getModel('magedoc/source_type_article')
                            ->setStrId($this->getRequest()->getPost('str_id'))
                            ->setSupplierId($this->getRequest()->getPost('str_id'))
                            ->setSearchNumber($value);
                        if($typeId = $this->getRequest()->getPost('type_id')){
                            $source->setTypeId($typeId);
                        }
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($source->getExtendedOptionArray(array('code', 'sup_id', 'sup_brand', 'product_id', 'cost', 'price' ,'retailer_id'))));
                        break;
                    default:
                        break;
                }    
            }elseif($item == 'category'){
                $source = Mage::getModel('magedoc/source_type_category');
                if($typeId = $this->getRequest()->getPost('type_id')){
                    $source->setTypeId($typeId);
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($source->getOptionArray()));
                }else{
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array()));
                }
            }
        }
    }
    
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('magedoc/order')->load($id);

        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);
        return $order;
    }
    
    /**
     * Print assembilies for selected orders
     */
    public function pdfassembliesAction(){
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!is_array($orderIds) && strlen($orderIds)){
            $orderIds = explode(',',$orderIds);
        }
        if (!empty($orderIds)) {
            $orders = Mage::getResourceModel('magedoc/order_collection')
                ->addFieldToFilter('entity_id', array('in' => $orderIds));

            if (!Mage::getSingleton('admin/session')->isAllowed('magedoc/orders/actions/print_all')) {
                $orders
                    ->addFieldToFilter('status', array('in' =>  Mage::helper('magedoc')->getAssembleOrderStatuses()))
                    ->addFieldToFilter('supply_status', array('in' =>  Mage::helper('magedoc')->getAssembleOrderSupplyStatuses()));
            }

            $composerMode = (int)$this->getRequest()->getParam('mode');
            if ($orders->getSize() && $composerMode) {
                $composer = Mage::getModel('magedoc/order_pdf_assembly')
                    ->setMode($composerMode);
                if (!isset($pdf)) {
                    $pdf = $composer->getPdf($orders);
                } else {
                    $pages = $composer->getPdf($orders);
                    $pdf->pages = array_merge($pdf->pages, $pages->pages);
                }
                $filename = 'assembly' . Mage::getSingleton('core/date')->date('Y-m-d_H-i-s') . '.pdf';
                foreach ($orders as $order) {
                    $message = Mage::helper('magedoc')->__('Printed order assemblies to %s', $filename);
                    $comment = $order->addStatusHistoryComment($message);
                    $order->addRelatedObject($comment);
                    $order->save();
                }
                return $this->_prepareDownloadResponse(
                    $filename, $pdf->render(),
                    'application/pdf'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/*/');
            }
        }
        $this->_redirect('*/*/');
    }

    public function saveSupplyStatusAction()
    {
        $successCount = 0;
        $failedCount = 0;
        $inquiry = Mage::getModel('magedoc/order_inquiry');
        $item = Mage::getModel('sales/order_item');
        $hlp = Mage::helper('magedoc/supply');
        $orderIds = array();
        $classes = array(
            'inquiry'   =>  'inquiries',
            'item'      =>  'items'
        );
        $supplySettings = new Varien_Object();
        try {
            if ($data = $this->getRequest()->getPost('order')) {
                foreach ($classes as $model => $index){
                    if (isset($data[$index])) {
                        foreach ($data[$index] as $itemId => $itemData) {
                            try {
                                $$model->load($itemId);
                                if ($$model->getId()) {
                                    $supplySettings->setData($itemData);
                                    $hlp->validateItemSupplySettings($$model, $supplySettings);
                                    $$model->addData($supplySettings->getData());
                                    if ($hlp->hasItemSupplyDataChanged($$model)) {
                                        $$model->save();
                                        $orderIds[$$model->getOrderId()] = $$model->getOrderId();
                                        $successCount++;
                                    }
                                }
                            } catch (Testimonial_MageDoc_Exception $e){
                                $this->_getSession()->addWarning($e->getMessage());
                                $$model->addData($supplySettings->getData());
                                if ($hlp->hasItemSupplyDataChanged($$model)) {
                                    $$model->save();
                                    $orderIds[$$model->getOrderId()] = $$model->getOrderId();
                                    $successCount++;
                                }
                            } catch (Mage_Core_Exception $e){
                                $this->_getSession()->addError($this->__('Item %s supply settings update failed: %s', $$model->getSku(), $e->getMessage()));
                                $failedCount++;
                            }
                        }
                    }
                }
            }
            foreach ($orderIds as $orderId){
                $order = Mage::getModel('magedoc/order')->load($orderId);
                $order->updateSupplyStatus();
            }
            $this->_getSession()->addSuccess($this->__('Order supply settings updated successfully'));
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($this->__('Order supply settings update failed: %s', $e->getMessage()));
        }

        $this->_redirect('*/*/view', array('order_id' => $this->getRequest()->getPost('order_id')));
    }

    /**
     * Generate order history for ajax request
     */
    public function commentsHistoryAction()
    {
        $this->_initOrder();
        $html = $this->getLayout()->createBlock('magedoc/adminhtml_order_view_tab_history')->toHtml();
        /* @var $translate Mage_Core_Model_Translate_Inline */
        $translate = Mage::getModel('core/translate_inline');
        if ($translate->isAllowed()) {
            $translate->processResponseBody($html);
        }
        $this->getResponse()->setBody($html);
    }

    /**
     * Add order comment action
     */
    public function addCommentAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $response = false;
                $data = $this->getRequest()->getPost('history');

                $notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
                $visible = isset($data['is_visible_on_front']) ? $data['is_visible_on_front'] : false;
                $createCrmCall = isset($data['create_crm_call']) ? $data['create_crm_call'] : false;
                $notifyBySms = isset($data['is_customer_notified_by_sms']) ? $data['is_customer_notified_by_sms'] : false;
                $statusChangeReason = !empty($data['status_change_reason']) ? $data['status_change_reason'] : null;

                $statusHistory = $order->addStatusHistoryComment($data['comment'], $data['status'], $data['supply_status'], $statusChangeReason)
                    ->setIsVisibleOnFront($visible)
                    ->setIsCustomerNotified($notify);

                if($notifyBySms){
                    Mage::dispatchEvent('customer_notified_by_sms', array(
                        'post_data' => $data,
                        'order' => $order,
                        'history' => $statusHistory));
                }

                $comment = trim(strip_tags($data['comment']));

                if ($order->canChangeManager() && !empty($data['order_manager_id'])){
                    $newManager = Mage::getModel('admin/user')
                        ->load($data['order_manager_id']);
                    if ($newManager->getIsActive()){
                        $order->setManager($newManager);
                        if ($order->dataHasChangedFor('manager_id')){
                            $comment = Mage::helper('magedoc')->__('Order manager was changed to %s', $newManager->getName());
                            $statusHistory = $order->addStatusHistoryComment($comment, $data['status'], $data['supply_status'])
                                ->setIsVisibleOnFront(false)
                                ->setIsCustomerNotified(false);
                        }
                    }
                }

                $order->save();
                $order->sendOrderUpdateEmail($notify, $comment);
                if($createCrmCall) {
                    $dateStr = isset($data['date_crm_call'])? $data['date_crm_call']: null;
                    $statusHistory->setCallDateTime($dateStr);
                    Mage::helper('sugarcrm/call')->exportCallToSugarcrm( $statusHistory );
                }

                $this->loadLayout('empty');
                $this->renderLayout();
            }
            catch (Mage_Core_Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => $e->getMessage(),
                );
            }
            catch (Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => $this->__('Cannot add order history.')
                );
                Mage::logException($e);
            }
            if (is_array($response)) {
                $response = Mage::helper('core')->jsonEncode($response);
                $this->getResponse()->setBody($response);
            }
        }
    }

    public function saveShippingDateAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $response = false;
                $data = $this->getRequest()->getPost('order');
                $shippingDate = isset($data['shipping_date'])? $data['shipping_date']: false;
                if($shippingDate){
                    $order->setGmtShippingDate($shippingDate);
                    $origShippingDate = Mage::app()->getLocale()->date(
                        Varien_Date::toTimestamp($order->getOrigData('shipping_date')),
                        null,
                        null,
                        true
                    );
                    $origShippingDate = $origShippingDate->toString(Mage::helper('magedoc')->getShippingDateFormat());
                    if ($origShippingDate != $shippingDate){
                        $message = Mage::helper('magedoc')->__('Shipping date changed from %s to %s', $origShippingDate, $shippingDate);
                        $comment = $order->addStatusHistoryComment($message);
                        $order->addRelatedObject($comment);
                        $order->save();
                    }
                }
            }catch (Mage_Core_Exception $e) {
                    $response = array(
                        'error'     => true,
                        'message'   => $e->getMessage(),
                    );
                }
            catch (Exception $e) {
                    $response = array(
                        'error'     => true,
                        'message'   => $this->__('Cannot add order history.')
                    );
                    Mage::logException($e);
                }
            if (is_array($response)) {
                $response = Mage::helper('core')->jsonEncode($response);
                $this->getResponse()->setBody($response);
            }
        }
    }

    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'orders.csv';
        $grid       = $this->getLayout()->createBlock('magedoc/adminhtml_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName   = 'orders.xml';
        $grid       = $this->getLayout()->createBlock('magedoc/adminhtml_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    /**
     * Cancel order
     */
    public function shipAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                if ($this->_shipOrder($order)){
                    $this->_getSession()->addSuccess(
                        $this->__('The shipment has been created.')
                    );
                }
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('Cannot save shipment.'));
                Mage::logException($e);
            }
            $this->_redirect('*/sales_order/index');
        }
    }

    protected function _shipOrder($order)
    {
        if (!$order->canShip()) {
            $this->_getSession()->addError($this->__('Cannot do shipment for the order.'));
            return false;
        }
        $itemSavedQtys = array();
        $inquirySavedQtys = array();
        foreach ($order->getAllItems() as $item)
        {
            if ($item->getQtyToShip()>0 && !$item->getIsVirtual()
                && !$item->getLockedDoShip())
            {
                $itemSavedQtys[$item->getId()] = $item->getQtyToShip();
            }
        }

        foreach ($order->getAllInquiries() as $item)
        {
            if ($item->getQtyToShip()>0 && !$item->getIsVirtual()
                && !$item->getLockedDoShip())
            {
                $inquirySavedQtys[$item->getId()] = $item->getQtyToShip();
            }
        }

        $shipment = Mage::getModel('magedoc/service_order', $order)
            ->prepareInquiriesShipment($itemSavedQtys, $inquirySavedQtys);
        if (!$shipment) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Cannot save shipment.'));
            return false;
        }
        $shipment->register();

        $this->_saveShipment($shipment);

        return $shipment;
    }

    /**
     * Save shipment and order in one transaction
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return Mage_Adminhtml_Sales_Order_ShipmentController
     */
    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();

        return $this;
    }
}