<?php

class Testimonial_MageDoc_Model_Mysql4_Retailer_Data_Import_Preview_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('magedoc/retailer_data_import_preview');
    }

    protected function _beforeLoad()
    {
        $this->joinExtendedTable();
        return parent::_beforeLoad();
    }

    public function joinExtendedTable()
    {
        $this->getSelect()
            ->joinLeft(
                array('extended' => $this->getTable('magedoc/import_retailer_data_extended_base')),
                'extended.data_id = main_table.data_id',
                'data'
            );
        return $this;
    }

    protected function _afterLoad()
    {
        foreach($this->_items as &$item) {
           $extendedData = unserialize($item->getData('data'));

            $extendedData = !is_array($extendedData) ? array() : $extendedData ;
            $item->addData($extendedData);
        }

        return parent::_afterLoad();
    }
}
