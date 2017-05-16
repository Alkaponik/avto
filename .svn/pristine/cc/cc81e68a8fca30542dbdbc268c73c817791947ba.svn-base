<?php

class Testimonial_MageDoc_Block_Adminhtml_Retailer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_collection;
    const ACTUAL_PRICE_TERM = 168;

    public function __construct()
    {
        parent::__construct();
        $this->setId('magedoc_retailers');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('magedoc/retailer_collection')
            ->joinLastImportSession()
            ->joinSupplyConfig();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'retailer_id',
            array(
                'header' => Mage::helper('magedoc')->__('Id'),
                'width'  => '80px',
                'index'  => 'retailer_id',
                'filter_index' => 'main_table.retailer_id',
            )
        );

        $this->addColumn(
            'name',
            array(
                 'header' => Mage::helper('magedoc')->__('Name'),
                 'index'  => 'name',
            )
        );


        $this->addColumn(
            'model',
            array(
                 'header'  => Mage::helper('magedoc')->__('Model'),
                 'type'    => 'options',
                 'options' => Mage::getModel('magedoc/source_retailer_data_import_model')
                     ->getOptionArray(),
                 'index'   => 'model',
            )
        );

        $this->addColumn(
            'rate',
            array(
                 'width'  => '60px',
                 'header' => Mage::helper('magedoc')->__('Rate'),
                 'index'  => 'rate',
            )
        );

        $this->addColumn(
            'margin_ratio',
            array(
                 'width'  => '60px',
                 'header' => Mage::helper('magedoc')->__('Margin ratio'),
                 'index'  => 'margin_ratio',
            )
        );

        $this->addColumn(
            'enabled',
            array(
                 'header'  => Mage::helper('magedoc')->__('Enabled'),
                 'width'   => '100px',
                 'type'    => 'options',
                 'options' => Mage::getModel('eav/entity_attribute_source_boolean')
                     ->getOptionArray(),
                 'index'   => 'enabled',
            )
        );

        $this->addColumn(
            'is_import_enabled',
            array(
                 'header'  => Mage::helper('magedoc')->__('Import enabled'),
                 'width'   => '100px',
                 'type'    => 'options',
                 'options' => Mage::getModel('eav/entity_attribute_source_boolean')
                     ->getOptionArray(),
                 'index'   => 'is_import_enabled',
            )
        );

        $this->addColumn(
            'use_for_autopricing',
            array(
                 'header'  => Mage::helper('magedoc')->__('Use for autopricing'),
                 'width'   => '100px',
                 'type'    => 'options',
                 'options' => Mage::getModel('eav/entity_attribute_source_boolean')
                     ->getOptionArray(),
                 'index'   => 'use_for_autopricing',
            )
        );

        $this->addColumn(
            'show_on_frontend',
            array(
                'header'  => Mage::helper('magedoc')->__('Display on frontend'),
                'width'   => '100px',
                'type'    => 'options',
                'options' => Mage::getModel('eav/entity_attribute_source_boolean')
                    ->getOptionArray(),
                'index'   => 'show_on_frontend',
            )
        );

        $this->addColumn(
            'stock_status',
            array(
                 'header'  => Mage::helper('magedoc')->__('Stock status'),
                 'width'   => '100px',
                 'index'   => 'stock_status',
                 'type'    => 'options',
                 'options' => Mage::getModel('magedoc/source_stock_status')
                     ->getOptionArray(),
            )
        );

        $this->addColumn(
            'delivery_type',
            array(
                'header'  => Mage::helper('magedoc')->__('Delivery Type'),
                'width'   => '100px',
                'index'   => 'delivery_type',
                'type'    => 'options',
                'options' => Mage::getModel('magedoc/source_retailer_config_supply')
                    ->getOptionArray(),
            )
        );

        $this->addColumn(
            'delivery_term_days',
            array(
                'header'  => Mage::helper('magedoc')->__('Delivery Term, d'),
                'width'   => '100px',
                'index'   => 'delivery_term_days',
                'type'    => 'number'
            )
        );


        $this->addColumn(
            'max_order_time',
            array(
                'header'  => Mage::helper('magedoc')->__('Max Order Time'),
                'width'   => '100px',
                'index'   => 'max_order_time',
                'type'    => 'range'
            )
        );

        $statuses = Mage::getSingleton('magedoc/source_retailer_data_import_session_status')->getAllOptions();
        array_unshift($statuses, Mage::helper('magedoc')->__('Has no sessions'));
        $this->addColumn(
            'status_id',
            array(
                 'header'  => Mage::helper('magedoc')->__('Last Import Status'),
                 'index'   => 'status_id',
                 'type'    => 'options',
                 'options' => $statuses,
                 //'frame_callback' => array($this, 'decorateUpdateRequired'),
                 'column_css_class' => 'a-center'
            )
        );

        $this->addColumn(
            'last_import_date',
            array(
                 'header' => Mage::helper('magedoc')->__('Last Import Date'),
                 'width'  => '200px',
                 'index'  => 'last_import_date',
                 'column_css_class' => 'a-center',
                 'frame_callback' => array($this, 'decorateUpdateRequired'),
            )
        );

        return parent::_prepareColumns();
    }

    public function _beforeToHtml()
    {
        parent::_beforeToHtml();

    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getRetailerId()));
    }

    public function decorateUpdateRequired($value, $row, $column, $isExport)
    {
        if( !$row->isEverPriceImported() ) {
            return $this->__('Never');
        }

        if( !$row->getIsImportEnabled() ) {
            return $value;
        }


        if( $row->isPriceUpdateValid()) {
            $class = 'grid-severity-critical';
        } else {
            if(!$row->isPriceExpiresSoon()) {
                $class = 'grid-severity-notice';
            } else {
                $class = 'grid-severity-minor-grey';
            }
        }


        return '<span class="'.$class.' '. $row->isPriceExpiresSoon() .'"><span>'.$value.'</span></span>';
    }
}