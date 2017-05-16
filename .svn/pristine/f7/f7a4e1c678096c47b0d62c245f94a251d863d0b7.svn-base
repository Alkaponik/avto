<?php

class Testimonial_MageDoc_Block_Adminhtml_Price_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'magedoc';
        $this->_controller = 'adminhtml_price';
        
        $this->_updateButton('save', 'label', Mage::helper('magedoc')->__('Save Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('price_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'price_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'price_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('price_data')) {
            return Mage::helper('magedoc')->__("Edit Item id '%s'", $this->htmlEscape(Mage::registry('price_data')->getId()));
        }
        return Mage::helper('magedoc')->__("Create new item");
    }
}