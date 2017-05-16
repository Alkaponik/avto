<?php

class Testimonial_MageDoc_Block_Adminhtml_Retailer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_retailer';
        $this->_blockGroup = 'magedoc';
        $this->_headerText = Mage::helper('magedoc')->__('Manage retailers');
        parent::__construct();
        Mage::dispatchEvent('retailer_grid_container_event', array('block' => $this));
    }

    public function addButton($id, $data, $level = 0, $sortOrder = 0, $area = 'header')
    {
        return parent::_addButton($id, $data, $level, $sortOrder, $area);
    }
}