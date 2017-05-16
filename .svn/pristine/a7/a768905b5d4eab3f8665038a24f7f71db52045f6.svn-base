<?php

class Testimonial_FlatCatalog_Model_Resource_Product_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('flatcatalog/product');
    }

    public function addAttributeToSelect($field)
    {
        return parent::addFieldToSelect($field);
    }

    public function setStore($storeId = null)
    {
        return $this;
    }

    public function addMinimalPrice()
    {
        return $this;
    }

    public function addFinalPrice()
    {
        return $this;
    }

    public function addTaxPercents()
    {
        return $this;
    }

    public function addStoreFilter($storeId = null)
    {
        return $this;
    }

    public function addUrlRewrite()
    {
        return $this;
    }

    public function addCountToCategories($categoryCollection)
    {
        return $this;
    }

    public function addAttributeToFilter($attributeCode, $condition)
    {
        return $this->addFieldToFilter($attributeCode, $condition);
    }
}