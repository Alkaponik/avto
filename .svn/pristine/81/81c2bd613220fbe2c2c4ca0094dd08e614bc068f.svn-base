<?php

class Testimonial_MageDoc_Block_Adminhtml_Price_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('price_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('magedoc')->__('Price Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('magedoc')->__('Price Information'),
          'title'     => Mage::helper('magedoc')->__('Price Information'),
          'content'   => $this->getLayout()->createBlock('magedoc/adminhtml_price_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}