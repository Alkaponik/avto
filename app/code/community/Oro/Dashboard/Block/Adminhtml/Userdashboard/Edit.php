<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Block_Adminhtml_Userdashboard_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'oro_dashboard';
        $this->_controller = 'adminhtml_userdashboard';

        $this->_updateButton('save', 'label', Mage::helper('oro_dashboard')->__('Save'));

        $this->_updateButton('back', 'label', Mage::helper('oro_dashboard')->__('Cancel'));

        $this->_removeButton('reset');
        $this->_removeButton('delete');

        $this->_formScripts[] = "";
    }

    /**
     * Get current dashboard id
     *
     * @return int
     */
    public function getCurrentId()
    {
        return $this->getRequest()->getParam($this->_objectId);
    }

    /**
     * Get page header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('dashboard_data') && Mage::registry('dashboard_data')->getId()) {
            return Mage::helper('oro_dashboard')->__("Edit Dashboard '%s'", $this->htmlEscape(Mage::registry('dashboard_data')->getName()));
        } else {
            return Mage::helper('oro_dashboard')->__('Add Dashboard');
        }
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getCurrentId()) {
            return $this->getUrl('*/*/view', array($this->_objectId => $this->getCurrentId(), 'store' => $this->getDashboard()->getDefaultStoreId()));
        }

        return $this->getUrl('*/*/index');
    }

    /**
     * Get current dashboard
     *
     * @return Oro_Dashboard_Model_Dashboard
     */
    public function getDashboard()
    {
        $id = $this->getCurrentId();
        $dashboard = Mage::getModel('oro_dashboard/dashboard')->load($id);

        return $dashboard;
    }
}
