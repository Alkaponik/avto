<?php

    class Testimonial_CallBackRequest_Block_Adminhtml_Grid extends Mage_Adminhtml_Block_Widget_Grid_Container
    {
        public function __construct()
        {
            //where is the controller
            $this->_controller = 'adminhtml_request';
            $this->_blockGroup = 'callbackrequest';
            //text in the admin header
            $this->_headerText = Mage::helper('callbackrequest')->__('Call Back Request');
            //value of the add button
            $this->_addButtonLabel = 'Add a contact';
            $this->setTemplate('widget/grid/container.phtml');
            parent::__construct();
        }
    }