<?php

class Testimonial_MageDoc_Block_Adminhtml_Manufacturer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_saveParametersInSession = true;
    protected $_collection;

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('magedoc/tecdoc_manufacturer_collection')
            ->joinManufacturers();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('mfa_id', array(
            'header' => Mage::helper('magedoc')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'mfa_id',
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

        $this->addColumn('description', array(
            'header' => Mage::helper('magedoc')->__('Description'),
            'align' => 'left',
            'index' => 'description',
        ));

        $this->addColumn('logo', array(
            'header' => Mage::helper('magedoc')->__('Logo'),
            'align' => 'left',
            'index' => 'logo',
            'width' => '150px',
        ));

        $this->addColumn('is_passenger_car', array(
            'header'    => Mage::helper('magedoc')->__('Passenger Car'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'mfa_pc_mfc',
            'type'      => 'options',
            'options'   => array(
                0 => Mage::helper('magedoc')->__('No'),
                1 => Mage::helper('magedoc')->__('Yes'),
            ),
        ));

        $this->addColumn('is_commercial_vehicle', array(
            'header'    => Mage::helper('magedoc')->__('Commercial Vehicle'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'mfa_cv_mfc',
            'type'      => 'options',
            'options'   => array(
                0 => Mage::helper('magedoc')->__('No'),
                1 => Mage::helper('magedoc')->__('Yes'),
            ),
        ));

        $this->addColumn('is_axle', array(
            'header'    => Mage::helper('magedoc')->__('Axle'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'mfa_axl_mfc',
            'type'      => 'options',
            'options'   => array(
                0 => Mage::helper('magedoc')->__('No'),
                1 => Mage::helper('magedoc')->__('Yes'),
            ),
        ));

        $this->addColumn('is_engine', array(
            'header'    => Mage::helper('magedoc')->__('Engine'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'mfa_eng_mfc',
            'type'      => 'options',
            'options'   => array(
                0 => Mage::helper('magedoc')->__('No'),
                1 => Mage::helper('magedoc')->__('Yes'),
            ),
        ));

        $this->addColumn('is_engine', array(
            'header'    => Mage::helper('magedoc')->__('Engine'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'mfa_eng_mfc',
            'type'      => 'options',
            'options'   => array(
                0 => Mage::helper('magedoc')->__('No'),
                1 => Mage::helper('magedoc')->__('Yes'),
            ),
        ));

        $this->addColumn('is_diesel', array(
            'header'    => Mage::helper('magedoc')->__('Diesel'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'mfa_eng_typ',
            'type'      => 'options',
            'options'   => array(
                0 => Mage::helper('magedoc')->__('No'),
                1 => Mage::helper('magedoc')->__('Yes'),
            ),
        ));

        $this->addColumn('action',
            array(
                'header' => Mage::helper('magedoc')->__('Action'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getTdMfaId',
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
        $this->setMassactionIdField('td_mfa_id');
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
        return $this->getUrl('*/*/edit', array('id' => $row->getTdMfaId()));
    }

}