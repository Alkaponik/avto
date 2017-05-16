<?php

class Testimonial_MageDoc_Model_Import_Entity_Product_Type_Simple
    extends Mage_ImportExport_Model_Import_Entity_Product_Type_Simple
{
    /**
     * Attributes' codes which will be allowed anyway, independently from its visibility property.
     *
     * @var array
     */
    protected $_forcedAttributesCodes = array(
        'related_targetrule_position_behavior', 'related_targetrule_position_limit',
        'upsell_targetrule_position_behavior', 'upsell_targetrule_position_limit'
    );

    protected $_requiredAttributesToSkip = array(
        'short_description' => '',
        'weight'            => 0,
        'price'             => 0,
        'description'       => '',
    );

    protected function _isAttributeRequiredCheckNeeded($attrCode)
    {
        return parent::_isAttributeRequiredCheckNeeded($attrCode)
            && !isset($this->_requiredAttributesToSkip[$attrCode]);
    }

    public function addAttributeParams($attrSetName, array $attrParams)
    {
        return $this->_addAttributeParams($attrSetName, $attrParams);
    }

    public function getProductAttributes($attrSetData)
    {
        return $this->_getProductAttributes($attrSetData);
    }

    public function hasAttribute($attrSetName, $attributeCode)
    {
        return isset($this->_attributes[$attrSetName][$attributeCode]);
    }

    /**
     * Prepare attributes values for save: remove non-existent, remove empty values, remove static.
     *
     * @param array $rowData
     * @return array
     */
    public function prepareAttributesForSave(array $rowData, $withDefaultValue = true)
    {
        $resultAttrs = array();

        foreach ($this->_getProductAttributes($rowData) as $attrCode => $attrParams) {
            if (!$attrParams['is_static']) {
                if (isset($rowData[$attrCode]) && strlen($rowData[$attrCode])) {
                    $resultAttrs[$attrCode] =
                        ('select' == $attrParams['type'] || 'multiselect' == $attrParams['type'])
                            ? $attrParams['options'][mb_strtolower($rowData[$attrCode], 'UTF-8')]
                            : $rowData[$attrCode];
                } elseif (array_key_exists($attrCode, $rowData)) {
                    $resultAttrs[$attrCode] = $rowData[$attrCode];
                } elseif ($withDefaultValue && null !== $attrParams['default_value']) {
                    $resultAttrs[$attrCode] = $attrParams['default_value'];
                }
            }
        }
        return $resultAttrs;
    }
}
