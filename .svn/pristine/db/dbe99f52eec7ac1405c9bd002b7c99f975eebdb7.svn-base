<?php

class Testimonial_MageDoc_Block_Adminhtml_Supplier_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('magedoc_suppliers');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        if (!isset($this->_collection)) {
            $this->_collection = Mage::getResourceModel('magedoc/tecdoc_supplier_collection')
                ->joinSuppliers();
        }

        $this->setCollection($this->_collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('sup_id', array(
            'header' => Mage::helper('magedoc')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'sup_id',
        ));

        $this->addColumn('enabled', array(
            'header' => Mage::helper('magedoc')->__('Enabled'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'enabled',
            'type' => 'options',
            'options' => array(
                0 => 'Disabled',
                1 => 'Enabled',
            ),
        ));

        $this->addColumn('title', array(
            'header' => Mage::helper('magedoc')->__('Title'),
            'align' => 'left',
            'index' => 'title',
            'width' => '400px',
        ));

        $this->addColumn('logo', array(
            'header' => Mage::helper('magedoc')->__('Logo'),
            'align' => 'left',
            'index' => 'logo',
            'width' => '150px',
        ));


        $this->addColumn('action',
            array(
                'header' => Mage::helper('magedoc')->__('Action'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getTdSupId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('magedoc')->__('Edit'),
                        'url' => array('base' => '*/*/edit'),
                        'field' => 'id'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('sup_id');
        $this->getMassactionBlock()->setFormFieldName('magedoc');

        $this->getMassactionBlock()->addItem('enable', array(
            'label' => Mage::helper('magedoc')->__('Update Status'),
            'url' => $this->getUrl('*/*/massEnabled', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'enabled',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('magedoc')->__('Status'),
                    'values' => array(
                        0 => 'Disabled',
                        1 => 'Enabled',
                    )
                )
            )
        ));
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getTdSupId()));
    }

}