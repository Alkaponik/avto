<?php

class Testimonial_Avtoto_Model_Customer_Session extends Mage_Customer_Model_Session
{
    protected $_isCustomerEmailChecked = null;

    /**
     * Set customer object and setting customer id in session
     *
     * @param   Mage_Customer_Model_Customer $customer
     * @return  Mage_Customer_Model_Session
     */
    public function setCustomer(Mage_Customer_Model_Customer $customer)
    {
        // check if customer is not confirmed
        if ($customer->isConfirmationRequired()) {
            if ($customer->getConfirmation()) {
                return $this->_logout();
            }
        }
        $this->_customer = $customer;
        $this->setId($customer->getId());
        $_SESSION['log'] = $customer->getEmail();
        // save customer as confirmed, if it is not
        if ((!$customer->isConfirmationRequired()) && $customer->getConfirmation()) {
            $customer->setConfirmation(null)->save();
            $customer->setIsJustConfirmed(true);
        }
        return $this;
    }

    public function getCustomer()
    {
        if ($this->_customer instanceof Mage_Customer_Model_Customer) {
            return $this->_customer;
        }

        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
        if ($this->getId()) {
            $customer->load($this->getId());
        } elseif (!empty($_SESSION['log'])){
            $customer->loadByEmail($_SESSION['log']);
        }

        $this->setCustomer($customer);
        return $this->_customer;
    }

    /**
     * Checking customer login status
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return ((bool)$this->getId() && (bool)$this->checkCustomerId($this->getId()))
            || (!empty($_SESSION['log']) && $this->checkCustomerEmail($_SESSION['log']));
    }

    /**
     * Check exists customer (light check)
     *
     * @param int $customerId
     * @return bool
     */
    public function checkCustomerEmail($email)
    {
        if ($this->_isCustomerEmailChecked === null) {
            $this->_isCustomerEmailChecked = $this->_checkCustomerEmail($email);
        }
        return $this->_isCustomerEmailChecked;
    }

    /**
     * Check customer by id
     *
     * @param int $customerId
     * @return bool
     */
    public function _checkCustomerEmail($email)
    {
        $resource = Mage::getResourceSingleton('customer/customer');
        $adapter = Mage::getResourceSingleton('customer/customer')->getReadConnection();
        $bind    = array('customer_email' => $email);
        $select  = $adapter->select()
            ->from($resource->getTable('customer/entity'), 'email')
            ->where('email = :customer_email')
            ->limit(1);

        $result = $adapter->fetchOne($select, $bind);
        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * Logout without dispatching event
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _logout()
    {
        $this->setId(null);
        unset($_SESSION['log']);
        $this->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
        $this->getCookie()->delete($this->getSessionName());
        return $this;
    }
}
