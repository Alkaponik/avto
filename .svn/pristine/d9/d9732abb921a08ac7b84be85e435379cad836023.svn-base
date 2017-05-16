<?php

class Phoenix_Brands_Model_Eav_Entity_Attribute_Source_Override extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    public function getAttribute()
    {
        return Mage::helper('phoenixbrands')->getBrandAttribute($this->_attribute->getStoreId());
    }
}
