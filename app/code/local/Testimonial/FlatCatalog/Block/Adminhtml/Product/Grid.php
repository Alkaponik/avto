<?php

class Testimonial_FlatCatalog_Block_Adminhtml_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('flatproductGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('flatproduct_filter');

    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        /** @var $collection Testimonial_FlatCatalog_Model_Resource_Product_Collection */
        $collection = Mage::getModel('flatcatalog/product')->getCollection();
        $baseCostExpr = new Zend_Db_Expr('main_table.cost * r.rate');
        $deliveryTermExpr = new Zend_Db_Expr('IFNULL(main_table.delivery_days, rcs.delivery_term_days)');
        $collection->getSelect()->joinInner(
            array('r' => $collection->getResource()->getTable('magedoc/retailer')),
            'r.retailer_id = main_table.retailer_id AND r.enabled = 1',
            array('base_cost' => $baseCostExpr))
            ->joinLeft(
                array('rcs' => $collection->getResource()->getTable('magedoc/retailer_config_supply')),
                'rcs.retailer_id = main_table.retailer_id',
                array('delivery_term' => $deliveryTermExpr)
            );
        $collection->addFilterToMap('base_cost', $baseCostExpr);
        $collection->addFilterToMap('delivery_term', $deliveryTermExpr);

        $this->setCollection($collection);

        parent::_prepareCollection();
        
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('data_id',
            array(
                'header'=> Mage::helper('catalog')->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'data_id',
        ));
        $this->addColumn('name',
            array(
                'header'=> Mage::helper('catalog')->__('Name'),
                'index' => 'name',
                'filter_index' => 'main_table.name'
        ));

        $this->addColumn('manufacturer',
            array(
                'header'=> Mage::helper('catalog')->__('Manufacturer'),
                'width' => '80px',
                'index' => 'manufacturer',
            ));

        $this->addColumn('type',
            array(
                'header'=> Mage::helper('catalog')->__('Type'),
                'width' => '60px',
                'index' => 'type_id',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));

        $this->addColumn('sku',
        array(
            'header'=> Mage::helper('catalog')->__('SKU'),
            'width' => '80px',
            'index' => 'sku',
        ));

        $this->addColumn('code_raw',
            array(
                'header'=> Mage::helper('catalog')->__('Raw Code'),
                'width' => '80px',
                'index' => 'code_raw',
            ));

        $this->addColumn('code_normalized',
            array(
                'header'=> Mage::helper('catalog')->__('Normalized Code'),
                'width' => '80px',
                'index' => 'code_normalized',
                'filter_condition_callback' => array($this, 'getNormalizedNumberFilterCallback'),
            ));

        $this->addColumn('cost',
            array(
                'header'=> Mage::helper('catalog')->__('Cost'),
                'type'  => 'currency',
                'index' => 'cost',
            ));

        $this->addColumn('price',
            array(
                'header'=> Mage::helper('catalog')->__('Price'),
                'type'  => 'currency',
                'index' => 'price',
            ));

        $store = $this->_getStore();

        $this->addColumn('base_cost',
            array(
                'header'=> Mage::helper('catalog')->__('Base Cost'),
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'base_cost',
            ));

        $this->addColumn('final_price',
            array(
                'header'=> Mage::helper('catalog')->__('Final Price'),
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'final_price',
        ));

        $this->addColumn('qty',
            array(
                'header'=> Mage::helper('catalog')->__('Qty'),
                'type'  => 'range',
                'index' => 'qty',
                'width' => '80px',
            ));

        $this->addColumn('retailer', array(
            'header'    => Mage::helper('magedoc')->__('Retailer'),
            'width'     => '80px',
            'type'      => 'options',
            'index'     => 'retailer_id',
            'filter_index' => 'main_table.retailer_id',
            'options'   => Mage::getSingleton('magedoc/source_retailer')->getOptionArray(),
        ));

        $this->addColumn('delivery_term',
            array(
                'header'=> Mage::helper('catalog')->__('Delivery Term'),
                'type'  => 'range',
                'index' => 'delivery_term',
                'width' => '80px',
            ));

        $this->addColumn('status',
            array(
                'header'=> Mage::helper('catalog')->__('Status'),
                'width' => '70px',
                'index' => 'status',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        $this->addColumn('updated_at',
            array(
                'header'=> Mage::helper('magedoc')->__('Updated at'),
                'index' => 'updated_at',
                'type'  => 'datetime',
            ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        return $this;
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('flatproduct');

        $statuses = Mage::getSingleton('catalog/product_status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('catalog')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('catalog')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));

        Mage::dispatchEvent('adminhtml_flatcatalog_product_grid_prepare_massaction', array('block' => $this));
        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return null;
    }

    public function getNormalizedNumberFilterCallback($collection, $column)
    {
        if($column->getFilter()->getValue()) {
            $codeNormalized = Mage::helper('magedoc')->normalizeCode( $column->getFilter()->getValue() );
            $this->getCollection()->getSelect()
                ->where("main_table.code_normalized LIKE '{$codeNormalized}%'");
        }
        return $this;
    }
}
