<?php

class Testimonial_MageDoc_Model_Mysql4_Order extends Mage_Sales_Model_Resource_Order
{
    protected function _initVirtualGridColumns()
    {
        parent::_initVirtualGridColumns();
        $this->addVirtualGridColumn(
            'payment_method',
            'sales/order_payment',
            array('entity_id' => 'parent_id'),
            'method'
        );
        return $this;
    }

    public function getUpdateGridRecordsSelect($ids, &$flatColumnsToSelect, $gridColumns = null)
    {
        $select = parent::getUpdateGridRecordsSelect($ids, $flatColumnsToSelect, $gridColumns);
        $select->columns(array('shipping_carrier' => new Zend_Db_Expr('SUBSTRING_INDEX(main_table.shipping_method, \'_\', 1)')));
        $flatColumnsToSelect []= 'shipping_carrier';
        return $select;
    }
}
