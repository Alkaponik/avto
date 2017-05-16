<?php

class Phoenix_Multipletablerates_Model_Rule_Condition_Rate extends Mage_Rule_Model_Condition_Abstract
{
    
    /**
     * Load attribute options
     *
     * @return Mage_CatalogRule_Model_Rule_Condition_Product
     */
    public function loadAttributeOptions()
    {

        $attributes = 
            Mage::getSingleton('phoenix_multipletablerates/carrier_multipletablerates')->getConditionNames();

        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'package_weight':
            case 'package_value':
            case Phoenix_Multipletablerates_Model_Carrier_Multipletablerates::PACKAGE_VALUE_INCL_TAX_CONDITION:
            case 'package_qty':
                return 'numeric';

            case Phoenix_Multipletablerates_Model_Carrier_Multipletablerates::TRANSPORT_TYPE_CONDITION:
                return 'select';
        }
        return 'string';
    }

    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case Phoenix_Multipletablerates_Model_Carrier_Multipletablerates::TRANSPORT_TYPE_CONDITION:
                return 'select';
        }
        return 'text';
    }

    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case Phoenix_Multipletablerates_Model_Carrier_Multipletablerates::TRANSPORT_TYPE_CONDITION:
                    $options = Mage::getSingleton('phoenix_multipletablerates/source_transport_type')
                        ->toOptionArray();
                    break;
                default:
                    $options = array();
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    /**
     * Validate Rate Rule Condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $rate = $object;
        
        return parent::validate($rate);
    }

    public function toOptionArray()
    {
        $options = $this->getDefaultOperatorOptions();
        $operators = array();

        foreach ($options as $value => $label) {
            $operators[$value] = array(
                'label' => $label,
                'value' => $value
                );
        }
        return $operators;
    }

    public function getDefaultOperatorOptions()
    {
        if (null === $this->_defaultOperatorOptions) {
            $this->_defaultOperatorOptions = array(
                '=='  => Mage::helper('rule')->__('is'),
                '!='  => Mage::helper('rule')->__('is not'),
                '>='  => Mage::helper('rule')->__('equals or greater than'),
                '<='  => Mage::helper('rule')->__('equals or less than'),
                '>'   => Mage::helper('rule')->__('greater than'),
                '<'   => Mage::helper('rule')->__('less than'),
            );
        }
        return $this->_defaultOperatorOptions;
    }
}
