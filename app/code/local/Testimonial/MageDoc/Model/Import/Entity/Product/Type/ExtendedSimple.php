<?php

class  Testimonial_MageDoc_Model_Import_Entity_Product_Type_ExtendedSimple
    extends Mage_ImportExport_Model_Import_Entity_Product_Type_Simple
{

    protected $_requiredAttributesToSkip = array(
        'short_description' => '',
        'weight'            => 0,
        'price'             => 0,
        'description'       => 0
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
}
