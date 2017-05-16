<?php
class Testimonial_Avtoto_Block_Adminhtml_Autopricing_Process_Status extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_autopricing_process_status';
        $this->_blockGroup = 'avtoto';
        $this->_headerText = Mage::helper('magedoc')->__('Avtoto price status');
        parent::__construct();
        $this->_removeButton('add');
    }
}