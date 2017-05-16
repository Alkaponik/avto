<?php

class Testimonial_MageDoc_Block_Adminhtml_Customer_Edit_Tab_Orders extends Mage_Adminhtml_Block_Customer_Edit_Tab_Orders
{
    public function __construct()
    {
        parent::__construct();
        $this->setCountTotals(true);

        $this->setColumnRenderers(
            array(
                'action'    => 'magedoc_system/adminhtml_widget_grid_column_renderer_action',
            ));
    }

    protected function _prepareCollection()
    {
        $marginPercentExpression = new Zend_Db_Expr('ROUND(main_table.margin/(main_table.grand_total - main_table.margin) * 100, 2)');
        $collection = Mage::getResourceModel('sales/order_grid_collection')
            ->addFieldToSelect('entity_id')
            ->addFieldToSelect('increment_id')
            ->addFieldToSelect('customer_id')
            ->addFieldToSelect('created_at')
            ->addFieldToSelect('grand_total')
            ->addFieldToSelect('order_currency_code')
            ->addFieldToSelect('store_id')
            ->addFieldToSelect('billing_name')
            ->addFieldToSelect('shipping_name')
            ->addFieldToSelect('margin')
            ->addFieldToSelect($marginPercentExpression, 'margin_percent')
            ->addFieldToSelect('shipping_carrier')
            ->addFieldToSelect('payment_method')
            ->addFieldToSelect('status')
            ->addFieldToSelect('supply_status')
            ->addFieldToSelect('manager_id')
            ->addFieldToSelect('last_status_history_comment')
            ->addFieldToFilter('customer_id', Mage::registry('current_customer')->getId())
            ->setIsCustomerMode(true);

        $collection->addFilterToMap('margin_percent', $marginPercentExpression);

        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumnAfter('margin', array(
            'header' => Mage::helper('sales')->__('Margin'),
            'index' => 'margin',
            'type'  => 'currency',
            'currency'  => 'order_currency_code',
            'total'     => 'sum',
        ), 'grand_total');

        $this->addColumnAfter('margin_percent', array(
            'header' => Mage::helper('sales')->__('Margin %'),
            'index' => 'margin_percent',
            'type'  => 'currency',
            'total' => 'avg',
        ), 'margin');

        $this->addColumnAfter('shipping_carrier', array(
            'header' => Mage::helper('magedoc')->__('Shipping Carrier'),
            'index' => 'shipping_carrier',
            'value_index' => 'shipping_carrier',
            'type'  => 'options',
            'show_missing_option_values' => true,
            'width' => '70px',
            'options' => Mage::getSingleton('magedoc/source_availableShippingCarriers')->getOptionArray(),
        ), 'margin_percent');

        $this->addColumnAfter('payment_method', array(
            'header' => Mage::helper('magedoc')->__('Payment Method'),
            'index' => 'payment_method',
            'type'  => 'options',
            'width' => '140px',
            'options' => Mage::helper('magedoc')->getPaymentMethodsHash(),
        ), 'shipping_carrier');

        $this->addColumnAfter('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ), 'payment_method');

        $this->addColumnAfter('supply_status', array(
            'header' => Mage::helper('magedoc')->__('Supply Status'),
            'index' => 'supply_status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('magedoc/source_order_supply_status')->getOptionArray(),
        ), 'status');

        $this->addColumnAfter('manager', array(
            'header' => Mage::helper('magedoc')->__('Manager'),
            'index' => 'manager_id',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('magedoc/source_orderManager')->getOptionArray(),
        ), 'supply_status');

        $this->addColumnAfter('last_status_history_comment', array(
            'header' => Mage::helper('sales')->__('Comment'),
            'index' => 'last_status_history_comment',
        ), 'manager');
        parent::_prepareColumns();
        $this->getColumn('grand_total')->setTotal('sum');

        $actions = array();
        $actions[] = array(
            'caption' => Mage::helper('sales')->__('Details'),
            'class' => 'order-details',
            'url' => array(
                'base' => 'magedoc/adminhtml_supply/document',
                'params' => array(
                    'document_type' => 'order'
                )
            ),
            'field' => 'reference',
            'getter'  => 'getId',
            'data-order_id' => '{{getId()}}'
            //'popup' => true
        );

        $this->addColumn('details',
            array(
                'header'    => Mage::helper('sales')->__('Details'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => $actions,
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
                'force_links'=> true,
                'totals_label'=> ''
            ));

        $this->getColumn('action')->setTotalsLabel('');
        return $this;
    }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        $totalObj = Mage::getModel('magedoc_system/grid_totals');
        $this->setTotals($totalObj->countTotals($this));
        return parent::_afterLoadCollection();
    }

    protected function _toHtml()
    {
        $html = <<<HTML
            <script type="text/javascript">
                $$('.order-details').each(function(e){
                    e.observe('click', function(event){
                    Event.stop(event);
                    var e = event.target;
                    var container = $('order_details_'+e.readAttribute('data-order_id'));
                    if (container){
                        if (container.visible()){
                            container.hide();
                        } else {
                            container.show();
                        }
                        return false;
                    }
                    new Ajax.Request(e.readAttribute('href'),
                    {
                            params: {isAjax: true},
                            onSuccess: function(e, response){
                                var parentTr = e.up('tr');
                                var colspan = parentTr.select('td').length;
                                var orderId = e.readAttribute('data-order_id');
                                parentTr.insert({after: '<tr><td class="order-details-container" colspan="'+colspan+'" id="order_details_'+orderId+'">'+response.responseText+'</td></tr>'});
                            }.curry(e)
                        }
                    )
                    return false;
                    });
                });
            </script>
HTML;
        return parent::_toHtml().$html;
    }
}
