<?php

class MageDoc_AdminMessenger_Block_Adminhtml_Message_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('MessageGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('asc');
        $this->setDefaultPage(999999);
        $this->setDefaultFilter(array('manager_id' => Mage::getSingleton('admin/session')->getUser()->getId()));
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);

        $this->setSubReportSize(null);
        /*$this->setColumnRenderers(
            array(
                'action'    => 'testimonial_system/adminhtml_widget_grid_column_renderer_action',
                'currency'  => 'testimonial_system/adminhtml_widget_grid_column_renderer_currency'
            ));*/
    }

    protected function _prepareCollection()
    {
        $this->__prepareCollection();
        return parent::_prepareCollection();
    }

    protected function __prepareCollection()
    {
        /* @var $collection Mage_Sales_Model_Resource_Order_Status_History_Collection */
        $collection = Mage::getResourceModel('sales/order_status_history_collection');
        $customerNameExpr = new Zend_Db_Expr("CONCAT(IFNULL(o.customer_firstname, ''), ' ', IFNULL(o.customer_lastname, ''))");

        $collection->getSelect()->join(
            array('o' => $collection->getTable('sales/order')),
            'o.entity_id = main_table.parent_id',
            array(
                'increment_id',
                'order_manager_id' => 'manager_id',
                'customer_name' => $customerNameExpr
            ));
        $collection->addFilterToMap('customer_name', $customerNameExpr);
        $collection->addFilterToMap('order_manager_id', 'o.manager_id');

        //print_r((string)$collection->getSelect());die;
        $this->setCollection($collection);
        //$this->setDefaultPage($collection->getLastPageNumber());
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id',
            array(
                'header' => Mage::helper('admin_messenger')->__('ID'),
                'align' => 'right',
                'width' => '50px',
                'index' => 'entity_id',
                'type' => 'range'
            )
        );

        $this->addColumn('created_at', array(
            'header' => Mage::helper('admin_messenger')->__('Created At'),
            'index' => 'created_at',
            'type' => 'datetime',
            'filter_index' => 'main_table.created_at'
        ));

        $this->addColumn('increment_id',
            array(
                'header' => Mage::helper('admin_messenger')->__('Order ID'),
                'align' => 'right',
                'width' => '50px',
                'index' => 'increment_id',
            )
        );

        if (Mage::helper('admin_messenger')->isModuleEnabled('Testimonial_MageDoc')) {
            $this->addColumn('order_manager_id', array(
                'header' => Mage::helper('admin_messenger')->__('Posted By'),
                'index' => 'order_manager_id',
                'filter_index' => 'o.manager_id',
                'type' => 'options',
                'options' => Mage::getSingleton('magedoc/source_orderManager')->getOptionArray()
            ));

            $this->addColumn('manager_id', array(
                'header' => Mage::helper('admin_messenger')->__('Manager'),
                'index' => 'manager_id',
                'filter_index' => 'main_table.manager_id',
                'type' => 'options',
                'options' => Mage::getSingleton('magedoc/source_orderManager')->getOptionArray()
            ));
        }

        $this->addColumn('customer_name', array(
            'header' => Mage::helper('admin_messenger')->__('Customer Name'),
            'index' => 'customer_name',
            'filter_index' => 'customer_name',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        if (Mage::helper('admin_messenger')->isModuleEnabled('Testimonial_MageDoc')) {
            $this->addColumnAfter('supply_status', array(
                'header' => Mage::helper('magedoc')->__('Supply Status'),
                'index' => 'supply_status',
                'type' => 'options',
                'width' => '70px',
                'options' => Mage::getSingleton('magedoc/source_order_supply_status')->getOptionArray(),
                'filter' => 'testimonial_system/adminhtml_widget_grid_column_filter_multiselect',
            ), 'status');
        }

        $this->addColumn('comment',
            array(
                'header' => Mage::helper('admin_messenger')->__('Comment'),
                'index' => 'comment',
                'column_css_class' => 'comment'
            )
        );


        Mage::dispatchEvent('admin_messenger_messages_grid_prepare_columns_after', array('block' => $this));

        return parent::_prepareColumns();
    }
}
