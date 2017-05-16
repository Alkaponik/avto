<?php
class Testimonial_MageDoc_Adminhtml_SupplyController extends Mage_Adminhtml_Controller_Action
{
    protected $_publicActions = array('document');

    protected function _initCustomer($idFieldName = 'reference')
    {
        $customerId = (int) $this->getRequest()->getParam($idFieldName);
        $customer = Mage::getModel('customer/customer');

        if ($this->getRequest()->getParam('document_type') == Testimonial_MageDoc_Block_Adminhtml_Supply_Document_Grid::DOCUMENT_TYPE_CUSTOMER
            && $customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);
        return $this;
    }
 
    public function indexAction()
    {
        $this->loadLayout()
			->_setActiveMenu('magedoc/retailers')
			->_addBreadcrumb(Mage::helper('magedoc')->__('Supply management'), Mage::helper('magedoc')->__('Supply management'));                
		$this->renderLayout();
    }

    
    public function saveAction()
    {
        $items = $this->getRequest()->getPost('item');
        $inquiries = $this->getRequest()->getPost('inquiry');
        $reference = $this->getRequest()->getPost('document_reference');
        $this->_getSession()->setDocumentReference($reference);

        $orderIds = array();
        try{
            $orderIds = $this->_processSupplyData($items);
            $orderIds =  array_merge($orderIds, $this->_processSupplyData($inquiries, 'inquiry'));

        }catch(Mage_Core_Exception $e){
            $this->_getSession()->addError($e->getMessage());
        }catch(Exception $e){
            $this->_getSession()->addError($e->getMessage());
            Mage::logException($e);
        }
        foreach ($orderIds as $orderId){
            /** @var $order Testimonial_MageDoc_Model_Order */
            $order = Mage::getModel('magedoc/order')->load($orderId);
            $order->updateSupplyStatus();
            if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
                $orderLink = $this->getUrl('*/sales_order/view', array('order_id' => $order->getId()));
                $orderLink = sprintf('<a href="%s" target="_blank">%s</a>', $orderLink, $order->getIncrementId());
            } else {
                $orderLink = $order->getIncrementId();
            }

            $shippingMethodText = $order->getShippingCarrier()->getConfigData('title');
            if ($order->dataHasChangedFor('supply_status')){
                switch ($order->getSupplyStatus()){
                    case Testimonial_MageDoc_Model_Source_Order_Supply_Status::RESERVED:
                        $this->_getSession()->addSuccess(Mage::helper('magedoc')->__('Order #%s reserved', $orderLink));
                        break;
                    case Testimonial_MageDoc_Model_Source_Order_Supply_Status::ASSEMBLING:
                        $this->_getSession()->addSuccess(Mage::helper('magedoc')->__('Order #%s assembling started', $orderLink));
                        break;
                    case Testimonial_MageDoc_Model_Source_Order_Supply_Status::ASSEMBLED:
                        if ($order->getLastSupplyStatus() == Testimonial_MageDoc_Model_Source_Order_Supply_Status::RESERVED){
                            $this->_getSession()->addSuccess(Mage::helper('magedoc')->__('Order #%s assembling started and complete (%s)', $orderLink, $shippingMethodText));
                        } else {
                            $this->_getSession()->addSuccess(Mage::helper('magedoc')->__('Order #%s assembled (%s)', $orderLink, $shippingMethodText));
                        }
                        break;
                    default:

                }
            } elseif ($order->getSupplyStatus() == Testimonial_MageDoc_Model_Source_Order_Supply_Status::ASSEMBLING) {
                $this->_getSession()->addSuccess(Mage::helper('magedoc')->__('Order #%s assembling in progress', $orderLink));
            } elseif ($order->getSupplyStatus() == Testimonial_MageDoc_Model_Source_Order_Supply_Status::SHIPPED
                || $order->getSupplyStatus() == Testimonial_MageDoc_Model_Source_Order_Supply_Status::CUSTOMER_NOTIFIED) {
                if ($order->isAllItemsAssembled()){
                    $this->_getSession()->addSuccess(Mage::helper('magedoc')->__('Order #%s assembled (%s)', $orderLink, $shippingMethodText));
                } elseif ($order->hasAssembledItems()){
                    $this->_getSession()->addSuccess(Mage::helper('magedoc')->__('Order #%s assembling in progress', $orderLink));
                }
                $this->_getSession()->addSuccess(Mage::helper('magedoc')->__('Order #%s shipped (%s)', $orderLink, $shippingMethodText));
            }
        }

        $this->_redirect('*/*/index');
    }

    public function resetEANFilter($filterName)
    {
        $session = Mage::getSingleton('adminhtml/session');
        if($filter = $session->getData($filterName)){
            $data = Mage::helper('adminhtml')->prepareFilterString($filter);
            if(isset($data['ean']) || isset($data['name'])){
                unset($data['ean']);
                unset($data['name']);
                array_walk_recursive($data, array($this, 'encodeFilter'));
                $session->setData($filterName, base64_encode(http_build_query($data)));
            }
        }
        return false;
    }
    
    public function encodeFilter($value)
    {
        return rawurlencode($value);
    }
    
    public function gridAction()
    {
        $this->loadLayout();
        $gridId = $this->getRequest()->getParam('id', 'item');
        $result = $this->getLayout()
                ->createBlock("magedoc/adminhtml_supply_{$gridId}_grid")
                ->toHtml();
        $this->getResponse()->setBody($result);
    }

    public function documentAction()
    {
        $this->loadLayout();
        if ($this->getRequest()->getParam('isAjax', false)){
            $this->getResponse()->setBody($this->getLayout()->getBlock('content')->toHtml());
        } else {
            $this->renderLayout();
        }
    }

    public function customerGridAction()
    {
        $this->_initCustomer();
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('magedoc/adminhtml_customer_edit_tab_items')
                ->toHtml());
        return $this;
    }

    protected function _processSupplyData($items, $entityType = 'item')
    {
        $successCount = 0;
        $failedCount = 0;
        $hlp = Mage::helper('magedoc/supply');
        $orderIds = array();
        if(!empty($items)){
            $item = $entityType == 'item'
                ? Mage::getModel('sales/order_item')
                : Mage::getModel('magedoc/order_inquiry');
            $supplySettings = new Varien_Object();
            $model = 'item';
            foreach ($items as $itemId => $itemData){
                $item->load($itemId);
                if($item->getId()){
                    try {
                        $supplySettings->setData($itemData);
                        $hlp->validateItemSupplySettings($$model, $supplySettings);
                        $$model->addData($supplySettings->getData());
                        if ($hlp->hasItemSupplyDataChanged($$model)) {
                            $$model->save();
                            $orderIds[$$model->getOrderId()] = $$model->getOrderId();
                            $successCount++;
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
                }else{
                    $this->_getSession()->addError($this->__('%s %s no longer exists', $this->__(strtoupper($entityType)), $itemId));
                }
            }
            $this->_getSession()->addSuccess(sprintf($this->__('%d items updated successfully, %d failed'), $successCount, $failedCount));
        }
        $this->resetEANFilter($entityType.'filter');

        return $orderIds;
    }

    /**
     * Print assembilies for selected orders
     */
    public function pdfroutingAction(){
        $itemIds = $this->getRequest()->getPost('item_ids');
        $flag = false;
        if (!is_array($itemIds) && strlen($itemIds)){
            $itemIds = explode(',',$itemIds);
        }
        $collections = array();
        $collections []= Mage::getResourceModel('magedoc/order_item_collection');
        $collections []= Mage::getResourceModel('magedoc/order_inquiry_collection');

        $retailers = new Varien_Data_Collection();
        $total = 0;

        foreach ($collections as $collection) {
            $idFieldName = $collection->getResource()->getIdFieldName();
            $collection->getSelect()
                ->joinInner(array('order' => $collection->getTable('sales/order')),
                'order.entity_id = main_table.order_id',
                '');
            $collection->addFieldToFilter('order.state',
                array('in' => Mage::helper('magedoc/supply')->getVisibleOrderStatuses()));
            $collection->addFieldToFilter('order.supply_status',
                array('in' => Mage::helper('magedoc/supply')->getVisibleOrderSupplyStatuses()));
            $collection->addFieldToFilter('main_table.supply_status',
                Testimonial_MageDoc_Model_Source_SuppliedType::RESERVED);
            $collection->addFieldToFilter('main_table.supply_date',
                array('lt' => Mage::app()->getLocale()->date()->add(1, Zend_Date::DAY)->toString('yyyy-MM-dd 00:00:00')));
            if (!empty($itemIds)) {
                //$collection->addFieldToFilter($idFieldName, array('in' => $itemIds));
            }
            $collection->addAttributeToSort('retailer_id');
            //print_r((string)$collection->getSelect());die;

            foreach ($collection as $item){
                if (!isset($retailer) || $retailer->getId() != $item->getRetailerId()){
                    if (!$retailer = $retailers->getItemById($item->getRetailerId())){
                        $retailer = Mage::getModel('magedoc/retailer');
                        $retailer->load($item->getRetailerId());
                        $retailers->addItem($retailer);
                        $items = new Varien_Data_Collection;
                        $retailer->setItems($items);
                    }else{
                        $items = $retailer->getItems();
                    }
                }
                $item->setId($idFieldName.'_'.$item->getId());
                $items->addItem($item);
                $rowTotal = $item->getCost()*$item->getQtyReserved();
                $retailer->setTotal($retailer->getTotal()+$rowTotal);
                $total += $rowTotal;
                $item->setPrice($item->getCost());
                $item->setRowTotal($rowTotal);
                $item->setQtyOrdered($item->getQtyReserved());
            }
        }

        if ($retailers->getSize()) {
            $composerMode = 'items';
            $composer = Mage::getModel('magedoc/order_pdf_supply')
                ->setMode($composerMode);

            if (!isset($pdf)) {
                $pdf = $composer->getPdf($retailers);
            } else {
                $pages = $composer->getPdf($retailers);
                $pdf->pages = array_merge($pdf->pages, $pages->pages);
            }
            if ($total > 0){
                $composer->drawTotals(array(array(
                    'label' => Mage::helper('magedoc')->__('Grand Total'),
                    'value' => $total)
                ));
            }
            return $this->_prepareDownloadResponse(
                'routing_' . Mage::getSingleton('core/date')->date('Y-m-d_H-i-s') . '.pdf', $pdf->render(),
                'application/pdf'
            );
        } else {
            $this->_getSession()->addError($this->__('There are no routing sheets available'));
            $this->_redirect('*/*/');
        }
        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        $action = strtolower($this->getRequest()->getActionName());

        switch ($action) {
            default:
                $aclResource = 'magedoc/supplies';
                break;

        }
        return Mage::getSingleton('admin/session')->isAllowed($aclResource);
    }
}