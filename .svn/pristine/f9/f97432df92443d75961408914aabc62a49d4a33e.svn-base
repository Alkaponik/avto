<?php

class Testimonial_MageDoc_Block_Adminhtml_Supply_Document_Grid
    extends Testimonial_MageDoc_Block_Adminhtml_Widget_Grid_Supply_Abstract
{
    const DOCUMENT_TYPE_RECEIPT = 'receipt';
    const DOCUMENT_TYPE_RETURN = 'return';
    const DOCUMENT_TYPE_ORDER = 'order';
    const DOCUMENT_TYPE_CUSTOMER = 'customer';

    protected $_defaultLimit    = 200;
    protected $_filterVisibility = false;
    protected $_pagerVisibility = false;

    public function __construct()
    {
        Mage_Adminhtml_Block_Widget_Grid::__construct();
        $this->_defaultFilter = array();
        $this->setId('supply_document_grid_'.mb_substr($this->getReference(), -1, null, 'UTF-8'));

        $this->setColumnRenderers(
            array(
                'action'    => 'magedoc_system/adminhtml_widget_grid_column_renderer_action',
            ));
        $this->setCountTotals(true);
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->removeColumn('ean');
        $this->removeColumn('status');
        //$this->removeColumn('retailer_id');
        //$this->removeColumn('receipt_reference');
        $this->removeColumn('qty_reserved');
        $this->removeColumn('information');
        //$this->removeColumn('return_reference');
        $this->getColumn('retailer_id')->unsRenderer();
        $this->getColumn('qty_supplied')->unsRenderer(null);
        $this->getColumn('supply_date')->unsRenderer(null);
        $this->getColumn('receipt_reference')->unsRenderer(null);
        $this->getColumn('return_reference')->unsRenderer(null);
        $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        $this->addColumnAfter('supplied_total', array(
            'header'            => Mage::helper('magedoc')->__('Supplied Total'),
            'type'              => 'text',
            'index'             => 'supplied_total',
            'width'             => '40px',
            'type'              => 'currency',
            'currency_code'     => $currencyCode,
            'total'             => 'sum',
        ), 'qty_supplied');
    }

    protected function _prepareCollection()
    {
        $this->prepareCollection();
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    public function prepareCollection()
    {
        $collection = Mage::getResourceModel('magedoc/order_item_collection');
        $columns = array(
            'item_id',
            'order_id',
            'product_id',
            'name',
            'cost',
            'row_total' => new Zend_Db_Expr('main_table.cost * main_table.qty_ordered'),
            'supplied_total' => new Zend_Db_Expr('main_table.cost * main_table.qty_supplied'),
            'qty_ordered',
            'qty_supplied',
            'qty_refunded',
            'qty_shipped',
            'retailer_id',
            'receipt_reference',
            'return_reference',
            'supply_status',
            'created_at' => new Zend_Db_Expr('main_table.created_at')
        );
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns($columns);

        $supplyDateExpr = new Zend_Db_Expr('IFNULL(main_table.supply_date, order.created_at)');
        $idExpr = new Zend_Db_Expr('CONCAT("item_",main_table.item_id)');
        $collection->getSelect()
            ->joinInner(array('order' => $collection->getTable('sales/order_grid')),
                'order.entity_id = main_table.order_id',
                array('order_increment_id'  => 'order.increment_id',
                    'order_status'          => 'order.status',
                    'order_supply_status'   => 'order.supply_status',
                    'manager_id'            => 'order.manager_id',
                    'supply_date'           => $supplyDateExpr,
                ));

        /*$collection->addFieldToFilter('order.status',
            array('in' => Mage::helper('magedoc/supply')->getVisibleOrderStatuses()));
        $collection->addFieldToFilter('order.supply_status',
            array('in' => Mage::helper('magedoc/supply')->getVisibleOrderSupplyStatuses()));*/
        $collection->addFilterToMap('supply_date', $supplyDateExpr);

        $inquiryCollection = Mage::getResourceModel('magedoc/order_inquiry_collection');
        $columns[0] = 'CONCAT("inquiry_",main_table.inquiry_id) AS item_id';
        $columns[2] = "CONCAT('inquiry_', IFNULL(main_table.article_id, CONCAT(main_table.name, main_table.sku))) AS product_id";
        $columns[3] = "CONCAT(main_table.name, ' ', IFNULL(main_table.supplier, ''), ' ',IFNULL(main_table.code, '')) AS name";
        $inquiryCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns($columns);
        $inquiryCollection->getResource()->setIdFieldName('item_id');

        $idExpr = new Zend_Db_Expr('CONCAT("inquiry_",main_table.inquiry_id)');
        $inquiryCollection->getSelect()
            ->joinInner(array('order' => $collection->getTable('sales/order_grid')),
                'order.entity_id = main_table.order_id',
                array('order_increment_id' => 'order.increment_id',
                    'order_status' => 'order.status',
                    'order_supply_status' => 'order.supply_status',
                    'manager_id' => 'order.manager_id',
                    'supply_date' => $supplyDateExpr,
                ));

        /*$inquiryCollection->addFieldToFilter('order.status',
            array('in' => Mage::helper('magedoc/supply')->getVisibleOrderStatuses()));
        $inquiryCollection->addFieldToFilter('order.supply_status',
            array('in' => Mage::helper('magedoc/supply')->getVisibleOrderSupplyStatuses()));*/
        $inquiryCollection->addFilterToMap('supply_date', $supplyDateExpr);

        switch ($this->getDocumentType()) {
            case self::DOCUMENT_TYPE_RETURN:
                $condition = 'return_reference IN (?)';
                $likeCondition = ' OR return_reference LIKE ? OR return_reference LIKE ? OR return_reference LIKE ?';
                break;
            case self::DOCUMENT_TYPE_ORDER:
                $condition = 'order_id IN (?)';
                break;
            case self::DOCUMENT_TYPE_CUSTOMER:
                $condition = 'order.customer_id IN (?)';
                break;
            case self::DOCUMENT_TYPE_RECEIPT:
            default:
                $condition = 'receipt_reference IN (?)';
                $likeCondition = ' OR receipt_reference LIKE ? OR receipt_reference LIKE ? OR receipt_reference LIKE ?';
        }

        $adapter = $collection->getConnection();
        $condition = $adapter->quoteInto($condition, $this->getReference(), null, 1);

        if (isset($likeCondition)) {
            $references = is_array($this->getReference())
                ? $this->getReference()
                : array($this->getReference());

            foreach ($references as $reference) {
                $condition .= $likeCondition;
                $condition = $adapter->quoteInto($condition, $reference . ';%', null, 1);
                $condition = $adapter->quoteInto($condition, '%;' . $reference, null, 1);
                $condition = $adapter->quoteInto($condition, '%;' . $reference . ';%', null, 1);
            }
        }

        $collection->getSelect()->where($condition);
        $inquiryCollection->getSelect()->where($condition);

        $this->_prepareItemsCollectionAfter($collection);
        $this->_prepareInquiriesCollectionAfter($inquiryCollection);

        $select = clone $collection->getSelect();
        $collection->getSelect()->reset();
        $select = clone $collection->getSelect()->union(array($select, $inquiryCollection->getSelect()));
        $collection->getSelect()->reset();
        $collection->getSelect()->from($select);

        $this->_prepareCollectionAfter($collection);

        $this->setCollection($collection);
        return $collection;
    }

    protected function _prepareItemsCollectionAfter($collection)
    {
    }

    protected function _prepareInquiriesCollectionAfter($collection)
    {
    }

    public function getReference()
    {
        return $this->getData('reference')
            ? $this->getData('reference')
            : Mage::app()->getFrontController()->getRequest()->getParam('reference');
    }

    public function getDocumentType()
    {
        return $this->getData('document_type')
            ? $this->getData('document_type')
            : Mage::app()->getFrontController()->getRequest()->getParam('document_type', self::DOCUMENT_TYPE_RECEIPT);
    }
}