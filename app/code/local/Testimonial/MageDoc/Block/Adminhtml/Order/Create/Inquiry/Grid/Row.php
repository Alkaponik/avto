<?php

class Testimonial_MageDoc_Block_Adminhtml_Order_Create_Inquiry_Grid_Row extends Mage_Adminhtml_Block_Widget
{
    protected $_comboboxes = array();
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('magedoc/order/create/inquiry/grid/row.phtml');
        $this->_prepareRow();
    }
    
    public function addCombobox($comboId, $options)
    {
        $combobox = Mage::app()->getLayout()->createBlock('magedoc/adminhtml_widget_combobox');
        $combobox->setData($options);
        $combobox->setId($comboId);
        $this->_comboboxes[$comboId]=$combobox;
        return $this;
    }
    
    public function getCombobox($comboId)
    {
        if(isset($this->_comboboxes[$comboId])){
            return $this->_comboboxes[$comboId];
        }
        return null;
    }
    
    
    protected function _prepareRow()
    {
        $this->addCombobox('category', array('name' => 'category'));
        $this->addCombobox('supplier', array('name' => 'supplier'));
        $this->addCombobox('number', array('name' => 'number'));
        $this->addCombobox('retailer', array('name' => 'retailer'));
        
        return $this;
    }
 
    
    public function getContainerId()
    {
        return '#{_container_id}';
    }
    
}
