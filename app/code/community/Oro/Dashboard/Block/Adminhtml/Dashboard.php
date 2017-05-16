<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Block_Adminhtml_Dashboard extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_dashboard';
        $this->_blockGroup = 'oro_dashboard';
        parent::__construct();

        $this->_headerText = Mage::helper('oro_dashboard')->__('Manage Dashboards');
        $this->_updateButton('add', 'label', Mage::helper('oro_dashboard')->__('Add New Dashboard'));

        if (!Mage::helper('oro_dashboard')->isSectionAllowed('dashboards_create')) {
            $this->_removeButton('add');
        }
    }
}
