<?php

class Testimonial_CustomerNotification_Model_Message extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('customernotification/message');
    }

    public function send()
    {
        if ($this->getChannel() == Testimonial_CustomerNotification_Helper_Data::NOTIFICATION_CHANNEL_SMS){
            $this->setAttemptCount($this->getAttemptCount() + 1);
            if (Mage::getModel('smsgateway/api')->sendMessage($this->getText(), $this->getRecipient(), $this->getStoreId())) {
                $this->setSuccessCount($this->getSuccessCount() + 1);
                $this->setStatus(Testimonial_CustomerNotification_Helper_Data::NOTIFICATION_STATUS_SUCCESS);
            }else{
                $this->setStatus(Testimonial_CustomerNotification_Helper_Data::NOTIFICATION_STATUS_FAILED);
                Mage::throwException(Mage::helper('customernotification')->__('Unable to send SMS message'));
            }
        }
        return $this;
    }

    public function getCustomerAddressByTelephone($telephone)
    {
        $config = Mage::getSingleton('eav/config');
        $telephoneAttrId = $config->getAttribute('customer_address', 'telephone')->getId();

        $tableName = Mage::getSingleton('core/resource')->getTableName('customer_address_entity_varchar');
        $adapter = $this->_getReadAdapter();
        $bind    = array(
            'telephone' => $telephone,
            'attribute_id' => $telephoneAttrId
        );
        $select  = $adapter->select()
            ->from($tableName, array('entity_id'))
            ->where('value = :telephone')
            ->where('attribute_id = :attribute_id');
        $customerAddressId = $adapter->fetchOne($select, $bind);
        return Mage::getModel('customer/address')->load($customerAddressId);
    }

    protected function _getReadAdapter()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }
}
