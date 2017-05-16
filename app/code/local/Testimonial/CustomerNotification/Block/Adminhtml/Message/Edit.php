<?php

class Testimonial_CustomerNotification_Block_Adminhtml_Message_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        //$this->_objectId = 'block_id';
        $this->_blockGroup = 'customernotification';
        $this->_controller = 'adminhtml_message';

        parent::__construct();

        //$this->_updateButton('save', 'label', Mage::helper('customernotification')->__('Send SMS'));
        $this->removeButton('delete');
        $this->removeButton('save');
        $this->_addButton('send_message', array(
            'label'     => Mage::helper('customernotification')->__('Send Message'),
            'onclick' => 'editForm.submit()',
        ));
    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('customernotification')->__('New Message');
    }

}
