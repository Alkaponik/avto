<?php

abstract class Testimonial_MageDoc_Block_Adminhtml_Widget_Grid_Supply_Abstract 
    extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_collection;
    protected $_collectionModelName;

    public function __construct() 
    {
        parent::__construct();
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->_defaultFilter = array();
        $statusOptions = Mage::getModel('magedoc/source_suppliedType')->getOptionArray();
        if ($statusFilterValue = key($statusOptions)){
            $this->_defaultFilter['status'] = $statusFilterValue;
        }
        if (!Mage::getSingleton('admin/session')->isAllowed('magedoc/orders/actions/show_all')){
            $this->_defaultFilter['manager'] = Mage::getSingleton('admin/session')->getUser()->getId();
        }
        $this->setColumnRenderers(
            array(
                'action'    => 'magedoc_system/adminhtml_widget_grid_column_renderer_action',
            ));
        $this->setCountTotals(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_collectionModelName);

        $supplyDateExpr = new Zend_Db_Expr('IFNULL(main_table.supply_date, order.created_at)');
        $collection->getSelect()
            ->joinInner(array('order' => $collection->getTable('sales/order_grid')),
                'order.entity_id = main_table.order_id AND order.created_at > \'2016-01-01 00:00:00\'',
                array('order_increment_id' => 'order.increment_id',
                    'order_status' => 'order.status',
                    'order_supply_status' => 'order.supply_status',
                    'manager_id' => 'order.manager_id',
                    'supply_date' => $supplyDateExpr
                ));

        $collection->addFieldToFilter('order.status',
            array('in' => Mage::helper('magedoc/supply')->getVisibleOrderStatuses()));
        $collection->addFieldToFilter('order.supply_status',
            array('in' => Mage::helper('magedoc/supply')->getVisibleOrderSupplyStatuses()));
        $collection->addFilterToMap('supply_date', $supplyDateExpr);
        $this->_prepareCollectionAfter($collection);


        $this->setCollection($collection);
        parent::_prepareCollection();
    }

    protected function _prepareCollectionAfter($collection)
    {
    }

    protected function _prepareColumns()
    {
        $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();

        $this->addColumn('ean', array(
            'header' => Mage::helper('magedoc')->__('EAN'),
            'align' => 'center',
            'width' => '50px',
            'index' => 'ean',
            'filter_condition_callback' => array($this, 'getEANFilterCallback'),
            'sortable'  => false
        ));

        $this->addColumn('order_id', array(
            'header'    => Mage::helper('magedoc')->__('Order #'),
            'align'     => 'center',
            'width'     => '50px',
            'index'     => 'order_increment_id',
            'filter_index' => 'order.increment_id',
            'type'   => 'action',
            'actions'   => array(
                array(
                    'caption' => '{{@order_increment_id}}',
                    'url'     => array(
                        'base'=>'*/sales_order/view',
                    ),
                    'field' =>  'order_id',
                    'value_index'   =>  'order_id'
                )
            )
        ));

        $this->addColumnAfter('manager', array(
            'header' => Mage::helper('magedoc')->__('Manager'),
            'index' => 'manager_id',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('magedoc/source_orderManager')->getOptionArray(),
        ), 'supply_status');

        $orderStatuses = Mage::getSingleton('sales/order_config')->getStatuses();
        $visibleOrderStatuses = Mage::helper('magedoc/supply')->getVisibleOrderStatuses();
        $orderStatuses = array_intersect_key($orderStatuses, array_combine($visibleOrderStatuses, $visibleOrderStatuses));

        $this->addColumn('order_status', array(
            'header'    => Mage::helper('magedoc')->__('Order Status'),
            'align'     => 'center',
            'type'      => 'options',
            'width'     => '40px',
            'index'     => 'order_status',
            'filter_index' => 'order.status',
            'options'   => $orderStatuses,
        ));

        $orderSupplyStatuses = Mage::getSingleton('magedoc/source_order_supply_status')->getOptionArray();
        $visibleOrderSupplyStatuses = Mage::helper('magedoc/supply')->getVisibleOrderSupplyStatuses();
        $orderSupplyStatuses = array_intersect_key($orderSupplyStatuses, array_combine($visibleOrderSupplyStatuses, $visibleOrderSupplyStatuses));

        $this->addColumn('order_supply_status', array(
            'header'    => Mage::helper('magedoc')->__('Supply Status'),
            'align'     => 'center',
            'type'      => 'options',
            'width'     => '40px',
            'index'     => 'order_supply_status',
            'filter_index' => 'order.supply_status',
            'options' => $orderSupplyStatuses,
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('magedoc')->__('Name'),
            'align'     => 'left',
            'index'     => 'name',
            'type'      => 'text',
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('magedoc')->__('Status'),
            'index'     => 'supply_status',
            'type'      => 'options',
            'width'     => '140px',
            'filter_index' => 'main_table.supply_status',
            'element_style'=> 'min-width:80px;width:100%;',
            'renderer'  => 'magedoc/adminhtml_widget_grid_column_renderer_options_select',
            'options'   => Mage::getModel('magedoc/source_suppliedType')->getOptionArray(),
            'internal_options' => Mage::getModel('magedoc/source_suppliedType')->getOptionArray(),
            'totals_label' => '',
        ));

        $this->addColumn('retailer_id', array(
            'header'    => Mage::helper('magedoc')->__('Retailer'),
            'type'      => 'options',
            'index'     => 'retailer_id',
            'filter_index' => 'main_table.retailer_id',
            'width'     => '120px',
            'renderer'  => 'magedoc/adminhtml_widget_grid_column_renderer_options_select',
            'options'   => Mage::getModel('magedoc/source_retailer')->getOptionArray(),
            'internal_options' => Mage::getModel('magedoc/source_retailer')->getOptionArray(),
            'totals_label' => '',
        ));

        $this->addColumn('receipt_reference', array(
            'header'    => Mage::helper('magedoc')->__('Reference'),
            'type'      => 'text',
            'index'     => 'receipt_reference',
            'width'     => '80px',
            'element_css_class' => 'qty-edit',
            'renderer'  => 'magedoc/adminhtml_widget_grid_column_renderer_text_input',
            'totals_label' => '',
        ));

        $this->addColumn('cost', array(
            'header'    => Mage::helper('magedoc')->__('Cost'),
            'type'      => 'currency',
            'index'     => 'cost',
            'filter_index' => 'cost',
            'width'     => '60px',
            'total'     => 'sum',
            'currency_code' => $currencyCode,
        ));

        /*$this->addColumn('price', array(
            'header'    => Mage::helper('magedoc')->__('Price'),
            'type'      => 'currency',
            'index'     => 'price',
            'width'     => '60px',
            'currency_code' => $currencyCode,
        ));*/

        $this->addColumn('qty_ordered', array(
            'header'    => Mage::helper('magedoc')->__('Qty'),
            'type'      => 'range',
            'column_css_class' => 'a-right',
            'index'     => 'qty_ordered',
            'getter'    => array($this, 'getQtyDecimalValue'),
            'width'     => '40px',
            'total'     => 'sum',
            'frame_callback' => array($this, 'getQtyColumnHtml')
        ));

        $this->addColumn('row_total', array(
            'header'            => Mage::helper('magedoc')->__('Row Total'),
            'type'              => 'text',
            'index'             => 'row_total',
            'width'             => '40px',
            'type'              => 'currency',
            'currency_code'     => $currencyCode,
            'total'     => 'sum',
        ));

        /*$this->addColumn('discount', array(
            'header'        => Mage::helper('magedoc')->__('Discount'),
            'type'          => 'text',
            'index'         => 'discount_percent',
            'width'         => '40px',
            'type'          => 'currency',
            'currency_code' => $currencyCode,
        ));*/

        /*$this->addColumn('row_total', array(
            'header'            => Mage::helper('magedoc')->__('Row Total'),
            'type'              => 'text',
            'index'             => 'row_total',
            'width'             => '40px',
            'type'              => 'currency',
            'currency_code'     => $currencyCode,
        ));*/

        $this->addColumn('qty_reserved', array(
            'header'    => Mage::helper('magedoc')->__('Reserved Qty'),
            'type'      => 'range',
            'index'     => 'qty_reserved',
            'width'     => '80px',
            'element_css_class' => 'qty-edit',
            'renderer'  => 'magedoc/adminhtml_widget_grid_column_renderer_text_input',
            'totals_label' => '',
        ));

        $this->addColumn('qty_supplied', array(
            'header'    => Mage::helper('magedoc')->__('Supplied Qty'),
            'type'      => 'range',
            'index'     => 'qty_supplied',
            'width'     => '80px',
            'element_css_class' => 'qty-edit',
            'renderer'  => 'magedoc/adminhtml_widget_grid_column_renderer_text_input',
            'totals_label' => '',
        ));


        $this->addColumn('supply_date', array(
            'header'    => Mage::helper('magedoc')->__('Supply Date'),
            'type'      => 'text',
            'column_css_class' => 'a-right',
            'index'     => 'supply_date',
            'width'     => '80px',
            'element_css_class' => 'qty-edit',
            'getter'    => array($this, 'getSupplyDateWithRetention'),
            'renderer'  => 'magedoc/adminhtml_widget_grid_column_renderer_text_date',
            'filter'    => 'adminhtml/widget_grid_column_filter_datetime',
            'totals_label' => '',
        ));

        $this->addColumn('information', array(
            'header' => Mage::helper('magedoc')->__('information'),
            'width' => '20px',
            'type'  => 'action',
            'multiple_links'    => true,
            'actions'   => array(
                    array(
                        'caption'   => Mage::helper('magedoc')->__('Information'),
                        'popup'     => true,
                        'class'     => 'information'
                    ),
                    array(
                        'caption'   => Mage::helper('magedoc')->__('Reserve'),
                        'onclick'   => "supplyManager.reserveItem('{{{$this->getId()}_id}}', '{$this->getId()}', '{{qty_ordered}}')",
                        'class'     => 'reserve'
                    ),
                    array(
                        'caption'   => Mage::helper('magedoc')->__('Ship'),
                        'onclick'   => "supplyManager.shipItem('{{{$this->getId()}_id}}', '{$this->getId()}', '{{qty_ordered}}')",
                        'class'     => 'ship'
                    )
                ),
            'filter'    => false,
            'sortable'  => false,
            'art_id'    => 'art_id',
            'renderer' => 'magedoc/adminhtml_widget_grid_column_renderer_action',
            'totals_label' => '',
        ));

        $this->addColumn('return_reference', array(
            'header'    => Mage::helper('magedoc')->__('Ret. Reference'),
            'type'      => 'text',
            'index'     => 'return_reference',
            'width'     => '80px',
            'element_css_class' => 'qty-edit',
            'renderer'  => 'magedoc/adminhtml_widget_grid_column_renderer_text_input',
            'totals_label' => '',
        ));

        return parent::_prepareColumns();
    }

    public function getQtyDecimalValue($row)
    {
        $qty = $row->getData('qty_ordered');
        return $qty * 1;
    }

    public function getSupplyDateWithRetention($row)
    {
        return Mage::helper('magedoc')->getItemSupplyDate($row);
    }

    public function _getSupplyDateWithRetention($row)
    {
        if($row->getData('supply_date') === null){
            return Mage::helper('magedoc')->getDateWithSuppliedRetention();
        }
        if(!$date = strtotime($row->getData('supply_date'))){
            $date = $row->getData('supply_date');
        }
        return date('Y-m-d', $date);
    }

    public function getEANFilterCallback($collection, $column)
    {
        if($column->getFilter()->getValue()){
            $numberNormalized = preg_replace('/[^a-zA-Z0-9]*/', '', $column->getFilter()->getValue());
            $this->getCollection()
                    ->addFieldToFilter("artLookUp.ARL_SEARCH_NUMBER",
                            array('eq' => $numberNormalized));
        }
        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true, 'id' => $this->getId()));
    }

    protected function _getArlKind()
    {
        $filter   = $this->getParam($this->getVarNameFilter(), null);
        if (is_string($filter)) {
            $filter = $this->helper('adminhtml')->prepareFilterString($filter);
        }
        $arlKind = is_array($filter) && !empty($filter['ean'])
            ? implode(',', array(1,2,5))
            : implode(',', array(1,2,5));
        return $arlKind;
    }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        $totalObj = Mage::getModel('magedoc_system/grid_totals');
        $this->setTotals($totalObj->countTotals($this));
        return parent::_afterLoadCollection();
    }

    public function getQtyColumnHtml($renderedValue, $row, $column)
    {
        return $renderedValue ? "
        <table cellspacing=\"0\" class=\"qty-table\">
    <tr>
        <td>".Mage::helper('sales')->__('Ordered')."</td>
        <td><strong>".($row->getQtyOrdered()*1)."</strong></td>
    </tr>".
    ((float) $row->getQtyRefunded() ?
    "<tr>
        <td>".Mage::helper('sales')->__('Refunded')."</td>
        <td><strong>".($row->getQtyRefunded()*1)."</strong></td>
    </tr>" : '')
        ."
    </table>
        " : '';
    }

    public function getRowUrl($row)
    {
        return null;
    }

    protected function getFilterValue($varName)
    {
        $filter   = $this->getParam($this->getVarNameFilter(), null);
        if (is_string($filter)) {
            $filter = $this->helper('adminhtml')->prepareFilterString($filter);
        }
        if (is_array($filter) && isset($filter[$varName])){
            return $filter[$varName];
        }
        return null;
    }
}