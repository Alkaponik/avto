<?php

class Testimonial_MageDoc_Model_Source_Order_Supply_Status 
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const SUPPLY_STATUSES_PATH = 'global/order_supply_status/types';
    const PENDING = 'pending';
    const MODIFIED = 'modified';
    const ARRANGED = 'arranged';
    const RESERVED = 'reserved';
    const ASSEMBLING = 'assembling';
    const ASSEMBLED = 'assembled';
    const SHIPPED = 'shipped';
    const CUSTOMER_NOTIFIED = 'customer_notified';
    const DELIVERED = 'delivered';
    const AWAITING_RETURN = 'awaiting_return';
    const RETURNED = 'returned';
    const PARTIALLY_RETURNED = 'partially_returned';
    const CANCELED = 'canceled';
    const REFUNDED = 'refunded';
    const PARTIALLY_REFUNDED = 'partially_refunded';

    public function getAllOptions($withEmpty=true)
    {
        if (!$this->_options) {
            $this->_options = array();
            foreach (Mage::getConfig()->getNode(self::SUPPLY_STATUSES_PATH)->children() as $type) {
                $labelPath = self::SUPPLY_STATUSES_PATH . '/' . $type->getName() . '/label';
                $this->_options[] = array(
                    'label' => Mage::helper('magedoc')->__((string) Mage::getConfig()->getNode($labelPath)),
                    'value' => $type->getName()
                );
            }            
        }
        $options = $this->_options;
        if ($withEmpty) {
            array_unshift($options, array(
                'value'=>'',
                'label'=>Mage::helper('core')->__('-- Please Select --'))
            );
        }
        return $options;
    }
    
    public function toOptionArray()
    {
        $types = array();

        foreach (Mage::getConfig()->getNode(self::SUPPLY_STATUSES_PATH)->children() as $type) {
            $labelPath = self::SUPPLY_STATUSES_PATH . '/' . $type->getName() . '/label';
            $types[$type->getName()] = array(
                'label' => Mage::helper('magedoc')->__((string) Mage::getConfig()->getNode($labelPath)),
                'value' => $type->getName()
            );
        }

        return $types;
    }

    public function getOptionArray()
    {
        $optionArray = array();
        $options = $this->getAllOptions(false);
        foreach ($options as $option) {
            $optionArray[$option['value']] = $option['label'];
        }
        return $optionArray;
    }
}
