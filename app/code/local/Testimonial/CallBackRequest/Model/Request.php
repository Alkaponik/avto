<?php

class Testimonial_CallBackRequest_Model_Request extends Mage_Core_Model_Abstract
{
    const STATUS_PENDING = 0;
    const STATUS_PROCESSED = 1;

    protected $_cartBlock;

    public function _construct(){
        $this->_init('callbackrequest/request');
    }

    public function getMailUrl()
    {
       return Mage::getUrl('callbackrequest/index/status', array('id' => $this->getId(), 'token' => $this->getToken()));
    }

    public function getStatusUrl()
    {
        return Mage::getUrl('adminhtml/callbackrequest/status', array('id' => $this->getId(), 'token' => $this->getToken(), '_secure' => true, '_nosid' => true));
    }

    protected function _beforeSave()
    {
        if ($this->getManagerId() === null && Mage::getSingleton('admin/session')->isLoggedIn())
        {
            $user = Mage::getSingleton('admin/session')->getUser();
            $this->setManagerId($user->getId());
            $this->setManagerName($user->getName());
        }
    }

    /**
     * Get object created at date affected current active store timezone
     *
     * @return Zend_Date
     */
    public function getCreatedAtDate()
    {
        return Mage::app()->getLocale()->date(
            Varien_Date::toTimestamp($this->getCreatedAt()),
            null,
            null,
            true
        );
    }

    public function getCartContent()
    {
        if (($cartData = $this->getCheckoutCart())){
            $cartData = json_decode($cartData, true);
            if (!empty($cartData['cart_content'])){
                $cartBlock = $this->_getCartBlock()
                    ->setCartInfo($cartData['cart_content'])
                    ->setTotal($cartData['total_price']);
                return $cartBlock->toHtml();
            }
        }
        return '';
    }

    protected function _getCartBlock()
    {
        if (!isset($this->_cartBlock)) {
            $this->_cartBlock = Mage::app()->getLayout()->getBlockSingleton('core/template')
                ->setArea('frontend')
                ->setTemplate('callbackrequest/cart.phtml')
                ->setModuleName('Testimonial_CallBackRequest');
        }
        return $this->_cartBlock;
    }
}