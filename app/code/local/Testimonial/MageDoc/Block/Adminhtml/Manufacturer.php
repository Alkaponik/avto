<?php

class Testimonial_MageDoc_Block_Adminhtml_Manufacturer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_manufacturer';
        $this->_blockGroup = 'magedoc';
        $this->_headerText = Mage::helper('magedoc')->__('Manage Manufacturers');
        Mage_Adminhtml_Block_Widget_Container::__construct();

        $this->setTemplate('widget/grid/container.phtml');
    
    }
  

  
  
}