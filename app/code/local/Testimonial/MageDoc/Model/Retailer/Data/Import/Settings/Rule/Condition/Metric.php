<?php

class Testimonial_MageDoc_Model_Retailer_Data_Import_Settings_Rule_Condition_Metric extends Mage_Rule_Model_Condition_Abstract
{
    protected $_objectForValidate;
    protected $_session;

    public function loadAttributeOptions()
    {
        $attributes = array(
            'invalid_records_count' => Mage::helper('magedoc')->__('Invalid records count'),
            'invalid_records_percent' => Mage::helper('magedoc')->__('Invalid records percent'),
            'valid_records' => Mage::helper('magedoc')->__('Valid records count'),
            'valid_records_percent' => Mage::helper('magedoc')->__('Valid records percent'),
            'records_with_old_brands' => Mage::helper('magedoc')->__('Linked to supplier count'),
            'linked_to_supplier_percent' => Mage::helper('magedoc')->__('Linked to supplier percent'),
            'records_linked_to_directory' => Mage::helper('magedoc')->__('Linked to directory count'),
            'linked_to_directory_percent' => Mage::helper('magedoc')->__('Linked to directory percent'),
            'new_brands' => Mage::helper('magedoc')->__('New brands count'),
            'new_brand_percent' => Mage::helper('magedoc')->__('New brands percent'),
        );

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
        return 'numeric';
    }

    public function getValueElementType()
    {
        return 'text';
    }

    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                /*case 'payment_method':
                    $options = Mage::getModel('adminhtml/system_config_source_payment_allmethods')
                        ->toOptionArray();
                    break;*/

                default:
                    $options = array();
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    /**
     * Validate Address Rule Condition
     *
     * @param Varien_Object $session
     * @return bool
     */
    public function validate(Varien_Object $session)
    {
        $this->setSession($session);
        $objectForValidate = $this->_getObjectToValidate();
        return parent::validate($objectForValidate);
    }

    public function setSession($session)
    {
        $this->_session = $session;
    }

    /**
     * @return Testimonial_MageDoc_Model_Retailer_Data_Import_Session
     */

    public function getSession()
    {
        return $this->_session;
    }

    public function loadOperatorOptions()
    {
        $this->setOperatorOption(array(
            '=='  => Mage::helper('rule')->__('is'),
            '!='  => Mage::helper('rule')->__('is not'),
            '>='  => Mage::helper('rule')->__('equals or greater than'),
            '<='  => Mage::helper('rule')->__('equals or less than'),
            '>'   => Mage::helper('rule')->__('greater than'),
            '<'   => Mage::helper('rule')->__('less than'),
        ));
        return $this;
    }

    public function validateAttribute($validatedValue){
        $result = parent::validateAttribute($validatedValue);
        if (!$result) {
            $attrName = $this->getAttributeName();
            $message = MAge::helper('magedoc')->__("%s is %s but expected %s %s", $attrName, $validatedValue, $this->getOperatorForValidate(), $this->getValueParsed());
            $this->getSession()->addError(null, null, null, array($message));
        }
        return $result;
    }

    protected function _getObjectToValidate()
    {
        if(!$this->_objectForValidate){

            $this->_objectForValidate = new Varien_Object();

            foreach ($this->getAttributeOption() as $key => $value) {
                $this->_objectForValidate->setData($key, $this->_getMetricValue($key));
            }
        }
        return $this->_objectForValidate;
    }

    protected function _getMetricValue($metricCode)
    {
        return $this->getSession()->getDataUsingMethod($metricCode);
    }

}
