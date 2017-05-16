<?php
class Testimonial_MageDoc_Model_Retailer_Data_Import_Settings_Rule
    extends Mage_Rule_Model_Abstract
{
    protected $_retailer;

    protected function _construct()
    {
        $this->_init('magedoc/retailer_data_import_settings_rule', 'rule_id');
    }

    public function setRetailer(Testimonial_MageDoc_Model_Retailer $retailer)
    {
        $this->_retailer = $retailer;
    }
        
    public function getRetailer()
    {
        return $this->_retailer;
    }

    public function getActionsInstance()
    {
        return null;
    }

    public function getConditionsInstance()
    {
        return Mage::getModel('magedoc/retailer_data_import_settings_rule_condition_combine');
    }

    public function getActions()
    {
        return null;
    }

}
