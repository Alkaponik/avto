<?php

class Phoenix_Brands_Model_Catalog_Category_Indexer_Product_Brand extends Mage_Index_Model_Indexer_Abstract
{
    protected function _construct()
    {
        $this->_init('phoenixbrands/catalog_category_indexer_product_brand');
    }

    public function getName()
    {
        return Mage::helper('phoenixbrands')->__('Brand Category Products');
    }

    /**
     * Get Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return Mage::helper('phoenixbrands')->__('Sale Products category/product associations');
    }

    /**
     * Register indexer required data inside event object
     *
     * @param   Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        return $this;
    }

    /**
     * Process event based on event state data
     *
     * @param   Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        return $this;
    }
}