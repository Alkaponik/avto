<?php
class Testimonial_Intime_Block_Adminhtml_Consignments extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'intime';
        $this->_controller = 'adminhtml_consignments';
        $this->_headerText = $this->__('Manage consignments - %s', Mage::helper('intime')->getTitle());

        parent::__construct();

        $this->_removeButton('add');
        $this->_addButton('synchronize', array(
            'label'     => $this->__('Check status consignments'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/checkstatus') .'\')'
        ));
    }
}