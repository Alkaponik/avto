<?php

class Phoenix_Multipletablerates_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_CONFIG_RATE_CONDITIONS_PATH = 'carriers/multipletablerates/rate_additional_conditions';
    const XML_CONFIG_CONDITION_OPERATOR_PATH = 'carriers/multipletablerates/condition_operator';

    public function getRateConditionCallback($conditionName)
    {
        $callbackPath = Phoenix_Multipletablerates_Model_Source_Shipping_RateCondition::SHIPPING_RATE_CONDITION_PATH . '/' . $conditionName . '/callback';
        return (string)Mage::getConfig()->getNode($callbackPath);
    }

    public function getEnabledRateConditionCallbacks()
    {
        $callbacks = array();
        $conditions = Mage::getStoreConfig(self::XML_CONFIG_RATE_CONDITIONS_PATH);
        if (!is_array($conditions)){
            if ($conditions){
                $conditions = explode(',',$conditions);
            }else{
                $conditions = array();
            }
        }
        foreach ($conditions as $condition){
            $callbacks[$condition] = $this->getRateConditionCallback($condition);
        }
        return $callbacks;
    }

    public function getConditionOperator()
    {
        return html_entity_decode(Mage::getStoreConfig(self::XML_CONFIG_CONDITION_OPERATOR_PATH));
    }

    public function getConditionOperatorText()
    {
        $options = Mage::getSingleton('phoenix_multipletablerates/rule_condition_rate')
            ->toOptionArray();
        $operator = $this->getConditionOperator();
        return isset($options[$operator])
            ? $options[$operator]['label']
            : '';
    }
}
?>