<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Block_Adminhtml_Dashboard_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'oro_dashboard';
        $this->_controller = 'adminhtml_dashboard';

        $this->_updateButton('save', 'label', Mage::helper('oro_dashboard')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('oro_dashboard')->__('Delete'));

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);

        $this->_formScripts[] = "

            function saveAndContinueEdit()
            {
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if (Mage::registry('dashboard_data') && Mage::registry('dashboard_data')->getId()) {
            return Mage::helper('oro_dashboard')->__("Edit Dashboard '%s'", $this->htmlEscape(Mage::registry('dashboard_data')->getName()));
        } else {
            return Mage::helper('oro_dashboard')->__('Add Dashboard');
        }
    }
}
