<?php

class Testimonial_MageDoc_Block_Adminhtml_Manufacturer_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('manufacturer_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('magedoc')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('magedoc')->__('Item Information'),
          'title'     => Mage::helper('magedoc')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('magedoc/adminhtml_manufacturer_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}