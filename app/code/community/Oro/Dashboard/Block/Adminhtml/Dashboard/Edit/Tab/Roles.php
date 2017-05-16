<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Block_Adminhtml_Dashboard_Edit_Tab_Roles extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $dashboardId = $this->getRequest()->getParam('id', false);

        $roles = Mage::getModel("admin/role")->getCollection()->load();
        $this->setTemplate('oro_dashboard/dashboard_roles.phtml')
            ->assign('roles', $roles->getItems())
            ->assign('dashboardId', $dashboardId);
    }

    protected function _prepareLayout()
    {
        $this->setChild('roleGrid', $this->getLayout()->createBlock('oro_dashboard/adminhtml_dashboard_edit_grid_roles', 'dashboardRolesGrid'));

        return parent::_prepareLayout();
    }

    protected function _getGridHtml()
    {
        return $this->getChildHtml('roleGrid');
    }

    protected function _getJsObjectName()
    {
        return $this->getChild('roleGrid')->getJsObjectName();
    }
}
