<?php

class Testimonial_FlatCatalog_Block_Adminhtml_Product extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_blockGroup = 'flatcatalog';
        $this->_controller = 'adminhtml_product';
        $this->_headerText = Mage::helper('flatcatalog')->__('Products');
        parent::__construct();
        $this->_removeButton('add');
    }

}
