<?php

class Testimonial_MageDoc_Block_Adminhtml_Report_Shopcart_Abandoned_Grid
        extends Mage_Adminhtml_Block_Report_Shopcart_Abandoned_Grid
{
    public function __construct()
    {
        parent::__construct();
        $defaultFilter = array('is_active' => 0);
        if (!Mage::getSingleton('admin/session')->isAllowed('magedoc/orders/actions/show_all')){
            $defaultFilter['manager'] = Mage::getSingleton('admin/session')->getUser()->getId();
        }
        $this->setDefaultFilter($defaultFilter);

        $this->setColumnRenderers(
            array(
                'action'    => 'magedoc_system/adminhtml_widget_grid_column_renderer_action',
            ));
    }

    protected function _prepareCollection()
    {
        /** @var $collection Mage_Reports_Model_Resource_Quote_Collection */
        $collection = Mage::getResourceModel('reports/quote_collection');

        $filter = $this->getParam($this->getVarNameFilter(), array());
        if ($filter) {
            $filter = base64_decode($filter);
            parse_str(urldecode($filter), $data);
        }

        if (!empty($data)) {
            $collection->prepareForAbandonedReport($this->_storeIds, $data);
        } else {
            $collection->prepareForAbandonedReport($this->_storeIds);
        }

        $this->setCollection($collection);
        return Mage_Adminhtml_Block_Report_Grid_Shopcart::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumnAfter('telephone', array(
            'header' => Mage::helper('magedoc')->__('Telephone'),
            'index' => 'telephone',
            'type'  => 'text',
            'width' => '70px',
        ), 'email');

        $this->addColumnAfter('is_active', array(
            'header' => Mage::helper('magedoc')->__('Is Active'),
            'index' => 'is_active',
            'filter_index' => 'main_table.is_active',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getModel('eav/entity_attribute_source_boolean')
                ->getOptionArray(),
        ), 'updated_at');

        $this->addColumnAfter('manager', array(
            'header' => Mage::helper('magedoc')->__('Manager'),
            'index' => 'manager_id',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('magedoc/source_orderManager')->getOptionArray(),
        ), 'is_active');

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/create')) {
            $this->addColumnAfter('action',
                array(
                    'header'    => Mage::helper('sales')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('sales')->__('Edit'),
                            'url'     => array(
                                'base'=>'magedoc/sales_order_create/index',
                                'params' => array(
                                    'store_id'    => '{{@store_id}}',
                                    'customer_id' => '{{@customer_id}}'
                                )
                            ),
                            'field'   => 'quote_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'entity_id',
                    'is_system' => true,
                ), 'manager');
        }

        parent::_prepareColumns();

        $this->removeColumn('email');
        $this->removeColumn('remote_ip');
    }
}