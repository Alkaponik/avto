<?php

class Testimonial_CustomerNotification_Block_Adminhtml_Message_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('message_id');
        $this->setDefaultDir('desc');
        $this->setId('messageGrid');
        $this->setSaveParametersInSession(true);

        $this->setColumnRenderers(
            array(
                'action'    => 'magedoc_system/adminhtml_widget_grid_column_renderer_action',
                'currency'  => 'magedoc_system/adminhtml_widget_grid_column_renderer_currency'
            ));
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('customernotification/message')
            ->getCollection();
        $salesOrderGridTable = $collection->getResource()->getTable('sales/order_grid');

        $collection->getSelect()->joinLeft(
            array('order' => $salesOrderGridTable),
            'main_table.order_id = order.entity_id',
            array(
                'order_status'  => 'order.status',
                'supply_status' => 'order.supply_status'
            )
        );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('message_id',
            array(
                'header'=>Mage::helper('customernotification')->__('ID'),
                'align' =>'right',
                'width' => '50px',
                'index' => 'message_id'
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

        $this->addColumn('manager_name', array(
                'header'       => Mage::helper('customernotification')->__('Manager Name'),
                'index'        => 'manager_name',
                'filter_index' => 'main_table.manager_id',
                'type'         => 'options',
                'options'   => Mage::getModel('customernotification/source_adminUser')->getOptionArray(false)
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

        $this->addColumn('customer_name',
            array(
                'header'=>Mage::helper('customernotification')->__('Customer'),
                'index' => 'customer_name'
            )
        );

        $this->addColumn('event',
            array(
                'header'=>Mage::helper('customernotification')->__('Event'),
                'index' => 'event'
            )
        );

        $this->addColumn('channel',
            array(
                'header'=>Mage::helper('customernotification')->__('Channel'),
                'width' => '100px',
                'type'      =>  'options',
                'index'     =>  'channel',
                'options'   => array(
                    Testimonial_CustomerNotification_Helper_Data::NOTIFICATION_CHANNEL_SMS => $this->__('SMS'),
                    Testimonial_CustomerNotification_Helper_Data::NOTIFICATION_CHANNEL_EMAIL => $this->__('Email'),
                )
            )
        );

        $this->addColumn('recipient',
            array(
                'header'=>Mage::helper('customernotification')->__('Recipient'),
                'width' => '100px',
                'index' => 'recipient',
            )
        );

        $this->addColumn('text',
            array(
                'header'=>Mage::helper('customernotification')->__('Message Text'),
                'index' => 'text',
            )
        );

        $this->addColumn('status', array(
            'header'    => Mage::helper('adminhtml')->__('Status'),
            'index'     => 'status',
            'filter_index' => 'main_table.status',
            'type'      => 'options',
            'options'   => array(
                Testimonial_CustomerNotification_Helper_Data::NOTIFICATION_STATUS_PENDING   => $this->__('Pending'),
                Testimonial_CustomerNotification_Helper_Data::NOTIFICATION_STATUS_SUCCESS   => $this->__('Success'),
                Testimonial_CustomerNotification_Helper_Data::NOTIFICATION_STATUS_FAILED    => $this->__('Failed'),
            )
        ));

        $this->addColumn('created_at', array(
            'header'    => $this->__('Created At'),
            'index'     => 'created_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('update_at', array(
            'header'    => $this->__('Updated At'),
            'index'     => 'updated_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('attempt_count',
            array(
                'header'=>Mage::helper('customernotification')->__('Attempt Count'),
                'index'     => 'attempt_count',
                'type' => 'range',
            )
        );

        $this->addColumn('success_count',
            array(
                'header'=>Mage::helper('customernotification')->__('Success Count'),
                'index'     => 'success_count',
                'type' => 'range',
            )
        );

        $this->addColumn('action',
            array(
                'header'=> Mage::helper('customernotification')->__('Action'),
                'index' => 'message_id',
                'type'  => 'action',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('magedoc')->__('Resend'),
                        'url'       => array('base'=> '*/*/send'),
                        'field'     => 'id',
                        'confirm'   => Mage::helper('customernotification')->__('Do you really want to resend this message?'),
                    )
                ),
            )
        );

        return parent::_prepareColumns();
    }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
