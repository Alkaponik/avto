<?php

class Testimonial_MageDoc_Model_Import_Entity_Product_Type_Spare_Update
    extends Testimonial_MageDoc_Model_Import_Entity_Product_Type_Spare
{
    protected $_attributesToUpdate = array('price','qty');

    protected function _initAttributes()
    {

        $attributesCache = array();

        foreach (Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter($this->_entityModel->getEntityTypeId()) as $attributeSet) {
            foreach (Mage::getResourceModel('catalog/product_attribute_collection')
                ->setAttributeSetFilter($attributeSet->getId()) as $attribute) {

                $attributeCode = $attribute->getAttributeCode();
                $attributeId   = $attribute->getId();
                if(!in_array($attributeCode, $this->_attributesToUpdate)){
                   continue;
                }

                if ($attribute->getIsVisible() || in_array($attributeCode, $this->_forcedAttributesCodes)) {
                    if (!isset($attributesCache[$attributeId])) {
                        
                        $attributesCache[$attributeId] = array(
                            'id'               => $attributeId,
                            'code'             => $attributeCode,
                            'for_configurable' => $attribute->getIsConfigurable(),
                            'is_global'        => $attribute->getIsGlobal(),
                            'is_required'      => $attribute->getIsRequired(),
                            'frontend_label'   => $attribute->getFrontendLabel(),
                            'is_static'        => $attribute->isStatic(),
                            'apply_to'         => $attribute->getApplyTo(),
                            'type'             => Mage_ImportExport_Model_Import::getAttributeType($attribute),
                            'default_value'    => strlen($attribute->getDefaultValue())
                                                  ? $attribute->getDefaultValue() : null,
                            'options'          => $this->_entityModel
                                                      ->getAttributeOptions($attribute, $this->_indexValueAttributes)
                        );
                    }
                    $this->_addAttributeParams($attributeSet->getAttributeSetName(), $attributesCache[$attributeId]);
                }
            }
            
        }
        return $this;
    }
    
    
    
}
