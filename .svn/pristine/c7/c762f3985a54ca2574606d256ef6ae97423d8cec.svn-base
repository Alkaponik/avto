<?php

class Testimonial_MageDoc_Block_Adminhtml_Price_Base extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_price_base';
        $this->_blockGroup = 'magedoc';
        $this->_headerText = Mage::helper('magedoc')->__('Manage Base Prices');

        parent::__construct();
        $this->setTemplate('magedoc/widget/grid/container.phtml');
        $this->_removeButton('add');
    }
  

}