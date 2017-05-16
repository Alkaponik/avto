<?php

class Testimonial_CustomerNotification_Adminhtml_CustomerNotification_MessageController extends Mage_Adminhtml_Controller_Action
{
    protected function _initMessage($idFieldName = 'id')
    {
        $id  = $this->getRequest()->getParam($idFieldName);
        $message  = Mage::getModel('customernotification/message');
        $hlp = Mage::helper('customernotification');
        if ($id) {
            $message->load($id);
        }

        Mage::register('customernotification_message', $message);
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('Customer Notification'))->_title($this->__('Messages'));

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('customernotification/adminhtml_message'))
            ->renderLayout();
        return $this;
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_initMessage();
        $this->_title($this->__('CustomerNotification'))->_title($this->__('Messages'));
        
        $hlp = Mage::helper('customernotification');
        $message = Mage::registry('customernotification_message');
        $id = $message->isObjectNew() ? null : $message->getId();

        $this->_title($message->getId() ? $message->getName() : $this->__('Message'));

        $data = Mage::getSingleton('adminhtml/session')->getMessageData(true);
        if (!empty($data)) {
            $message->setData($data);
        }

        $this->_initAction()
            ->_addBreadcrumb($id ? $hlp->__('Edit Message') :  $hlp->__('New Message'), $id ?  $hlp->__('Edit Message') :  $hlp->__('New Message'));

        if ($message->isObjectNew()){
        $this
            ->_addContent($this->getLayout()->createBlock('customernotification/adminhtml_message_edit')
                ->setData('action', $this->getUrl('*/*/save')));
        }

        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($postData = $this->getRequest()->getPost()) {
            $model = Mage::getSingleton('customernotification/message');
            $model->setData($postData);

            try {
                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customernotification')->__('The message has been saved.'));
                $this->_redirect('*/*/');

                return;
            }
            catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customernotification')->__('An error occurred while saving this message.'));
            }

            Mage::getSingleton('adminhtml/session')->setMessageData($postData);
            $this->_redirectReferer();
        }
    }

    public function deleteAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $model = Mage::getSingleton('customernotification/message')
            ->load($id);
        if (!$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customernotification')->__('This message no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        try {
            $model->delete();

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customernotification')->__('The message has been deleted'));
            $this->_redirect('*/*/');

            return;
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customernotification')->__('An error occurred while deleting this message.'));
        }

        $this->_redirectReferer();
    }

    /**
     * Initialize action
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('customernotification/message')
            ->_addBreadcrumb(Mage::helper('customernotification')->__('Customer Notification'), Mage::helper('customernotification')->__('Customer Notification'))
            ->_addBreadcrumb(Mage::helper('customernotification')->__('Messages'), Mage::helper('customernotification')->__('Messages'))
        ;
        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/notification');;
        $action = strtolower($this->getRequest()->getActionName());

        switch ($action) {
            case 'index':
                $aclResource = 'customernotification/message/actions/view';
                break;
            case 'new':
                $aclResource = 'customernotification/message/actions/create';
                break;
            case 'save':
                $aclResource = 'customernotification/message/actions/edit';
                break;
            case 'delete':
                $aclResource = 'customernotification/message/actions/delete';
                break;
            case 'edit':
                $aclResource = 'customernotification/journal/actions/view';
                break;
            default:
                $aclResource = 'customernotification/message';
                break;

        }
        return Mage::getSingleton('admin/session')->isAllowed($aclResource);
    }

    public function sendTrackSmsAction()
    {
        $trackId = $this->getRequest()->getParam('id');
        $track = Mage::getModel('sales/order_shipment_track')->load($trackId);
        if ($track->getId()){
            $track->setShipment(Mage::getModel('magedoc/order_shipment')->load($track->getParentId()));
            if (Mage::helper('customernotification')->sendTrackInformation($track) === Testimonial_CustomerNotification_Helper_Data::NOTIFICATION_STATUS_SUCCESS){
                $track->save();
                $this->getResponse()->setBody($this->__('Notified'));
                return;
            }
        }
        $this->getResponse()->setBody($this->__('Failed'));
    }

    public function sendAction()
    {
        $messageId = $this->getRequest()->getParam('id');
        /* @var $message Testimonial_CustomerNotification_Model_Message */
        $message = Mage::getModel('customernotification/message')->load($messageId);
        if ($message->getId()){
            try{
                $message->send();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customernotification')->__('Message was sent successfully.'));
            }catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customernotification')->__('An error occurred while sending this message.'));
            }
            $message->save();
        }else{
            Mage::getSingleton('adminhtml/session')->addError($this->__('Specified message no longer exists'));
        }
        $this->_forward('index');
    }

    public function getDataByTelephoneAction()
    {
        $telephone = Mage::app()->getRequest()->getParam('value');

        if (!empty($telephone)) {
            $customerAddress = Mage::getModel('customernotification/message')->getCustomerAddressByTelephone($telephone);
            if ($customerAddress->getId()) {
                $name = $customerAddress->getFirstname() . ' ' . $customerAddress->getLastname();
                return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(
                    array('name' => $name, 'customer_id' => $customerAddress->getCustomerId())));
            }
        }
    }

    public function sendMessageAction()
    {
        if ($postData = $this->getRequest()->getPost()) {
            try {
                $model = Mage::getSingleton('customernotification/message');
                $user = Mage::helper('customernotification')->getCurrentUser();
                $additionalData = array(
                    'event'         => 'Custom',
                    'manager_id'    => $user->getId(),
                    'manager_name'  => $user->getFirstname() . ' ' . $user->getLastname(),
                    'status'        => Testimonial_CustomerNotification_Helper_Data::NOTIFICATION_STATUS_PENDING
                );
                $data = array_merge($postData, $additionalData);

                $model->setData($data);

                $model->send();

                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customernotification')->__('The message has been sent.'));
                $this->_redirect('*/*/');

                return;
            }
            catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customernotification')->__('An error occurred while sanding this message.'));
            }

            Mage::getSingleton('adminhtml/session')->setMessageData($postData);
            $this->_redirectReferer();
        }
    }

}
