<?php

/**
 * Catalog Rule Combine Condition data model
 */
class Testimonial_MageDoc_Model_Retailer_Data_Import_Settings_Rule_Condition_Combine
    extends Mage_Rule_Model_Condition_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('magedoc/retailer_data_import_settings_rule_condition_combine');
    }

    public function getNewChildSelectOptions()
    {
        $metricCondition = Mage::getModel('magedoc/retailer_data_import_settings_rule_condition_metric');
        $metricAttributes = $metricCondition->loadAttributeOptions()->getAttributeOption();
        $metricAttributesValues = array();
        foreach ($metricAttributes as $code=>$label) {
            $metricAttributesValues[] = array(
                'value'=>'magedoc/retailer_data_import_settings_rule_condition_metric|'.$code,
                'label'=>$label);
        }

        $metricChangeCondition = Mage::getModel('magedoc/retailer_data_import_settings_rule_condition_metricChange');
        $metricChangeAttributes = $metricChangeCondition->loadAttributeOptions()->getAttributeOption();
        $metricChangeAttributesValues = array();
        foreach ($metricChangeAttributes as $code=>$label) {
            $metricChangeAttributesValues[] = array(
                'value'=>'magedoc/retailer_data_import_settings_rule_condition_metricChange|'.$code,
                'label'=>$label);
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array('value'=>'magedoc/retailer_data_import_settings_rule_condition_combine', 'label'=>Mage::helper('catalogrule')->__('Conditions Combination')),
            array('label'=>Mage::helper('magedoc')->__('Import Session Metrics'), 'value'=>$metricAttributesValues),
            array('label'=>Mage::helper('magedoc')->__('Import Session Metrics Change'), 'value'=>$metricChangeAttributesValues),
        ));

        return $conditions;
    }
}
