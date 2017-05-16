<?php

class Testimonial_MageDoc_Block_Adminhtml_Catalog_Product_Edit_Tab_Cars extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('cars_product_grid');
        $this->setDefaultSort('typ_id');
        $this->setUseAjax(true);
    }

    public function getProduct()
    {
        return Mage::registry('product');
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_cars') {
            $carIds = $this->_getSelectedCars();
            if (empty($carIds)) {
                $carIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('typ_id', array('in'=>$carIds));
            }
            elseif(!empty($carIds)) {
                $this->getCollection()->addFieldToFilter('typ_id', array('nin'=>$carIds));
            }
        }
        else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('magedoc/tecdoc_model_collection')->setIdFieldName('TYP_ID')
            ->joinDesignations()
            ->joinManufacturers()
            ->joinModels()
            ->joinTypes();

        Mage::getResourceModel('magedoc/tecdoc_type_collection')->joinTypeDesignation('td_type', $collection);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('in_cars', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'in_cars',
            'values'    => $this->_getSelectedCars(),
            'align'     => 'center',
            'index'     => 'typ_id'
        ));
        $this->getColumn('in_cars')->setFieldName('product[typ_id][]');


        $this->addColumn('manufacturer', array(
            'header' => Mage::helper('magedoc')->__('Manufacturer'),
            'align' => 'left',
            'index' => 'mod_mfa_id',
            'width' => '200px',
            'type' => 'options',
            'options' => Mage::getModel('magedoc/source_manufacturer')->getOptionArray(),
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('magedoc')->__('Title'),
            'align' => 'left',
            'index' => 'name',
            'filter_index' => 'td_desText.TEX_TEXT',
            'width' => '400px',
        ));

        $this->addColumn('typ_cds_text', array(
            'header' => Mage::helper('magedoc')->__('Type'),
            'align' => 'left',
            'index' => 'typ_cds_text',
            'filter_index' => 'td_desText1.TEX_TEXT',
            'width' => '400px',
        ));


        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    protected function _getSelectedCars()
    {
        $types = Mage::getResourceModel('magedoc/catalog_layer_filter_type')
            ->getProductTypes($this->getProduct());
        return array_keys($types);
    }

}

