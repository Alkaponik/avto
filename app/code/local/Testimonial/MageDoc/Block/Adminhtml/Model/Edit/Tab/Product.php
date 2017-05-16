<?php

class Testimonial_MageDoc_Block_Adminhtml_Model_Edit_Tab_Product extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('magedoc_model_products');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    public function getModel()
    {
        return Mage::registry('model');
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in model flag
        if ($column->getId() == 'in_model') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
            }
            elseif(!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
            }
        }
        else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        if ($this->getModel()->getId()) {
            $this->setDefaultFilter(array('in_model'=>1));
        }
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('in_model', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'in_model',
            'values'    => $this->_getSelectedProducts(),
            'align'     => 'center',
            'index'     => 'entity_id'
        ));

        $this->getColumn('in_model')->setFieldName('product[id][]');

        $this->addColumn('product_entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => '60',
            'index'     => 'entity_id',
        ));
        $this->addColumn('product_name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'index'     => 'name',
        ));
        $this->addColumn('product_sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'width'     => '80',
            'index'     => 'sku',
        ));
        $this->addColumn('product_price', array(
            'header'    => Mage::helper('catalog')->__('Price'),
            'type'  => 'currency',
            'width'     => '1',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'     => 'price',
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    protected function _getSelectedProducts()
    {
        $products = $this->getModel()->getSelectedProducts();
        return array_unique($products);
    }

    protected function _setFilterValues($data)
    {
        foreach ($this->getColumns() as $columnId => $column) {
            preg_match('/(?<=product\[).*(?=\])/',$columnId, $matches);
            $index = !empty($matches[0])?$matches[0]:$columnId;
            if (isset($data['product'][$index])
                && (!empty($data['product'][$index]) || strlen($data[$index]) > 0)
                && $column->getFilter()
            ) {
                $column->getFilter()->setValue($data['product'][$index]);
                $this->_addColumnFilterToCollection($column);
            }
        }
        return $this;
    }

}

