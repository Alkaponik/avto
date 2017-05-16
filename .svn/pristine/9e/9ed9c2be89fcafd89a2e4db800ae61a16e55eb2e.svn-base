<?php

class Testimonial_MageDoc_Block_Adminhtml_Supplier_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'magedoc';
        $this->_controller = 'adminhtml_supplier';
        
        $this->_updateButton('save', 'label', Mage::helper('magedoc')->__('Save Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('supplier_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'supplier_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'supplier_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
                
        if( Mage::registry('supplier_data') && Mage::registry('supplier_data')->getTdSupId()) {
            return Mage::helper('magedoc')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('supplier_data')->getTitle()));
        }
    }
}