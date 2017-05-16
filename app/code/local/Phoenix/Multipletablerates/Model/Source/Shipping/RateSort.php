<?php

class Phoenix_Multipletablerates_Model_Source_Shipping_RateSort extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const SHIPPING_RATE_SORT_PATH = 'global/phoenix_multipletablerates/shipping/rate/sort';

    public function getAllOptions($withEmpty=true)
    {
        if (!$this->_options) {
            $this->_options = array();
            foreach (Mage::getConfig()->getNode(self::SHIPPING_RATE_SORT_PATH)->children() as $type) {
                $labelPath = self::SHIPPING_RATE_SORT_PATH . '/' . $type->getName() . '/label';
                $valuePath = self::SHIPPING_RATE_SORT_PATH . '/' . $type->getName() . '/value';
                $value = (string) Mage::getConfig()->getNode($valuePath);
                $this->_options[$value] = array(
                    'label' => Mage::helper('phoenix_multipletablerates')->__((string) Mage::getConfig()->getNode($labelPath)),
                    'value' => $value
                );
            }
            ksort($this->_options);
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

        foreach (Mage::getConfig()->getNode(self::SHIPPING_RATE_SORT_PATH)->children() as $type) {
            $labelPath = self::SHIPPING_RATE_SORT_PATH . '/' . $type->getName() . '/label';
            $valuePath = self::SHIPPING_RATE_SORT_PATH . '/' . $type->getName() . '/value';
            $value = (string) Mage::getConfig()->getNode($valuePath);
            $types[$value] = array(
                'label' => Mage::helper('phoenix_multipletablerates')->__((string) Mage::getConfig()->getNode($labelPath)),
                'value' => $value
            );
        }
        ksort($types);

        return $types;
    }
    
}
