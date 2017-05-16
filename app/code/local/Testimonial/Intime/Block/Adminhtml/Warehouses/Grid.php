<?php

class Testimonial_Intime_Block_Adminhtml_Warehouses_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('id');
        $this->setId('warehousesGrid');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        /** @var $collection Testimonial_Intime_Model_Resource_Warehouse_Collection */
        $collection = Mage::getModel('intime/warehouse')
                ->getCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => $this->__('ID'),
            'align'  => 'right',
            'width'  => '50px',
            'index'  => 'id'
                )
        );

        $this->addColumn('name', array(
            'header'  => $this->__('City'),
            'index'   => 'city_code',
            'type'    => 'options',
            'options' => Mage::getModel('intime/city')->getOptionArray()
                )
        );

        $this->addColumn('phone', array(
            'header' => $this->__('Phone'),
            'index'  => 'phone'
                )
        );

        $this->addColumn('adress', array(
            'header' => $this->__('Adress'),
            'index'  => 'adress'
                )
        );

        $this->addColumn('code', array(
            'header' => $this->__('City code'),
            'index'  => 'city_code'
                )
        );

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return false;
    }

}
