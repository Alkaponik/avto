<?php

class Testimonial_Intime_Block_Adminhtml_Consignments_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('updated_at');
        $this->setId('intime_consignment_grid');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);

        $this->setColumnRenderers(
            array(
                'action'    => 'magedoc_system/adminhtml_widget_grid_column_renderer_action',
            ));
    }

    protected function _prepareCollection()
    {
        /** @var $collection Testimonial_Intime_Model_Resource_Consignment_Collection */
        $collection = Mage::getModel('intime/consignment')
            ->getCollection();
        $collection->getSelect()
            ->joinLeft(
                array('order'   =>  $collection->getTable('sales/order')),
                'order.entity_id = main_table.order_id',
                array(
                    'order_status'  => 'order.status',
                    'supply_status' => 'order.supply_status'
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

    protected function _prepareColumns()
    {
        $this->addColumn('ttn', array(
            'header' => $this->__('Consignment number'),
            'width'  => '80px',
            'index'  => 'ttn'
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
                            'base'=>'adminhtml/sales_order/view',
                        ),
                        'field' =>  'order_id',
                        'value_index'   =>  'order_id'
                    )
                ),
            )
        );

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

        $this->addColumn('status', array(
            'header'       => $this->__('Status'),
            'index'        => 'status',
            'filter_index' => 'main_table.status',
            'type'         => 'options',
            'options'      => Mage::getSingleton('intime/source_tracking_status')->getStatus()
                )
        );

        $this->addColumn('sender_city', array(
            'header' => $this->__('Sender city'),
            'index'  => 'sender_city',
            'type'   => 'text'
                )
        );

        $this->addColumn('receiver_city', array(
            'header' => $this->__('Receiver city'),
            'index'  => 'receiver_city',
            'type'   => 'text'
                )
        );

        $this->addColumn('payer', array(
            'header' => $this->__('Payer'),
            'index'  => 'payer',
            'type'   => 'text'
                )
        );

        $this->addColumn('sum', array(
            'header'   => $this->__('Sum'),
            'index'    => 'sum',
            'type'     => 'currency',
            'currency' => 'base_currency_code'
                )
        );

        $this->addColumn('is_back_delivery', array(
            'header'  => $this->__('Is back delivery'),
            'index'   => 'is_back_delivery',
            'type'    => 'options',
            'options' => Mage::getSingleton('intime/source_tracking_backdelivery')->getBackDelivery()
                )
        );

        $this->addColumn('redelivery', array(
            'header' => $this->__('Redelivery'),
            'index'  => 'redelivery'
                )
        );

        $this->addColumn('arrival_date', array(
            'header' => $this->__('Arrival date'),
            'index'  => 'arrival_date',
            'width'  => '80px',
            'type'   => 'datetime'
                )
        );

        $this->addColumn('created_at', array(
            'header' => $this->__('Created at'),
            'index'  => 'created_at',
            'width'  => '80px',
            'type'   => 'datetime'
                )
        );

        $this->addColumn('updated_at', array(
            'header' => $this->__('Updated at'),
            'index'  => 'updated_at',
            'width'  => '80px',
            'type'   => 'datetime'
                )
        );

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return false;
    }

}
