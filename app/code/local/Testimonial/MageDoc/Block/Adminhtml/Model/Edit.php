<?php

class Testimonial_MageDoc_Block_Adminhtml_Model_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'magedoc';
        $this->_controller = 'adminhtml_model';
        
        $this->_updateButton('save', 'label', Mage::helper('magedoc')->__('Save Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
                
        if( Mage::registry('model') && Mage::registry('model')->getId()) {
            return Mage::helper('magedoc')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('model')->getTitle()));
        }
    }
}