<?php

class Testimonial_MageDoc_Model_Source_Order_Reason
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const STATUS_CHANGE_REASON_PATH = 'global/order_status_change_reason';
    const PRODUCT_HIGH_PRICE = 'product_high_price';
    const PRODUCT_INCORRECT_PRICE = 'product_incorrect_price';
    const PRODUCT_NOT_AVAILABLE = 'product_not_available';
    const PRODUCT_NOT_FOUND = 'product_not_found';
    const PRODUCT_LOW_QUALITY = 'product_low_quality';
    const PRODUCT_MISSING_INFORMATION = 'product_missing_info';
    const DELIVERY_LONG_TERM = 'delivery_long_term';
    const DELIVERY_NOT_AVAILABLE = 'delivery_not_available';
    const DELIVERY_NO_LOCAL_BRANCH = 'delivery_no_local_branch';
    const NO_VAT = 'no_vat';
    const ORDER_ORIGINAL_ID = 'order_original_id';
    const ORDER_EDIT = 'order_edit';
    const OTHER = 'other';

    public function getAllOptions($withEmpty=true)
    {
        if (!$this->_options) {
            $this->_options = array();
            foreach (Mage::getConfig()->getNode(self::STATUS_CHANGE_REASON_PATH)->children() as $type) {
                $labelPath = self::STATUS_CHANGE_REASON_PATH . '/' . $type->getName() . '/label';
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

        foreach (Mage::getConfig()->getNode(self::STATUS_CHANGE_REASON_PATH)->children() as $type) {
            $labelPath = self::STATUS_CHANGE_REASON_PATH . '/' . $type->getName() . '/label';
            $types[$type->getName()] = array(
                'label' => Mage::helper('magedoc')->__((string) Mage::getConfig()->getNode($labelPath)),
                'value' => $type->getName()
            );
        }

        return $types;
    }

    public function getOptionArray($withEmpty=false)
    {
        $optionArray = array();
        $options = $this->getAllOptions($withEmpty);
        foreach ($options as $option) {
            $optionArray[$option['value']] = $option['label'];
        }
        return $optionArray;
    }
}
