<?php

class Testimonial_MageDoc_Model_Mysql4_Retailer_Data_Import_Source_Config_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('magedoc/retailer_data_import_source_config');
    }

    protected $_retailer;

    public function toOptionArray()
    {
        return $this->_toOptionArray( 'source_id' );
    }

    public function setRetailer($retailer)
    {
        $this->_retailer = $retailer;
        $retailerId      = $retailer->getId();
        if ($retailerId) {
            $this->addFieldToFilter('retailer_id', $retailerId);
        } else {
            $this->_totalRecords = 0;
            $this->_setIsLoaded(true);
        }
        return $this;
    }

    protected function _afterLoad()
    {
        $resource = $this->getResource();
        foreach ($this->getItems() as $item)
        {
            $resource->unserializeFields($item);
        }
    }
}
