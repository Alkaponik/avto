<?php

class Testimonial_MageDoc_Model_Observer
{
    public function catalog_category_save_after(Varien_Event_Observer $observer)
    {
        $category = $observer->getEvent()->getDataObject();
        if($category->getTdStrId()){
            $root = Mage::helper('magedoc')->getSearchTreeRootCategoryId();
            $parentCategories = $category->getParentCategories();
            if (($category->getOrigData('entity_id') === null) && ($root) 
                &&  isset($parentCategories[$root])
            ){
                $this->rewriteChildrenData($category, $category->getParentId());
            }
            if ($searchTreeData = $category->getSearchTree()){
                if (is_array($searchTreeData)){
                    $searchTree = Mage::getModel('magedoc/searchTree')->load($category->getTdStrId());
                    $searchTree->addData($searchTreeData);
                    $category->setSearchTree($searchTree);
                    $searchTree->save();
                }
            }
        }
    }

    public function rewriteChildrenData($currentCategory, $parentCategoryId)
    {
        $categoryStrId = $currentCategory->getTdStrId();
        $categoryId = $currentCategory->getId();
        $searchTreeCollection = Mage::getResourceModel('magedoc/searchTree_collection');
        $searchTreeCollection->getSelect()
            ->joinInner(array('md_searchTree' => $searchTreeCollection->getMainTable()),
                'main_table.path LIKE CONCAT(md_searchTree.path, "%")', 
                '')
                ->where("md_searchTree.str_id = {$categoryStrId} 
                            AND main_table.STR_ID <> {$categoryStrId}");
        $categoryModel = Mage::getModel('catalog/category');

        foreach ($searchTreeCollection as $node) {
            if ($category = $categoryModel->loadByAttribute('td_str_id', $node->getStrId())) {
                $category->load($category->getId());
                if ($category->getParentId() == $parentCategoryId) {
                    $category->move($categoryId, null);
                }
            }
        }
        return $this;
    }

    
    public function catalog_product_save_after(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getDataObject();
        
        if(($product->getTdArtId() !== null) 
                && ($product->getOrigData('entity_id') == null)){
            $productId = $product->getId();
            $resource = Mage::getSingleton('core/resource');
            $connection = $resource->getConnection('write');
            $collection = Mage::getResourceModel('magedoc/tecdoc_linkArt_collection')
                    ->getTypesByArtId($product->getTdArtId());
            $types = array();
            foreach($collection as $item){
                foreach($item->getTypeIds() as $type){
                    $types[] = array('product_id' => $productId, 'type_id' => $type);
                }
            }
            $tableName = $resource->getTableName('magedoc/type_product');
            try {
                $connection->insertOnDuplicate($tableName, $types, array());
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        if ($product->getTypId() !== null) {
            try {
                $resource = Mage::getSingleton('core/resource');
                $connection = $resource->getConnection('write');
                $tableName = $resource->getTableName('magedoc/type_product');
                $select = $connection->select()
                    ->from($tableName, array('product_id', 'type_id'))
                    ->where('product_id=:product_id')
                    ->where('type="S"');

                $systemTypes = $connection->fetchPairs($select, array('product_id' => $product->getId()));

                $delete = array();
                foreach($systemTypes as $k => $v){
                    if(!in_array($v, $product->getTypId())){
                        $delete[] = $v;
                    }
                }
                if(!empty($delete)){
                    $connection->delete(
                        $tableName,
                        array('product_id = ?' => $product->getId(), 'type_id IN(?)' => $delete)
                    );
                }

                $connection->delete(
                    $tableName,
                    array('product_id = ?' => $product->getId(), 'type = ?' => 'U')
                );

                $newTypes = array_diff($product->getTypId(), $systemTypes);
                $productTypes = array();
                foreach ($newTypes as $type) {
                    $productTypes[] = array(
                        'product_id' => $product->getId(),
                        'type_id' => $type,
                        'type' => 'U');
                }

                $connection->insertOnDuplicate($tableName, $productTypes, array());

            }catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
    }

    public function sales_order_save_before(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $this->_setCurrentManager($order);
    }

    public function sales_order_status_history_save_before(Varien_Event_Observer $observer)
    {
        $statusHistory = $observer->getEvent()->getStatusHistory();
        $order = $statusHistory->getOrder() ? $statusHistory->getOrder()->getStoreId() : null;
        $this->_setCurrentManager($statusHistory, $order);
    }

    public function sales_order_invoice_save_before(Varien_Event_Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $this->_setCurrentManager($invoice);
    }

    public function sales_order_shipment_save_before(Varien_Event_Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $this->_setCurrentManager($shipment);
    }

    public function sales_order_creditmemo_save_before(Varien_Event_Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $this->_setCurrentManager($creditmemo);
    }

    protected function _setCurrentManager($object, $storeId = null)
    {
        Mage::helper('magedoc')->setCurrentManager($object, $storeId);
    }

    public function sales_order_save_after(Varien_Event_Observer $observer)
    {
        $customerSession = Mage::getSingleton('customer/session');
        if($customerSession->getCustomerVehicle() !== null
            && Mage::helper('magedoc')->getCustomerVehicleSaveRule() == 'registration_order_placement'
        ){
            $customerVehicles = $customerSession->getCustomer()->getVehicle();            
            if($sessionCustomerVehicles = $customerSession->getCustomerVehicle()){
                $newCustomerVehicles = implode(',', 
                    array_unique(array_merge(
                        explode(',', $customerVehicles),
                        explode(',', $sessionCustomerVehicles))));
                $customerSession->getCustomer()->setVehicle($newCustomerVehicles)
                        ->save();
            }
            $customerSession->unsetData('customer_vehicle');
        }
    }

    public function customer_save_before(Varien_Event_Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if(!$customer->getId()){
            $customerSession = Mage::getSingleton('customer/session');
            if($customerSession->getCustomerVehicle() !== null){
                $customer->setVehicle($customerSession->getCustomerVehicle());
                $customerSession->unsetData('customer_vehicle');
            }
        }
        $this->_setCurrentManager($customer);
    }
    
    public function sales_order_grid_collection_load_before(Varien_Event_Observer $observer)
    {
        $adminUser = Mage::getSingleton('admin/session')->getUser();
        if($adminUserId = $adminUser->getId()){
            if(!Mage::getSingleton('admin/session')->isAllowed('magedoc/orders/actions/view_all')){
                $observer->getOrderGridCollection()->addFieldToFilter('manager_id', 
                    array('in' => array(0, $adminUserId)));
            }
        }
    }

    public function sales_quote_item_set_product(Varien_Event_Observer $observer)
    {
        $product = $observer->getProduct();
        $quoteItem = $observer->getQuoteItem();
        if($quoteItem->getRetailerId() === null){
            $quoteItem->setRetailerId($product->getRetailerId());
        }
        $quoteItem->setCost($product->getCost());
        if ($quoteItem->getRetailerId()
                && $retailer = Mage::getResourceSingleton('magedoc/retailer_collection')->getItemById($quoteItem->getRetailerId())){
            $quoteItem->setRetailer($retailer->getName());
        }
    }

    public function sales_quote_collect_totals_after(Varien_Event_Observer $observer)
    {
        $quote = $observer->getQuote();
        if($quote instanceof Testimonial_MageDoc_Model_Quote){
            $quote->setInquiriesQty(0);
            foreach($quote->getAllInquiries() as $inquiry){            
                $quote->setInquiriesQty($quote->getInquiriesQty() + $inquiry->getQty());
            }
        }
        $quote->setCost(0);
        $quote->setBaseCost(0);
        $quote->setMargin(0);
        $quote->setBaseMargin(0);
        foreach ($quote->getAllAddresses() as $address) {
            $quote->setCost($quote->getCost() + $address->getCost());
            $quote->setBaseCost($quote->getBaseCost() + $address->getBaseCost());
            $quote->setMargin($quote->getMargin() + $address->getMargin());
            $quote->setBaseMargin($quote->getBaseMargin() + $address->getBaseMargin());
        }
    }

    public function order_cancel_after(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order->isAllItemsQtyShipped()){
            $order->addStatusHistoryComment('', false, Testimonial_MageDoc_Model_Source_Order_Supply_Status::RETURNED);
        }else{
            if ($order->getRelationChildId()){
                $order->addStatusHistoryComment($order->getRelationChildRealId(), false, Testimonial_MageDoc_Model_Source_Order_Supply_Status::MODIFIED, Testimonial_MageDoc_Model_Source_Order_Reason::ORDER_EDIT);
            }else{
                $order->addStatusHistoryComment('', false, Testimonial_MageDoc_Model_Source_Order_Supply_Status::CANCELED);
            }
        }
    }

    public function sales_order_creditmemo_refund(Varien_Event_Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();
        if ($order->isAllItemsQtyShipped()){
            if ($order->getSubtotalRefunded() >= $order->getSubtotalInvoiced()){
                $order->addStatusHistoryComment('', false, Testimonial_MageDoc_Model_Source_Order_Supply_Status::RETURNED);
            }else{
                $order->addStatusHistoryComment('', false, Testimonial_MageDoc_Model_Source_Order_Supply_Status::PARTIALLY_RETURNED);
            }
        }else{
            if ($order->getSubtotalRefunded() >= $order->getSubtotalInvoiced()){
                $order->addStatusHistoryComment('', false, Testimonial_MageDoc_Model_Source_Order_Supply_Status::CANCELED);
            }
        }
    }

    public function sales_convert_order_item_to_quote_item(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $orderItem = $event->getOrderItem();
        $quoteItem = $event->getQuoteItem();
        if ($orderItem->getOriginalCost() != $orderItem->getCost()){
            $quoteItem->setCustomCost($orderItem->getCost());
        }
        if ($orderItem->getOriginalPrice() != $orderItem->getPrice()){
            $quoteItem->setCustomPrice($orderItem->getPrice());
            $quoteItem->setOriginalCustomPrice($orderItem->getPrice());
        }
    }

    public function bookkeeping_journal_grid_prepare_columns_after(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();

        if (($customer = $block->getCustomer()) && $customer->getId()){
            $block->addColumnAfter('supply_status', array(
                'header'       => $block->__('Supply Status'),
                'index'        => 'supply_status',
                'filter_index' => 'order.supply_status',
                'type'         => 'options',
                'options'      => Mage::getSingleton('magedoc/source_order_supply_status')->getOptionArray()
            ), 'order_status');
        }
    }

    public function admin_permissions_role_prepare_save(Varien_Event_Observer $observer)
    {
        $role = $observer->getObject();
        $request = $observer->getRequest();
        $role->setVisibleOrderStatuses(implode(',',$request->getParam('visible_order_statuses', array())));
        $role->setVisibleOrderSupplyStatuses(implode(',',$request->getParam('visible_order_supply_statuses', array())));
    }

    public function adminhtml_customer_prepare_save(Varien_Event_Observer $observer)
    {
        $data = $observer->getRequest()->getPost();
        $customer = $observer->getCustomer();

        if (isset($data['vehicle']['_template_'])) {
            unset($data['vehicle']['_template_']);
        }

        $modifiedVehicles = array();
        if (!empty($data['vehicle'])) {
            foreach (array_keys($data['vehicle']) as $index) {
                $vehicle = $customer->getVehicleItemById($index);
                if (!$vehicle) {
                    $vehicle = Mage::getModel('magedoc/customer_vehicle');
                }

                $vehicle->addData($data['vehicle'][$index]);

                if ($vehicle->getId()) {
                    $modifiedVehicles[] = $vehicle->getId();
                } else {
                    $customer->addVehicle($vehicle);
                }
            }

            foreach ($customer->getVehiclesCollection() as $customerVehicle) {
                if ($customerVehicle->getId() && !in_array($customerVehicle->getId(), $modifiedVehicles)) {
                    $customerVehicle->setData('_deleted', true);
                }
            }
        }
    }

    public function magedoc_adminhtml_customer_edit_tabs($observer)
    {
        $tabs = $observer->getTabs();
        if (Mage::registry('current_customer')->getId()) {
            $tabs->addTabAfter('order_items', array(
                'label'     => Mage::helper('magedoc')->__('Order Items'),
                'class'     => 'ajax',
                'url'       => $tabs->getUrl('magedoc/adminhtml_supply/customerGrid', array('ducument_type' => Testimonial_MageDoc_Block_Adminhtml_Supply_Document_Grid::DOCUMENT_TYPE_CUSTOMER, 'reference' => Mage::registry('current_customer')->getId())),
            ), 'orders');

            $tabs->addTabAfter('vehicles', array(
                'label'     => Mage::helper('magedoc')->__('Vehicles'),
                'content'   => $tabs->getLayout()->createBlock('magedoc/adminhtml_customer_edit_tab_vehicles')->initForm()->toHtml(),
            ), 'addresses');
        }
    }
}
