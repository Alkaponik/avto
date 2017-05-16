<?php

class Testimonial_MageDoc_Block_Adminhtml_Supply extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_supply';
        $this->_blockGroup = 'magedoc';
        $this->_headerText = Mage::helper('magedoc')->__('Supply Management');
        parent::__construct();

        $this->_addButton('reset', array(
            'label'     => Mage::helper('adminhtml')->__('Reset'),
            'onclick'   => 'setLocation(window.location.href)',
        ), -1);
        
        $this->_addButton('save', array(
            'label'     => Mage::helper('adminhtml')->__('Save'),
            'onclick'   => "$('edit_form').submit();",
            'class'     => 'save',
        ), 1);
        
    }


    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save/');
    }

    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }
}