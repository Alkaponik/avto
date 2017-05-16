<?php

class Testimonial_MageDoc_Block_Adminhtml_Catalog_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumnAfter('retailer', array(
            'header'    => Mage::helper('magedoc')->__('Retailer'),
            'width'     => '80px',
            'index'     => 'retailer_id',
            'type'      => 'options',
            'options'   => Mage::getSingleton('magedoc/source_retailer')->getOptionArray(),
            'name'      => 'retailer_id',
            'element_css_class' => 'input-text'
        ), 'sku');

        $this->sortColumnsByOrder();

        return $this;
    }
}
