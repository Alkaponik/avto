<?php

class Testimonial_MageDoc_Block_Adminhtml_Model_Edit_Tab_Products extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('types_product_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    public function getModel()
    {
        return Mage::registry('model');
    }

    protected function _prepareCollection()
    {
        $model = $this->getModel();
        $collection = Mage::getResourceModel('magedoc/tecdoc_type_collection')
            ->addTypeDesignation()
            ->joinTypeEngine()
            ->addModelFilter($model->getId());

        $typeProductTable = Mage::getSingleton('core/resource')->getTableName('magedoc/type_product');

        $collection->getSelect()->joinInner(
                $typeProductTable,
                'typ_id = type_id',
                array('product_id')
        );

        foreach($collection as $type){
            $veng=number_format(round($type->getTypCcm()/1000,1),1);
            $engineLabel = $veng.' '.$type->getTypFuelDesText().' '.$type->getTypHpFrom().' '.Mage::helper('magedoc')->__('h.p.').' ('.$type->getEngCode().')';
            $type->setTypeDesc($engineLabel);
            $type->setModelTitle($model->getTitle());
            $product = Mage::getModel('catalog/product')->load($type->getProductId());
            $type->setProductName($product->getName());
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('typ_id', array(
            'header' => Mage::helper('magedoc')->__('Type ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'typ_id',
        ));

        $this->addColumn('model_title', array(
            'header' => Mage::helper('magedoc')->__('Title'),
            'align' => 'left',
            'index' => 'model_title',
            'width' => '400px',
        ));

        $this->addColumn('type_desc', array(
            'header' => Mage::helper('magedoc')->__('Type'),
            'align' => 'left',
            'index' => 'type_desc',
            'filter_index' => 'td_desText1.TEX_TEXT',
            'width' => '400px',
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('magedoc')->__('Product Name'),
            'align' => 'left',
            'index' => 'product_name',
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
        $cars = $this->getRequest()->getPost('selected_cars');
        if (is_null($cars)) {
            $types = Mage::getResourceModel('magedoc/catalog_layer_filter_type')
                ->getProductTypes($this->getProduct());
            return array_keys($types);
        }
        return $cars;
    }

}

