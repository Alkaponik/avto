<?php

class Phoenix_AjaxNav_Model_Observer
{
    public function checkout_cart_add_product_complete(Varien_Event_Observer $observer)
    {
        $cart = Mage::getSingleton('checkout/cart');
        $isAjax = $observer->getRequest()->getParam('is_ajax');
        if (!Mage::helper('ajaxnav')->isEnabled()
                || !$isAjax
                || $cart->getQuote()->getHasError()){
            return;
        }
        Mage::getSingleton('checkout/session')->setNoCartRedirect(true);
        $this->_sendResponse($observer->getResponse(), $observer->getProduct());

//        $popupBlock = $this->_getPopupBlock()
//                ->setProduct($observer->getProduct());
//
//        $observer->getResponse()->setBody($popupBlock->toHtml());
        return $this;
    }

    public function controller_action_postdispatch_checkout_cart_add(Varien_Event_Observer $observer)
    {
        $action = $observer->getControllerAction();
        $isAjax = $action->getRequest()->getParam('is_ajax');
        if (!Mage::helper('ajaxnav')->isEnabled()
                || !$isAjax){
            return;
        }
        $response = $action->getResponse();
        if ($response->getHttpResponseCode() == 302) {
            //Mage::app()->getLayout()->getUpdate()->addHandle('default');
            //$action->initLayoutMessages(array('catalog/session', 'core/session', 'checkout/session'));
            $headers = $response->getHeaders();
            foreach ($headers as $header){
                if ($header['name'] == 'Location'){
                    $location = $header['value'];
                    break;
                }
            }
            if (!isset($location)){
                return;
            }
            $this->_sendResponse($response, null, $location);
        }
    }

    protected function _getPopupBlock()
    {
        return $popupBlock = Mage::app()->getLayout()->createBlock(
                'core/template',
                'add_to_cart_pupup',
                array('template'      => 'ajaxnav/popup.phtml'));
    }

    protected function _sendResponse($response, $product, $location = null)
    {
        $layout = Mage::app()->getLayout();
        $update = $layout->getUpdate();
        if (!is_null($location)){
            $result = Mage::helper('core')->jsonEncode(
                    array('location' => $location));
        }elseif($product) {
            $update->addHandle('ajaxnav_cart_add');
            $update->addHandle('ajaxnav_cart_add_block_minicart');
            $update->load();
            $layout->generateXml();
            $layout->generateBlocks();
            if ($popupBlock = $layout->getBlock('add-product-popup')){
                $popupBlock->setProduct($product);
            }
            $result = $layout->getBlock('content')->toHtml();
        }
        $response->clearHeader('Location')
                    ->setHttpResponseCode(200)
                    ->setBody($result);
    }

    public function controller_action_layout_load_before(Varien_Event_Observer $observer)
    {
        $layout = $observer->getLayout();
        $action = $observer->getAction();
        $update = $layout->getUpdate();
        if ($action->getFullActionName() == 'catalog_category_view'
                && $action->getRequest()->getParam('is_ajax')){
            $update->resetHandles();
            $update->addHandle('ajaxnav_category_index');
        }
    }
}