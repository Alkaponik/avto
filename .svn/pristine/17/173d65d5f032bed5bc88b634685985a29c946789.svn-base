<?php

class Testimonial_MageDoc_Block_Adminhtml_Price extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_price';
        $this->_blockGroup = 'magedoc';
        $this->_headerText = Mage::helper('magedoc')->__('Manage Prices');

        $this->setTemplate('magedoc/widget/grid/container.phtml');

        $this->_addButton('add', array(
            'label'     => Mage::helper('magedoc')->__('Add Price'),
            'onclick'   => "setLocation('{$this->getUrl('*/*/new')}')",
            'class'     => 'add',
        ));

        parent::__construct();
    }
  

}