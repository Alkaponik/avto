<?php

class Testimonial_MageDoc_Block_Adminhtml_Supplier_Map extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_supplier_map';
        $this->_blockGroup = 'magedoc';
        $this->_headerText = Mage::helper('magedoc')->__('Manage Supplier Map');
        parent::__construct();

        $this->setTemplate('magedoc/widget/grid/container.phtml');
        $this->_removeButton('add');
        $this->_addButton('save', array(
            'label'     => Mage::helper('adminhtml')->__('Save'),
            'onclick'   => "$('edit_form').submit()",
            'class'     => 'save',
        ), -100);

        if(!$this->isSuggestAction()) {
            $this->_addButton('suggest', array(
                'label'     => Mage::helper('adminhtml')->__('Suggest'),
                'onclick'   => "window.location.href = '{$this->getUrl('*/*/suggest', array('_current'=>true))}';",
                'class'     => 'suggest',
            ), -100);
        } else {
            $this->_addButton('back',
                array(
                     'label'     => Mage::helper('adminhtml')->__('Back'),
                     'onclick'   => "window.location.href = '{$this->getUrl('*/*/index', array('_current'=>true))}';",
                     'class'     => 'back',
                ), -100);
    }

    }

    public function getSaveUrl(array $args = array())
    {
        $params = array('_current'=>true);
        $params = array_merge($params, $args);
        return $this->getUrl('*/*/save', $params);
    }

    protected function isSuggestAction()
    {
        return $this->getRequest()->getActionName() == Testimonial_MageDoc_Block_Adminhtml_Supplier_Map_Grid::SUGGEST_ACTION_NAME;
    }


}