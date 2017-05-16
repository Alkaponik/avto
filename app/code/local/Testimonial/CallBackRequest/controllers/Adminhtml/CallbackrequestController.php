<?php


 class Testimonial_CallBackRequest_Adminhtml_CallbackrequestController extends Mage_Adminhtml_Controller_action
 {
    protected $_publicActions = array('status');

     protected function _initAction() {
         $this->loadLayout()
             ->_setActiveMenu('promo/callbackrequest')
             ->_addBreadcrumb(Mage::helper('adminhtml')->__('Promo'), Mage::helper('adminhtml')->__('Manage call back'));

         return $this;
     }

     public function indexAction()
     {
         $this->_title($this->__('Promotions'))->_title($this->__('Callback Requests'));

         if ($this->getRequest()->getParam('ajax')) {
             $this->_forward('grid');
             return;
         }

         $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('callbackrequest/adminhtml_request'))
             ->renderLayout();
     }

     public function gridAction()
     {
         $this->loadLayout();
         $this->getResponse()->setBody(
             $this->getLayout()->createBlock('callbackrequest/adminhtml_request_grid')->toHtml()
         );
     }

     public function editAction() {
         $id     = $this->getRequest()->getParam('id');
         $request  = Mage::getModel('callbackrequest/request')->load($id);

         if ($request->getId() || $id == 0) {
             $data = Mage::getSingleton('adminhtml/session')->getFormData(true);

             if (!empty($data)) {
                 $request->addData($data);
             }
         }elseif($id){
             $tdManufacturer = Mage::getModel('magedoc/tecdoc_manufacturer')
                 ->load($id);

             $collection = Mage::getResourceModel('magedoc/tecdoc_manufacturer_collection')->addFieldToFilter('MFA_ID', $id)
                 ->joinManufcaturers()->getData();

             $request->setId($tdManufacturer->getId());
             $request->setTitle($tdManufacturer->getMfaBrand());
         }
         Mage::register('manufacturer', $request);

         $this->loadLayout();
         $this->_setActiveMenu('magedoc/items');

         $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Manufacturers'), Mage::helper('adminhtml')->__('Manage Manufacturers'));
         $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
         $this->_addContent($this->getLayout()->createBlock('magedoc/adminhtml_manufacturer_edit'))
             ->_addLeft($this->getLayout()->createBlock('magedoc/adminhtml_manufacturer_edit_tabs'));

         $this->renderLayout();
     }

     public function saveAction() {
         if ($data = $this->getRequest()->getPost()) {


             if(isset($_FILES['logo']['name']) and (file_exists($_FILES['logo']['tmp_name']))) {
                 try {
                     $_FILES['logo']['name'] = Mage::helper('magedoc')
                         ->transliterationString($_FILES['logo']['name']);
                     $uploader = new Varien_File_Uploader('logo');
                     $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png')); // or pdf or anything

                     $uploader->setAllowRenameFiles(false);

                     // setAllowRenameFiles(true) -> move your file in a folder the magento way
                     // setAllowRenameFiles(true) -> move your file directly in the $path folder
                     $uploader->setFilesDispersion(false);

                     $path = Mage::getBaseDir('media').DS.'magedoc'.DS.'avtomarks'.DS;
                     $data['logo'] = $_FILES['logo']['name'];
                 }catch(Exception $e) {

                 }
             }

             $data = array('td_mfa_id' => $this->getRequest()->getParam('id')) + $data;

             $request = Mage::getModel('magedoc/manufacturer');
             $request->setData($data);
             $request->save();

             Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magedoc')->__('Manufacturer was successfully saved'));
             Mage::getSingleton('adminhtml/session')->setFormData(false);

             if ($this->getRequest()->getParam('back')) {
                 $this->_redirect('*/*/edit', array('id' => $request->getTdMfaId()));
                 return;
             }

             $this->_redirect('*/*/');
             return;
         }
         Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magedoc')->__('Unable to find item to save'));
         $this->_redirect('*/*/');
     }

     public function statusAction()
     {
         try {
             $id = $this->getRequest()->getParam('id');
             $request = Mage::getModel('callbackrequest/request')->load($id);
             if ($request->getId()
                 && $request->getToken() == $this->getRequest()->getParam('token')
                 && $request->getStatus() == Testimonial_CallBackRequest_Model_Request::STATUS_PENDING){
                 $request
                     ->setStatus(Testimonial_CallBackRequest_Model_Request::STATUS_PROCESSED)
                     ->save();
                 Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('callbackrequest')->__('Request status changed successfully'));
             }else{
                 Mage::throwException(Mage::helper('callbackrequest')->__('The link is not correct.'));
             }
         } catch (Mage_Core_Exception $e){
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magedoc')->__('Unable to change callback request status: %s', $e->getMessage()));
         }
         $this->_redirect('*/*/index');
     }

     /**
      * Check for is allowed
      *
      * @return boolean
      */
     protected function _isAllowed()
     {
         return Mage::getSingleton('admin/session')->isAllowed('promo/callbackrequest');
     }
 }