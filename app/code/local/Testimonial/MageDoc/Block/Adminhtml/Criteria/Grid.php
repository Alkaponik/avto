<?php

class Testimonial_MageDoc_Block_Adminhtml_Criteria_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /** @var Testimonial_MageDoc_Model_Mysql4_Tecdoc_Criteria_Collection  */
    protected $_collection;

    public function __construct()
    {
        parent::__construct();
        $this->setId('criteria');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('magedoc/tecdoc_criteria_collection')
                ->joinDesignation(null, 'main_table', 'CRI_DES_ID')
                ->joinDesignation(null, 'main_table', 'CRI_SHORT_DES_ID', 'short_name')
                ->joinDesignation(null, 'main_table', 'CRI_UNIT_DES_ID', 'unit')
                ->joinCriteria();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $attributeOptions = array(
            0 => Mage::helper('magedoc')->__('-- Please select --'),
            Testimonial_MageDoc_Model_Criteria::ATTRIBUTE_CODE_NEW => Mage::helper('magedoc')->__('-- Create New --'));
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
        ->addVisibleFilter();
        foreach ($attributes as $attribute){
            $attributeOptions[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        $this->addColumn('td_cri_id',
            array(
                'header' => Mage::helper('magedoc')->__('Id'),
                'width' => '80px',
                'index' => 'td_cri_id',
            ));

        $this->addColumn('name',
            array(
                'header' => Mage::helper('magedoc')->__('Name'),
                'index' => 'name',
            ));

        $this->addColumn('short_name',
            array(
                'header' => Mage::helper('magedoc')->__('Short Name'),
                'index' => 'short_name',
            ));

        $this->addColumn('unit',
            array(
                'header' => Mage::helper('magedoc')->__('Unit'),
                'index' => 'unit',
            ));

        $this->addColumn('enabled',
            array(
                'header' => Mage::helper('magedoc')->__('Enabled'),
                'width' => '100px',
                'type' => 'options',
                'options' => Mage::getModel('eav/entity_attribute_source_boolean')
                    ->getOptionArray(),
                'index' => 'enabled',
            ));

        $this->addColumn('is_import_enabled',
            array(
                'header'    => Mage::helper('magedoc')->__('Is import enabled'),
                'index'     => 'is_import_enabled',
                'width'     => '120px',
                'type'      => 'checkbox',
                'values'    => '1',
                'value'     => '1',
                'renderer'  => 'magedoc/adminhtml_widget_grid_column_renderer_checkbox',
            )
        );

        $this->addColumn('attribute_code', array(
            'header'    => Mage::helper('magedoc')->__('Attribute'),
            'index'     => 'attribute_code',
            'type'      => 'options',
            'renderer'  => 'magedoc/adminhtml_widget_grid_column_renderer_options_select',
            'options'   => $attributeOptions,
            'internal_options' => $attributeOptions,
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('td_mfa_id');
        $this->getMassactionBlock()->setFormFieldName('magedoc');

        $this->getMassactionBlock()->addItem('enabled', array(
            'label'=> Mage::helper('magedoc')->__('Update Status'),
            'url'  => $this->getUrl('*/*/massEnabled', array('_current'=>true)),
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
        ))->addItem('is_import_enabled', array(
            'label'=> Mage::helper('magedoc')->__('Update Import Status'),
            'url'  => $this->getUrl('*/*/massIsImportEnabled', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'visible',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('magedoc')->__('Is Import Enabled'),
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
        return null;
        return $this->getUrl('*/*/edit', array('id' => $row->getTdCriId()));
    }
}