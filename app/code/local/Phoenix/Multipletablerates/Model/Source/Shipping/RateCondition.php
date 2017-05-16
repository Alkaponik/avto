<?php

class Phoenix_Multipletablerates_Model_Source_Shipping_RateCondition extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const SHIPPING_RATE_CONDITION_PATH = 'global/phoenix_multipletablerates/shipping/rate/conditions';

    public function getAllOptions($withEmpty=true)
    {
        if (!$this->_options) {
            $this->_options = array();
            foreach (Mage::getConfig()->getNode(self::SHIPPING_RATE_CONDITION_PATH)->children() as $condition) {
                $labelPath = self::SHIPPING_RATE_CONDITION_PATH . '/' . $condition->getName() . '/label';
                $valuePath = self::SHIPPING_RATE_CONDITION_PATH . '/' . $condition->getName() . '/value';
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
        $conditions = array();

        foreach (Mage::getConfig()->getNode(self::SHIPPING_RATE_CONDITION_PATH)->children() as $condition) {
            $labelPath = self::SHIPPING_RATE_CONDITION_PATH . '/' . $condition->getName() . '/label';
            $valuePath = self::SHIPPING_RATE_CONDITION_PATH . '/' . $condition->getName() . '/value';
            $value = (string) Mage::getConfig()->getNode($valuePath);
            $conditions[$value] = array(
                'label' => Mage::helper('phoenix_multipletablerates')->__((string) Mage::getConfig()->getNode($labelPath)),
                'value' => $value
            );
        }
        ksort($conditions);

        return $conditions;
    }

    public function getConditionCallbacks()
    {
        $conditionCallbacks = array();
        foreach (Mage::getConfig()->getNode(self::SHIPPING_RATE_CONDITION_PATH)->children() as $condition) {
            $valuePath = self::SHIPPING_RATE_CONDITION_PATH . '/' . $condition->getName() . '/value';
            $callbackPath = self::SHIPPING_RATE_CONDITION_PATH . '/' . $condition->getName() . '/callback';
            $value = (string) Mage::getConfig()->getNode($valuePath);
            $callback = (string) Mage::getConfig()->getNode($callbackPath);
            $conditionCallbacks[$value] = $callback;
        }
        return $conditionCallbacks;
    }
}
