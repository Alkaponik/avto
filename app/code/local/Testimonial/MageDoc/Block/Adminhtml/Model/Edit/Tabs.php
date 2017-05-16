<?php

class Testimonial_MageDoc_Block_Adminhtml_Model_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('model_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('magedoc')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('magedoc')->__('Item Information'),
          'title'     => Mage::helper('magedoc')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('magedoc/adminhtml_model_edit_tab_form')->toHtml(),
      ));
      $this->addTab('linked_products', array(
          'label'     => Mage::helper('magedoc')->__('Linked products'),
          'title'     => Mage::helper('magedoc')->__('Linked products'),
          'content'   => $this->getLayout()->createBlock('magedoc/adminhtml_model_edit_tab_product')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}