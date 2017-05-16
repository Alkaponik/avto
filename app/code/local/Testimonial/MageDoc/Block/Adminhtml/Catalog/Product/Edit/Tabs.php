<?php

class Testimonial_MageDoc_Block_Adminhtml_Catalog_Product_Edit_Tabs extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs
{
    protected function _prepareLayout()
    {
        $this->addTabAfter('cars', array(
            'label'     => Mage::helper('magedoc')->__('Used in cars'),
            'class'     => 'ajax',
            'url'       => $this->getUrl('magedoc/adminhtml_catalog_product/cars', array('_current' => true)),
        ), 'categories');

        return parent::_prepareLayout();
    }
}
