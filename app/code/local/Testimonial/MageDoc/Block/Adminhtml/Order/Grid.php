<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales orders grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Testimonial_MageDoc_Block_Adminhtml_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    public function __construct()
    {
        parent::__construct();
        if (!Mage::getSingleton('admin/session')->isAllowed('magedoc/orders/actions/show_all')){
            $this->setDefaultFilter(array('manager' => Mage::getSingleton('admin/session')->getUser()->getId()));
        }
        $this->setColumnRenderers(
            array(
                'action'    => 'magedoc_system/adminhtml_widget_grid_column_renderer_action',
            ));
    }

    protected function _prepareCollection()
    {
        $marginPercentExpression = new Zend_Db_Expr('ROUND(main_table.margin/(main_table.grand_total - main_table.margin) * 100, 2)');
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $collection
            ->addFilterToMap('margin_percent', $marginPercentExpression);
        $collection->getSelect()->columns(array('margin_percent' => $marginPercentExpression));
        $this->setCollection($collection);
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumnAfter('telephone', array(
            'header' => Mage::helper('magedoc')->__('Telephone'),
            'index' => 'telephone',
            'type'  => 'text',
            'width' => '70px',
        ), 'shipping_name');

        $this->addColumnAfter('margin_percent', array(
            'header' => Mage::helper('sales')->__('Margin %'),
            'index' => 'margin_percent',
            'type'  => 'currency',
        ), 'grand_total');

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

        $this->addColumnAfter('supply_status', array(
            'header' => Mage::helper('magedoc')->__('Supply Status'),
            'index' => 'supply_status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('magedoc/source_order_supply_status')->getOptionArray(),
            'filter' => 'magedoc_system/adminhtml_widget_grid_column_filter_multiselect',
        ), 'status');

        $this->addColumnAfter('supply_date', array(
            'header' => Mage::helper('magedoc')->__('Supply Date'),
            'index' => 'supply_date',
            'type'  => 'date',
            'width' => '70px',
        ), 'supply_status');

        
        $this->addColumnAfter('manager', array(
            'header' => Mage::helper('magedoc')->__('Manager'),
            'index' => 'manager_id',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('magedoc/source_orderManager')->getOptionArray(),
        ), 'supply_date');

        $this->addColumnAfter('last_status_history_comment', array(
            'header' => Mage::helper('sales')->__('Comment'),
            'index' => 'last_status_history_comment',
        ), 'manager');

        $this->addColumnAfter('shipping_date', array(
            'header' => Mage::helper('magedoc')->__('Shipping Date'),
            'index' => 'shipping_date',
            'type'  => 'date',
            'width' => '70px',
        ), 'supply_date');

        parent::_prepareColumns();

        $this->removeColumn('base_grand_total');
        $this->removeColumn('shipping_name');
        $this->getColumn('billing_name')->setFilterConditionCallback(
            array($this, '_getNameFilterCallback'));
        $this->getColumn('status')->setWidth('105px');

        $actions = $this->getColumn('action')->getActions();
        if (isset($actions[0])){
            $actions[0]['getter'] = 'getId';
        }
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
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/ship')){
            $actions[] = array(
                'caption' => Mage::helper('sales')->__('Ship'),
                'url' => array(
                    'base' => '*/sales_order/ship',
                ),
                'field'   => 'order_id',
                'getter'  => array($this, 'getShipmentOrderId'),
                'confirm' => $this->__('Do you really want ship this order?'),
            );
        }
        $this->getColumn('action')->setActions($actions);
        $this->getColumn('action')->setForceLinks(true);

        return $this;
    }

    protected function _prepareMassaction()
    {
        $this->getMassactionBlock()->addItem('pdfexpendables_assemblies_order', array(
            'label' => Mage::helper('magedoc')->__('Print Expendables and Assemblies'),
            'url' => $this->getUrl('*/sales_order/pdfassemblies',
                array(
                    'mode'  =>  Testimonial_MageDoc_Model_Order_Pdf_Assembly::MODE_ASSEMBLIES
                    ^ Testimonial_MageDoc_Model_Order_Pdf_Assembly::MODE_EXPENDABLES
                )
            ),
        ));
        $this->getMassactionBlock()->addItem('pdfassemblies_order', array(
            'label' => Mage::helper('magedoc')->__('Print Assemblies'),
            'url' => $this->getUrl('*/sales_order/pdfassemblies',
                array(
                    'mode'  =>  Testimonial_MageDoc_Model_Order_Pdf_Assembly::MODE_ASSEMBLIES
                )
            ),
        ));
        $this->getMassactionBlock()->addItem('pdfexpendables_order', array(
            'label' => Mage::helper('magedoc')->__('Print Expendables'),
            'url' => $this->getUrl('*/sales_order/pdfassemblies',
                array(
                    'mode'  =>  Testimonial_MageDoc_Model_Order_Pdf_Assembly::MODE_EXPENDABLES
                )
            ),
        ));
        return parent::_prepareMassaction();
    }

    protected function _getNameFilterCallback($collection, $column)
    {
        $cond = $column->getFilter()->getCondition();
        if(isset($cond['like'])){
            $collection->addFieldToFilter(
                array('billing_name', 'shipping_name'),
                array(
                    array('like' => $cond['like']),
                    array('like' => $cond['like']),
                )
            );
        }
        return $this;
    }

    protected function _toHtml()
    {
        $html = <<<HTML
            <script type="text/javascript">
                $("sales_order_grid_filter_real_order_id").focus();
                $("sales_order_grid_filter_real_order_id").select();
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
            <style type="text/css">
                .order-details-container .hor-scroll { padding-bottom: 0; }
            </style>
HTML;
        return parent::_toHtml().$html;
    }

    public function getShipmentOrderId($row)
    {
        return $row->getSupplyStatus() == Testimonial_MageDoc_Model_Source_Order_Supply_Status::ASSEMBLED
            && in_array(
                $row->getStatus(),
                array(Mage_Sales_Model_Order::STATE_PROCESSING, Mage_Sales_Model_Order::STATE_PENDING_PAYMENT))
            && $row->getShippingMethod() == 'freeshipping_freeshipping'
            ? $row->getId()
            : false;
    }
}
