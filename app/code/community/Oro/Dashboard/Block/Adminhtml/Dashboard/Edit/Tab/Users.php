<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Block_Adminhtml_Dashboard_Edit_Tab_Users extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $dashboardId = $this->getRequest()->getParam('id', false);

        $users = Mage::getModel("admin/user")->getCollection()->load();
        $this->setTemplate('oro_dashboard/dashboard_users.phtml')
            ->assign('users', $users->getItems())
            ->assign('dashboardId', $dashboardId);
    }

    protected function _prepareLayout()
    {
        $this->setChild('userGrid', $this->getLayout()->createBlock('oro_dashboard/adminhtml_dashboard_edit_grid_users', 'dashboardUsersGrid'));

        return parent::_prepareLayout();
    }

    protected function _getGridHtml()
    {
        return $this->getChildHtml('userGrid');
    }

    protected function _getJsObjectName()
    {
        return $this->getChild('userGrid')->getJsObjectName();
    }
}
