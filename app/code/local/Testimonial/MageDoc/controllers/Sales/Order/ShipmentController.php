<?php
require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml').DS.'Sales'.DS.'Order'.DS.'ShipmentController.php';

class Testimonial_MageDoc_Sales_Order_ShipmentController extends Mage_Adminhtml_Sales_Order_ShipmentController
{
    protected function _setActiveMenu($menuPath)
    {
        parent::_setActiveMenu('magedoc/order');
        return $this;
        
    }

    protected function _getInquiryQtys()
    {
        $qtys = array();
        $data = $this->getRequest()->getParam('shipment');
        
        if (isset($data['inquiries'])) {
            $qtys = $data['inquiries'];
        } 
        return $qtys;
    }

    protected function _initShipment()
    {
        $this->_title($this->__('magedoc'))->_title($this->__('Shipments'));

        $shipment = false;
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $orderId = $this->getRequest()->getParam('order_id');
        if ($shipmentId) {
            $shipment = Mage::getModel('magedoc/order_shipment')->load($shipmentId);
        } elseif ($orderId) {
            $order      = Mage::getModel('magedoc/order')->load($orderId);

            /**
             * Check order existing
             */
            if (!$order->getId()) {
                $this->_getSession()->addError($this->__('The order no longer exists.'));
                return false;
            }
            /**
             * Check shipment is available to create separate from invoice
             */
            if ($order->getForcedDoShipmentWithInvoice()) {
                $this->_getSession()->addError($this->__('Cannot do shipment for the order separately from invoice.'));
                return false;
            }
            /**
             * Check shipment create availability
             */
            if (!$order->canShip()) {
                $this->_getSession()->addError($this->__('Cannot do shipment for the order.'));
                return false;
            }
            $itemSavedQtys    = $this->_getItemQtys();
            $inquirySavedQtys = $this->_getInquiryQtys();
            $shipment = Mage::getModel('magedoc/service_order', $order)
                    ->prepareInquiriesShipment($itemSavedQtys, $inquirySavedQtys);

            $tracks = $this->getRequest()->getPost('tracking');
            if ($tracks) {
                foreach ($tracks as $data) {
                    if (empty($data['number'])) {
                        Mage::throwException($this->__('Tracking number cannot be empty.'));
                    }
                    $track = Mage::getModel('sales/order_shipment_track')
                        ->addData($data);
                    $shipment->addTrack($track);
                }
            }
        }

        Mage::register('current_shipment', $shipment);
        return $shipment;
    }

    /**
     * Add new tracking number action
     */
    public function addTrackAction()
    {
        try {
            $carrier = $this->getRequest()->getPost('carrier');
            $number  = $this->getRequest()->getPost('number');
            $title  = $this->getRequest()->getPost('title');
            $notifyCustomer  = $this->getRequest()->getPost('notify_customer');
            if (empty($carrier) || empty($title)) {
                Mage::throwException($this->__('The carrier needs to be specified.'));
            }
            if (empty($number)) {
                Mage::throwException($this->__('Tracking number cannot be empty.'));
            }
            $shipment = $this->_initShipment();
            if ($shipment) {
                $track = Mage::getModel('sales/order_shipment_track')
                    ->setNumber($number)
                    ->setCarrierCode($carrier)
                    ->setTitle($title)
                    ->setNotifyCustomer($notifyCustomer);
                $shipment->addTrack($track)
                    ->save();

                $this->loadLayout();
                $response = $this->getLayout()->getBlock('shipment_tracking')->toHtml();
            } else {
                $response = array(
                    'error'     => true,
                    'message'   => $this->__('Cannot initialize shipment for adding tracking number.'),
                );
            }
        } catch (Mage_Core_Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $e->getMessage(),
            );
        } catch (Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $this->__('Cannot add tracking number.'),
            );
        }
        if (is_array($response)) {
            $response = Mage::helper('core')->jsonEncode($response);
        }
        $this->getResponse()->setBody($response);
    }

    /**
     * Return grid with shipping items for Ajax request
     *
     * @return Mage_Core_Controller_Response_Http
     */
    public function getShippingItemsGridAction()
    {
        $this->_initShipment();
        return $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('magedoc/adminhtml_order_shipment_packaging_grid')
                ->setIndex($this->getRequest()->getParam('index'))
                ->toHtml()
        );
    }
}
