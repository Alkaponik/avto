<?php

class Testimonial_MageDoc_Block_Adminhtml_Criteria extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_criteria';
        $this->_blockGroup = 'magedoc';
        $this->_headerText = Mage::helper('magedoc')->__('Manage Article Criteria');
        parent::__construct();

        $this->setTemplate('magedoc/widget/grid/container.phtml');
        $this->_removeButton('add');
        $this->_addButton('save', array(
            'label'     => Mage::helper('adminhtml')->__('Save'),
            'onclick'   => "$('edit_form').submit()",
            'class'     => 'save',
        ), -100);
    }

    public function getSaveUrl(array $args = array())
    {
        $params = array('_current'=>true);
        $params = array_merge($params, $args);
        return $this->getUrl('*/*/save', $params);
    }
}