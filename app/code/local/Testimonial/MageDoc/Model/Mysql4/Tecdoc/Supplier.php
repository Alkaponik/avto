<?php

class Testimonial_MageDoc_Model_Mysql4_Tecdoc_Supplier extends Testimonial_MageDoc_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('magedoc/tecdoc_supplier', 'SUP_ID');
    }

    protected function _prepareFullSelect($select)
    {
        $mainTable = $this->getMainTable();

        $select->joinLeft(
            array('td_supplierLogo' => $this->getTable('magedoc/tecdoc_supplierLogo')),
            "$mainTable.SUP_ID = td_supplierLogo.SLO_SUP_ID AND SLO_LNG_ID = 255",
            array('SLO_ID')
        );

        return $select;
    }
}
