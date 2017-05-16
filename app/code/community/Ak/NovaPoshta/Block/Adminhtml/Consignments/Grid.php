<?php
class Ak_NovaPoshta_Block_Adminhtml_Consignments_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('updated_at');
        $this->setId('novaposhta_consignment_grid');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);

        $this->setColumnRenderers(
            array(
                'action'    => 'testimonial_system/adminhtml_widget_grid_column_renderer_action',
            ));
        $this->setCountTotals(true);
    }

    protected function _prepareCollection()
    {
        /** @var $collection Testimonial_NovaPoshta_Model_Resource_Consignment_Collection */
        $collection = Mage::getModel('novaposhta/consignment')
            ->getCollection();
        $collection->getSelect()
            ->joinLeft(
                array('order'   =>  $collection->getTable('sales/order')),
                'order.entity_id = main_table.order_id',
                array(
                    'order_status'  => 'order.status',
                    'supply_status' => 'order.supply_status',
                    'order_grand_total'=> 'order.grand_total',
                    'order_currency_code'=>'order_currency_code'
                )
            )
            ->joinLeft(
                array('order_payment'   =>  $collection->getTable('sales/order_payment')),
                'order_payment.parent_id = main_table.order_id',
                array('payment_method' => 'order_payment.method')
    );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        $totalObj = Mage::getModel('testimonial_system/grid_totals');
        $this->setTotals($totalObj->countTotals($this));
        return parent::_afterLoadCollection();
    }

    protected function _prepareColumns()
    {
        $paymentMethods = array();
        foreach (Mage::helper('payment')->getPaymentMethods() as $code => $data) {
            if (!empty($data['active'])){
                if ((isset($data['title']))) {
                    $paymentMethods[$code] = $data['title'];
                } else {
                    if (Mage::helper('payment')->getMethodInstance($code)) {
                        $paymentMethods[$code] = Mage::helper('payment')->getMethodInstance($code)->getConfigData('title', null);
                    }
                }
            }
        }

        $this->addColumn('ttn', array(
            'header' => $this->__('Consignment number'),
            'width'  => '80px',
            'index'  => 'ttn'
                )
        );

        $this->addColumn('receiver', array(
            'header' => $this->__('Receiver'),
            'width'  => '120px',
            'index'  => 'receiver'
                )
        );
        /*
        $this->addColumn('status', array(
                'header'  => $this->__('Status'),
                'index'   => 'status',
                'filter_index'   => 'main_table.status',
                'width'   => '120px',
                'type'    => 'options',
                'options' => Mage::getSingleton('novaposhta/source_tracking_status')->getStatus()
            )
        );

        $this->addColumn('stage', array(
            'header'  => $this->__('Stage'),
            'index'   => 'stage',
            'width'   => '120px',
            'type'    => 'options',
            'options' => Mage::getSingleton('novaposhta/source_tracking_stage')->getStage()
                )
        );
        */
        $this->addColumn('state', array(
            'header'  => $this->__('State'),
            'index'   => 'state',
            'filter_index' => 'main_table.state',
            'width'   => '120px',
            'type'    => 'options',
            'options' => Mage::getSingleton('novaposhta/source_tracking_state')->getAllOptions()
                )
        );

        $this->addColumn('order_increment_id', array(
                'header' => $this->__('Order Id'),
                'index'  => 'order_increment_id',
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
                ),
            )
        );

        $this->addColumn('order_grand_total', array(
            'header' => Mage::helper('sales')->__('Order Total'),
            'index' => 'order_grand_total',
            'filter_index' => 'order.grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));

        $this->addColumn('order_status', array(
            'header'       => $this->__('Order Status'),
            'index'        => 'order_status',
            'filter_index' => 'order.status',
            'type'         => 'options',
            'options'      => Mage::getSingleton('sales/order_config')->getStatuses()
                )
        );

        $this->addColumn('supply_status', array(
                'header'       => $this->__('Supply Status'),
                'index'        => 'supply_status',
                'filter_index' => 'order.supply_status',
                'type'         => 'options',
                'options'      => Mage::getSingleton('magedoc/source_order_supply_status')->getOptionArray()
            )
        );

        $this->addColumn('payment_method', array(
                'header' => Mage::helper('magedoc')->__('Payment Method'),
                'index' => 'payment_method',
                'filter_index'   => 'order_payment.method',
                'type'  => 'options',
                'width' => '70px',
                'options' => $paymentMethods,
            )
        );

        $this->addColumn('city_receiver_ru', array(
            'header' => $this->__('Receiver city'),
            'index'  => 'city_receiver_ru'
                )
        );

        $this->addColumn('ware_receiver_ru', array(
                'header' => $this->__('Destination address'),
                'index'  => 'ware_receiver_ru'
            )
        );

        $this->addColumn('payer', array(
                'header'  => $this->__('Payer'),
                'index'   => 'payer',
                'type'    => 'options',
                'options' => Mage::getSingleton('novaposhta/source_tracking_payer')->getPayer()
            )
        );

        /*$this->addColumn('full_description', array(
                'header' => $this->__('Description'),
                'index'  => 'full_description'
            )
        );*/

        $this->addColumn('created_at', array(
            'header' => $this->__('Created at'),
            'index'  => 'created_at',
            'filter_index'  => 'main_table.created_at',
            'type'      => 'datetime',
            )
        );

        $this->addColumn('updated_at', array(
                'header' => $this->__('Updated at'),
                'index'  => 'updated_at',
                'filter_index'  => 'main_table.updated_at',
                'type'   => 'datetime',
            )
        );

        $this->addColumn('date_estimated', array(
                'header' => $this->__('Estimated Date'),
                'index'  => 'date_estimated',
                'type'   => 'datetime',
            )
        );

        $this->addColumn('date_received', array(
                'header' => $this->__('Date received'),
                'index'  => 'date_received',
                'type'   => 'datetime',
            )
        );

        /*$this->addColumn('delivery_form', array(
            'header'  => $this->__('Delivery point'),
            'index'   => 'delivery_form',
            'type'    => 'options',
            'options' => Mage::getSingleton('novaposhta/source_tracking_delivery')->getDeliveryForm()
                )
        );*/

        $this->addColumn('back_delivery', array(
            'header'  => $this->__('Back delivery'),
            'index'   => 'back_delivery',
            'type'    => 'options',
            'options' => Mage::getSingleton('novaposhta/source_tracking_backdelivery')->getBackDelivery()
                )
        );

        $this->addColumn('is_back_delivery', array(
            'header'  => $this->__('Is back delivery'),
            'index'   => 'is_back_delivery',
            'type'    => 'options',
            'options' => Mage::getSingleton('novaposhta/source_tracking_backdelivery')->getBackDelivery()
                )
        );

        $this->addColumn('redelivery', array(
                'header' => $this->__('Redelivery'),
                'index'  => 'redelivery'
            )
        );

        $this->addColumn('sum', array(
                'header'   => $this->__('Sum'),
                'index'    => 'sum',
                'type'     => 'currency',
                'currency' => 'base_currency_code',
                'total'    => 'sum',
            )
        );

        $this->addColumn('redelivery_price', array(
                'header' => $this->__('Redelivery price'),
                'index'  => 'redelivery_price',
                'total'  => 'sum',
            )
        );

        $this->addColumn('redelivery_sum', array(
                'header'    => $this->__('Redelivery sum'),
                'index'     => 'redelivery_sum',
                'type'      => 'currency',
                'currency'  => 'base_currency_code',
                'total'     => 'sum',
                )
        );

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return false;
    }

}
