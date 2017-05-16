<?php

class Testimonial_MageDoc_Block_Adminhtml_Model_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_saveParametersInSession = true;
    protected $_collection;

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('magedoc/tecdoc_model_collection')
            ->joinDesignations()->joinManufacturers()->joinModels();

        $collection->addFilterToMap('enabled', 'IFNULL(md_model.enabled, 0)');
        $collection->addFilterToMap('visible', 'IFNULL(md_model.visible, 0)');
        $collection->addFilterToMap('name', 'IFNULL(md_model.name, td_desText.TEX_TEXT)');
        $collection->addFilterToMap('title', 'IFNULL(md_model.title, CONCAT(MFA_BRAND, \' \', td_desText.TEX_TEXT))');
        $collection->addFilterToMap('manufacturer_enabled', 'IFNULL(md_manufacturer.enabled, 0)');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('mod_id', array(
            'header' => Mage::helper('magedoc')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'mod_id',
        ));


        $this->addColumn('manufacturer', array(
            'header' => Mage::helper('magedoc')->__('Manufacturer'),
            'align' => 'left',
            'index' => 'mod_mfa_id',
            'width' => '400px',
            'type' => 'options',
            'options' => Mage::getModel('magedoc/source_manufacturer')->getOptionArray(),
        ));

        $this->addColumn('manufacturer_enabled', array(
            'header' => Mage::helper('magedoc')->__('Is Manufacturer Enabled'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'manufacturer_enabled',
            'type' => 'options',
            'options' => array(
                0 => Mage::helper('magedoc')->__('No'),
                1 => Mage::helper('magedoc')->__('Yes'),
            ),
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('magedoc')->__('Name'),
            'align' => 'left',
            'index' => 'name',
            'width' => '400px',
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

        $this->addColumn('is_passenger_car', array(
            'header' => Mage::helper('magedoc')->__('Passenger Car'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'mod_pc',
            'type' => 'options',
            'options' => array(
                0 => Mage::helper('magedoc')->__('No'),
                1 => Mage::helper('magedoc')->__('Yes'),
            ),
        ));

        $this->addColumn('is_commercial_vehicle', array(
            'header' => Mage::helper('magedoc')->__('Commercial Vehicle'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'mod_cv',
            'type' => 'options',
            'options' => array(
                0 => Mage::helper('magedoc')->__('No'),
                1 => Mage::helper('magedoc')->__('Yes'),
            ),
        ));

        $this->addColumn('is_axle', array(
            'header' => Mage::helper('magedoc')->__('Axle'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'mod_axl',
            'type' => 'options',
            'options' => array(
                0 => Mage::helper('magedoc')->__('No'),
                1 => Mage::helper('magedoc')->__('Yes'),
            ),
        ));

        $this->addColumn('enabled', array(
            'header' => Mage::helper('magedoc')->__('Enabled'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'enabled',
            'type' => 'options',
            'options' => array(
                0 => Mage::helper('magedoc')->__('No'),
                1 => Mage::helper('magedoc')->__('Yes'),
            ),
        ));

        $this->addColumn('visible', array(
            'header' => Mage::helper('magedoc')->__('Visible'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'visible',
            'type' => 'options',
            'options' => array(
                0 => Mage::helper('magedoc')->__('No'),
                1 => Mage::helper('magedoc')->__('Yes'),
            ),
        ));

        $this->addColumn('identifier', array(
            'header' => Mage::helper('cms')->__('URL Key'),
            'align' => 'left',
            'index' => 'identifier'
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
        ))->addItem('visibility', array(
            'label' => Mage::helper('magedoc')->__('Update Visibility'),
            'url' => $this->getUrl('*/*/massVisible', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'visible',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('magedoc')->__('Visibility'),
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