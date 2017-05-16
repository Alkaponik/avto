<?php

class Testimonial_MageDoc_Block_Adminhtml_Customer_Edit_Tab_Items extends Testimonial_MageDoc_Block_Adminhtml_Supply_Document_Grid
{
    protected $_filterVisibility = true;
    protected $_pagerVisibility = true;
    protected $_defaultSort     = 'created_at';
    protected $_defaultDir      = 'desc';

    public function __construct()
    {
        parent::__construct();
        $this->setId('customer_order_items_grid');
        $this->_defaultFilter = array(
            'qty_lost' => array(
                'from' => '1'
            )
        );
        $this->setUseAjax(true);
        $this->setDocumentType(self::DOCUMENT_TYPE_CUSTOMER);
        $customer = Mage::registry('current_customer');
        $this->setReference($customer->getId());
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->removeColumn('receipt_reference');
        $this->removeColumn('return_reference');
        $this->removeColumn('supply_date');
        $this->removeColumn('supply_date');
        $this->removeColumn('retailer_id');
        $this->removeColumn('order_id');
        $this->removeColumn('manager');
        $this->removeColumn('order_status');
        $this->removeColumn('order_supply_status');
        $this->removeColumn('row_total');
        $this->removeColumn('supplied_total');

        $this->addColumn('qty_shipped',
            array(
                'header' => Mage::helper('magedoc')->__('Shipped Qty'),
                'type' => 'range',
                'index' => 'qty_shipped',
                'width' => '80px',
                'totals_label' => '',
                'filter_condition_callback' =>  array($this, '_havingFilterCallback')
            ));

        $this->addColumn('qty_returned',
            array(
                'header' => Mage::helper('magedoc')->__('Returned Qty'),
                'type' => 'range',
                'index' => 'qty_returned',
                'width' => '80px',
                'totals_label' => '',
                'filter_condition_callback' =>  array($this, '_havingFilterCallback')
            ));

        $this->addColumn('qty_returned_to_warehouse',
            array(
                'header' => Mage::helper('magedoc')->__('Returned To Warehouse Qty'),
                'type' => 'range',
                'index' => 'qty_returned_to_warehouse',
                'width' => '80px',
                'totals_label' => '',
                'filter_condition_callback' =>  array($this, '_havingFilterCallback')
            ));

        $this->addColumn('qty_assembled',
            array(
                'header' => Mage::helper('magedoc')->__('Assembled Qty'),
                'type' => 'range',
                'index' => 'qty_assembled',
                'width' => '80px',
                'totals_label' => '',
                'filter_condition_callback' =>  array($this, '_havingFilterCallback')
            ));

        $this->addColumn('qty_lost',
            array(
                'header' => Mage::helper('magedoc')->__('Lost Qty'),
                'type' => 'range',
                'index' => 'qty_lost',
                'width' => '80px',
                'totals_label' => '',
                'filter_condition_callback' =>  array($this, '_havingFilterCallback')
            ));

        $this->addColumn('value_lost',
            array(
                'header' => Mage::helper('magedoc')->__('Lost Amount'),
                'type' => 'range',
                'index' => 'value_lost',
                'width' => '80px',
                'totals_label' => '',
                'filter_condition_callback' =>  array($this, '_havingFilterCallback'),
                'total'             => 'sum'
            ));

        $this->addColumn('created_at',
            array(
                'header' => Mage::helper('magedoc')->__('Created At'),
                'type' => 'date',
                'index' => 'created_at',
                'width' => '80px',
                'totals_label' => '',
                'filter_condition_callback' =>  array($this, '_havingFilterCallback'),
                'total'             => 'sum'
            ));
    }

    protected function _prepareItemsCollectionAfter($collection)
    {
        $collection->getSelect()->where('main_table.created_at', array('ge' => '2015-03-01 00:00:00'));
    }

    protected function _prepareInquiriesCollectionAfter($collection)
    {
        $collection->getSelect()->where('main_table.created_at', array('ge' => '2015-03-01 00:00:00'));
    }

    /**
     * @var $collection Testimonial_MageDoc_Model_Mysql4_Order_Item_Collection
     */

    public function _prepareCollectionAfter($collection)
    {
        $suppliedExpr = new Zend_Db_Expr('SUM(IF(receipt_reference IS NOT NULL AND receipt_reference != \'\', qty_supplied, 0))');
        $shippedExpr = new Zend_Db_Expr("SUM(IF(order_supply_status IN ('shipped', 'customer_notified', 'awaiting_return', 'returned', 'partially_returned'), qty_shipped, 0))");
        $returnedExpr = new Zend_Db_Expr("SUM(IF(order_supply_status = 'returned', qty_shipped, IF(order_supply_status = 'partially_returned', qty_refunded, 0)))");
        $returnedToWarehouseExpr = new Zend_Db_Expr("
            SUM(
                IF(
                    order_supply_status = 'partially_returned'
                    AND return_reference IS NOT NULL
                    AND NOT return_reference LIKE 'АТО-%'
                    AND return_reference != '',
                    qty_refunded,
                    IF(
                        order_supply_status IN ('returned', 'canceled', 'modified')
                        AND return_reference IS NOT NULL
                        AND NOT return_reference LIKE 'АТО-%'
                        AND return_reference != '',
                        qty_ordered,
                        0
                        )
                    )
                )
        ");
        $assembledExpr = new Zend_Db_Expr("SUM(IF(order_supply_status IN ('assembling', 'assembled'), qty_shipped, 0))");
        $lostExpr = "{$suppliedExpr} + {$returnedExpr} - {$shippedExpr} - {$returnedToWarehouseExpr}";
        $lostValueExpr = "({$suppliedExpr} + {$returnedExpr} - {$shippedExpr} - {$returnedToWarehouseExpr}) * cost";
        $createdAtExpr = new Zend_Db_Expr("MAX(created_at)");

        $collection->getSelect()->columns(array('qty_supplied' => $suppliedExpr));
        $collection->getSelect()->columns(array('qty_shipped' => $shippedExpr));
        $collection->getSelect()->columns(array('qty_returned' => $returnedExpr));
        $collection->getSelect()->columns(array('qty_returned_to_warehouse' => $returnedToWarehouseExpr));
        $collection->getSelect()->columns(array('qty_assembled' => $assembledExpr));
        $collection->getSelect()->columns(array('qty_lost' => $lostExpr));
        $collection->getSelect()->columns(array('value_lost' => $lostValueExpr));

        $collection->addFilterToMap('qty_supplied', $suppliedExpr);
        $collection->addFilterToMap('qty_shipped', $shippedExpr);
        $collection->addFilterToMap('qty_returned', $returnedExpr);
        $collection->addFilterToMap('qty_returned_to_warehouse', $returnedToWarehouseExpr);
        $collection->addFilterToMap('qty_assembled', $assembledExpr);
        $collection->addFilterToMap('qty_lost', "ABS({$lostExpr})");
        $collection->addFilterToMap('value_lost', "{$lostValueExpr}");

        $collection->getSelect()->group('product_id');
        //Mage::log((string)$collection->getSelect());
        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/customerGrid', array('_current'=>true, 'id' => $this->getId()));
    }

    protected function _havingFilterCallback($collection, $column)
    {
        $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
        $cond = $column->getFilter()->getCondition();
        if ($field && $cond){
            $collection->addFieldToHavingFilter($field, $cond);
        }
    }
}