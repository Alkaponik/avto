<?php

/**
 * CatalogSearch layer attribute filter
 *
 */
class Phoenix_LayeredNav_Model_CatalogSearch_Layer_Filter_Attribute extends Phoenix_LayeredNav_Model_Catalog_Layer_Filter_Attribute
{
    /**
     * Check whether specified attribute can be used in LN
     *
     * @param Mage_Catalog_Model_Resource_Eav_Attribute  $attribute
     * @return bool
     */
    protected function _getIsFilterableAttribute($attribute)
    {
        return $attribute->getIsFilterableInSearch();
    }

}
