<?php
/**
 * @copyright   Copyright (c) 2011 http://magentosupport.net
 * @author		Vlad Vysochansky
 * @license     http://opensource.org/licenses/gpl-license.php  GNU General Public License (GPL)
 */
class Testimonial_CallBackRequest_IndexController extends Mage_Core_Controller_Front_Action
{
    const XML_PATH_CALLBACK_RECIPIENT  = 'callbackrequest/general/recipient_email';
    const XML_PATH_CALLBACK_SENDER     = 'callbackrequest/general/sender_email_identity';
    const XML_PATH_CALLBACK_TEMPLATE   = 'callbackrequest/general/email_template';
    const XML_PATH_ENABLED          = 'callbackrequest/general/enabled';

    protected $postObject;

    public function indexAction()
    {
        //Get current layout state
        $this->loadLayout();
        $this->_initLayoutMessages('core/session');
        if ($this->getRequest()->getParam('is_ajax')){
            $this->getResponse()->setBody($this->getLayout()->getBlock('callback_form')->toHtml());
        }else{
            $this->renderLayout();
        }
    }

    public function statusAction()
    {
        try {
            $id = $this->getRequest()->getParam('id');
            $model = Mage::getModel('callbackrequest/request')->load($id);
            if ($model->getId() && $model->getToken() == $this->getRequest()->getParam('token')){
                $model
                    ->setStatus(Testimonial_CallBackRequest_Model_Request::STATUS_PROCESSED)
                    ->save();
                $this->getResponse()->setBody('<div style="color: green; font-size: 20pt; text-align: center">' .
                    Mage::helper('callbackrequest')->__('Request status changed.') . '</div>');
            }else{
                $this->getResponse()->setBody('<div style="color: red; font-size: 20pt; text-align: center">' .
                    Mage::helper('callbackrequest')->__('The link is not correct.') . '</div>');
            }
        } catch (Mage_Core_Exception $e){
            Mage::getSingleton('customer/session')->addError(Mage::helper('magedoc')->__('Unable to change callback request status: %s', $e->getMessage()));
        }
    }

    public function sendAction()
    {
        $post = $this->getRequest()->getPost();

        if ( $post ) {
            try {
                $this->validateCallBackRequestData($post);
                $request = Mage::getModel('callbackrequest/request');
                $request->setData($post);
                $request->setToken( md5(microtime()) );
                $request->setRemoteAddr(Mage::helper('core/http')->getRemoteAddr(true));
                $request->setStatus(Testimonial_CallBackRequest_Model_Request::STATUS_PENDING);
                $request->setReferer(Mage::helper('core/http')->getHttpReferer(true));

                if( $request->getBucket() === '1' ) {
                    $productName = $request->getProductName();
                    $productUrl = $request->getProductUrl();
                    $comment = $this->__('Customer requested for the callback while adding to a cart <a href="%s">%s</a>', $productUrl, $productName);
                } elseif (!$request->getComment()){
                    $comment = $this->__('Customer requested for the callback from page <a href="%s">%s</a>', $request->getReferer(), htmlentities(urldecode($request->getReferer())));
                }
                if (isset($comment)){
                    $request->setComment($comment);
                }

                $request->save();
                $request->setUrl( $request->getStatusUrl() );

                $this->_mailCallBackRequestData($request);

                $message = Mage::helper('callbackrequest')->__('Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us.');
                if($this->getRequest()->isXmlHttpRequest()) {
		            $this->getResponse()->setBody('<script type="text/javascript">setTimeout(jQuery.fancybox.close,5000);</script><ul class="messages"><li class="success-msg"><ul><li>'.$message.'</li></ul></li></ul>');
		        } else {
	                Mage::getSingleton('customer/session')->addSuccess($message);
	                $this->_redirect('*/*/');
                }

                return;
            } catch (Exception $e) {
                Mage::logException($e);
		        if($this->getRequest()->isXmlHttpRequest())
		        {
                    $message = Mage::helper('callbackrequest')->__('Unable to submit your request. Please, try again later');
		            $this->getResponse()->setBody('<script type="text/javascript">setTimeout(jQuery.fancybox.close,5000);</script><ul class="messages"><li class="error-msg"><ul><li>'.$message.'</li></ul></li></ul>');
		        }
             	else{
	                Mage::getSingleton('customer/session')->addError($message);
	                $this->_redirect('*/*/');
                }
                return;
            }

        } else {
            $this->_redirect('*/*/');
        }
    }

    protected function validateCallBackRequestData( $post )
    {
        $valid = true;
        if (!empty($post['validation'])){
            Mage::throwException(
                Mage::helper('callbackrequest')->__(
                    "Bot detected at %s \n %s",
                    long2ip(Mage::helper('core/http')->getRemoteAddr(true)),
                    print_r($post, true)));
        }elseif( !Zend_Validate::is(trim($post['name']) , 'NotEmpty')
            || !Zend_Validate::is(trim($post['telephone']), 'NotEmpty')) {
            $valid = false;
        }

        if(!$valid) {
            Mage::throwException(Mage::helper('callbackrequest')->__('Callback request data validation error %s', print_r($post, true)));
        }
    }

    protected function _mailCallBackRequestData($request)
    {
        $mailTemplate = Mage::getModel('core/email_template');
        /* @var $mailTemplate Mage_Core_Model_Email_Template */
        $mailTemplate->setTemplateSubject($this->__('Call Back Request'));
        $mailTemplate->setDesignConfig(array('area' => 'frontend'));

        $mailTemplate->sendTransactional(
                Mage::getStoreConfig(self::XML_PATH_CALLBACK_TEMPLATE),
                Mage::getStoreConfig(self::XML_PATH_CALLBACK_SENDER),
                Mage::getStoreConfig(self::XML_PATH_CALLBACK_RECIPIENT),
                null,
                array('call' => $request)
            );
    }
}

