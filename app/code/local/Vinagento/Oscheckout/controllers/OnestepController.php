<?php
require_once BP.DS.'app'.DS.'code'.DS.'core'.DS.'Mage'.DS.'Checkout'.DS.'controllers'.DS.'OnepageController.php';
class Vinagento_Oscheckout_OnestepController extends Mage_Checkout_OnepageController
{
    protected $_name = 'oscheckout';
    
    public function indexAction()
    {
        if (!Mage::helper($this->_name)->isActive()) {
            $this->_redirect('checkout/onepage');
            return;
        }
        
        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->_redirect('checkout/cart');
            return;
        }
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message');
            Mage::getSingleton('checkout/session')->addError($error);
            $this->_redirect('checkout/cart');
            return;
        }
        if (!count(Mage::getSingleton('customer/session')->getCustomer()->getAddresses())) {
            $this->setDefaultCountryId();
        }
        
        $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array(
            '_secure' => true
        )));
        $this->getOnepage()->initCheckout();
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            $this->getOnepage()->saveCheckoutMethod(Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST);
        }
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('One Step Checkout'));
        $this->renderLayout();
    }
    
    public function loginPostAction()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $session = Mage::getSingleton('customer/session');
        $message = '';
        $result  = array();
        
        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost();
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $session->login($login['username'], $login['password']);
                    if ($session->getCustomer()->getIsJustConfirmed()) {
                        $this->_welcomeCustomer($session->getCustomer(), true);
                    }
                }
                catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', Mage::helper('customer')->getEmailConfirmationUrl($login['username']));
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    $session->setUsername($login['username']);
                }
                catch (Exception $e) {
                    $message = $e->getMessage();
                }
            } else {
                $message = $this->__('Login and password are required');
            }
        }
        if ($message) {
            $result['error'] = $message;
        } else {
            $result['redirect'] = 1;
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }
    
    public function reloadReviewAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $result = array();
        try {
            $result['review'] = $this->_getReviewHtml();
        }
        catch (Exception $e) {
        Mage::logException($e);
            $result['error'] = $e->getMessage();
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }
    
    public function switchMethodAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        
        $method = $this->getRequest()->getPost('method');
        if ($this->getRequest()->isPost() && $method)
            $this->getOnepage()->saveCheckoutMethod($method);
    }
    
    public function reloadPaymentAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $result = array();
        try {
            $result['payment'] = $this->_getPaymentMethodsHtml();
        }
        catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }
    
    public function saveBillingAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $result = array();
        $data   = $this->getRequest()->getPost();
        if ($data) {
            if ($data['use_for'] == 'billing') {
                Mage::getSingleton('checkout/session')->setData('use_for_shipping', false);
                try {
                    $this->getQuote()->getBillingAddress()->setCountryId($data['country_id'])->setPostcode($data['postcode'])->save();
                    $this->getQuote()->getShippingAddress()->setCountryId($data['country_id'])->setPostcode($data['postcode'])->save();
                    $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
                    $this->getQuote()->collectTotals()->save();
                    $result['shippingMethod'] = $this->_getShippingMethodsHtml();
                    $result['payment']        = $this->_getPaymentMethodsHtml();
                }
                catch (Exception $e) {
                    $result['error'] = $e->getMessage();
                }
            } else {
                Mage::getSingleton('checkout/session')->setData('use_for_shipping', true);
                try {
                    $this->getQuote()->getBillingAddress()->setCountryId($data['country_id'])->setPostcode($data['postcode'])->save();
                    $result['payment'] = $this->_getPaymentMethodsHtml();
                }
                catch (Exception $e) {
                    $result['error'] = $e->getMessage();
                }
            }
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }
    
    public function saveShippingAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        Mage::getSingleton('checkout/session')->setData('use_for_shipping', true);
        $result = array();
        $data   = $this->getRequest()->getPost();
        if ($data) {
            try {
                $this->getQuote()->getShippingAddress()->setCountryId($data['country_id'])->setPostcode($data['postcode'])->save();
                $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
                $this->getQuote()->collectTotals()->save();
                $result['shippingMethod'] = $this->_getShippingMethodsHtml();
            }
            catch (Exception $e) {
                $result['error'] = $e->getMessage();
            }
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }
    
    public function saveShippingMethodAction() {
  if($this->_expireAjax()) {
      return;
  }
  $result = array();
  $data = $this->getRequest()->getPost();
  if($data) {
      try{
    $return = $this->getOnepage()->saveShippingMethod($data['shipping_method']);
    if(!$return) {
        Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array('request'=>$this->getRequest(), 'quote'=>$this->getOnepage()->getQuote()));
    }
    $result['payment'] = $this->_getPaymentMethodsHtml();
    $result['review'] = $this->_getReviewHtml();
      } catch(Exception $e) {
    $result['error'] = $e->getMessage();
      }
  }
  $this->getResponse()->setBody(Zend_Json::encode($result));
    }
    
    public function savePaymentAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $result = array();
        $data   = $this->getRequest()->getPost();
        if ($data) {
            try {
                $this->getQuote()->getBillingAddress()->setPaymentMethod($data['method'])->save();
                $this->getQuote()->getPayment()->setMethod($data['method'])->save();
                $this->getQuote()->collectTotals();
                $result['test']   = $this->getQuote()->getPayment()->getMethod();
                $result['review'] = $this->_getReviewHtml();
            }
            catch (Exception $e) {
                $result['error'] = $e->getMessage();
            }
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }
    
    public function saveOrderAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        
        if ($this->getRequest()->isPost()) {
            $result = array();
            
            Mage::dispatchEvent('checkout_controller_oscheckout_save_order_after', array(
                'request' => $this->getRequest(),
                'quote' => $this->getOnepage()->getQuote()
            ));
            
            //save BillingAddres
            $billingPostData   = $this->getRequest()->getPost('billing', array());
            $billingData       = $this->_filterPostData($billingPostData);
            $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
            
            if (isset($billingData['email'])) {
                $billingData['email'] = trim($billingData['email']);
            }
            $resultBilling = $this->getOnepage()->saveBilling($billingData, $customerAddressId);
            if (isset($resultBilling['error'])) {
                $result['success'] = false;
                $result['error'] = true;
                $result['error_messages'] = 'Billing Error: ' . $resultBilling['message'];
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }
            if (isset($billingData['use_for_shipping']) && $billingData['use_for_shipping'] == 1) {
                //save ShippingAddress
                $shippingData      = $this->getRequest()->getPost('shipping', array());
                $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
                $resultShipping    = $this->getOnepage()->saveShipping($shippingData, $customerAddressId);
                if (isset($resultShipping['error'])) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = 'Shipping Error: ' . $resultShipping['message'];
                    $this->getResponse()->setBody(Zend_Json::encode($result));
                    return;
                }
            } else {
                $resultShipping = $this->getOnepage()->saveShipping($billingData, $customerAddressId);
                if (isset($resultShipping['error'])) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = 'Shipping Error: ' . $resultShipping['message'];
                    $this->getResponse()->setBody(Zend_Json::encode($result));
                    return;
                }
            }
            //save Shipping Method
            $shippingMethodData   = $this->getRequest()->getPost('shipping_method', '');
            $resultShippingMethod = $this->getOnepage()->saveShippingMethod($shippingMethodData,'');

            try {
                //save Payment
                $paymentData   = $this->getRequest()->getPost('payment', array());
                $resultPayment = $this->getOnepage()->savePayment($paymentData);
                $redirectUrl   = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
                
                if (isset($resultPayment['error'])) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = 'Your order cannot be completed at this time as there is no payment methods available for it.';
                    $this->getResponse()->setBody(Zend_Json::encode($result));
                    return;
                }
            }
            catch (Mage_Payment_Exception $e) {
                if ($e->getFields()) {
                    $result['fields'] = $e->getFields();
                }
                $result['success'] = false;
                $result['error'] = true;
                $result['error_messages'] = 'Payment Method Error:' . $e->getMessage();
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }
            catch (Mage_Core_Exception $e) {
                $result['success'] = false;
                $result['error'] = true;
                $result['error_messages'] = 'Core Exception: ' . $e->getMessage();
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }
            catch (Exception $e) {
                Mage::logException($e);
                $result['success'] = false;
                $result['error'] = true;
                $result['error_messages'] = 'Exception: ' . $this->__('Unable to set Payment Method.');
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }
            
            try {
                if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
                    $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                    if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
                        $result['error'] = $this->__('Please agree to all Terms and Conditions before placing the order.');
                        $this->getResponse()->setBody(Zend_Json::encode($result));
                        return;
                    }
                }
                if ($data = $this->getRequest()->getPost('payment', false)) {
                    $this->getOnepage()->getQuote()->getPayment()->importData($data);
                }
                $this->getOnepage()->saveOrder();
                $redirectUrl       = $this->getOnepage()->getCheckout()->getRedirectUrl();
                $result['success'] = true;
                $result['error']   = false;
            }
            catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
                $this->getOnepage()->getQuote()->save();
                $result['success'] = false;
                $result['error'] = true;
                $result['error_messages'] = $e->getMessage();
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }
            catch (Exception $e) {
                Mage::logException($e);
                Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
                $this->getOnepage()->getQuote()->save();
                $result['success']  = false;
                $result['error']    = true;
                $result['error_messages'] = 'Exception: ' . $this->__('There was an error processing your order. Please contact us or try again later.');
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }
            
            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }
            $this->getOnepage()->getQuote()->save();
            //$result['error'] = '123';
            $this->getCheckout()->unsetData('use_for_shipping');
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }
    
    protected function getCheckout()
    {
        return $this->getOnepage()->getCheckout();
    }
    protected function _getReviewHtml()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('checkout_onepage_review');
        $layout->generateXml();
        $layout->generateBlocks();
        $layout->unsetBlock('payment.form.directpost');
        $output = $layout->getOutput();
        return $output;
    }
    private function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }
    
    private function setDefaultCountryId()
    {
        $defaultCountry = Mage::getStoreConfig('general/country/default');
        $this->getQuote()->getShippingAddress()->setCountryId($defaultCountry)->save();
    }
}