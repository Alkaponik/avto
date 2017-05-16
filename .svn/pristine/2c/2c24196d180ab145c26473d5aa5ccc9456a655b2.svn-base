<?php
class Testimonial_MageDoc_Model_Mysql4_Retailer_Data_Import_Session_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('magedoc/retailer_data_import_session');
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

    public function getSelectCountSql()
    {
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->reset(Zend_Db_Select::GROUP);
        $countSelect->reset(Zend_Db_Select::HAVING);
        $countSelect->columns("COUNT(DISTINCT main_table.session_id)");
        return $countSelect;
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