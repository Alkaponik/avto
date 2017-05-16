<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Adminhtml_DashboardsController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init action
     * @return Oro_Dashboard_Adminhtml_DashboardsController
     */
    protected function _initAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('system/oro_dashboards');

        return $this;
    }

    /**
     * Check the permission to access dashboards manage page
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('system/dashboards/dashboards_manage');
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_addBreadcrumb(Mage::helper('oro_dashboard')->__('System'), Mage::helper('oro_dashboard')->__('System'));
        $this->_addBreadcrumb(Mage::helper('oro_dashboard')->__('Dashboards'), Mage::helper('oro_dashboard')->__('Dashboards'));
        $this->renderLayout();
    }

    /**
     * Edit action
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $modelDashboard = Mage::getModel('oro_dashboard/dashboard')->load($id);

        if ($modelDashboard->getId() || $id == 0) {

            $currentUserId = Mage::getSingleton('admin/session')->getUser()->getUserId();

            if (($id == 0) || ($modelDashboard->canEdit($currentUserId))) {

                $data = Mage::getSingleton('adminhtml/session')->getFormData(true);

                if (!empty($data)) {
                    $modelDashboard->setData($data);
                }

                Mage::register('dashboard_data', $modelDashboard);

                $this->_initAction();

                $this->_addBreadcrumb(Mage::helper('oro_dashboard')->__('Dashboard Details'), Mage::helper('oro_dashboard')->__('Dashboard Details'));

                $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

                $this->_addContent($this->getLayout()->createBlock('oro_dashboard/adminhtml_dashboard_edit'))
                    ->_addLeft($this->getLayout()->createBlock('oro_dashboard/adminhtml_dashboard_edit_tabs'));

                $this->_addJs(
                    $this->getLayout()->createBlock('adminhtml/template')->setTemplate('oro_dashboard/dashboard_users_js.phtml')
                );

                $this->_addJs(
                    $this->getLayout()->createBlock('adminhtml/template')->setTemplate('oro_dashboard/dashboard_roles_js.phtml')
                );

                $this->renderLayout();
            } else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('oro_dashboard')->__("You don't have permissions to edit this dashboard"));
                $this->_redirect('*/*/');
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('oro_dashboard')->__('Dashboard does not exist'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * New action
     */
    public function newAction()
    {
        if (Mage::helper('oro_dashboard')->isSectionAllowed('dashboards_create')) {
            $this->_forward('edit');
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('oro_dashboard')->__("You don't have permissions to create new dashboard"));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Save action
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {

            $dashboardId = $this->getRequest()->getParam('id');

            /*Collecting Users Permissions Data*/
            $dashboardViewUsers = $this->getRequest()->getParam('in_dashboard_view_user', null);
            parse_str($dashboardViewUsers, $dashboardViewUsers);
            $dashboardViewUsers = array_keys($dashboardViewUsers);

            $dashboardEditUsers = $this->getRequest()->getParam('in_dashboard_edit_user', null);
            parse_str($dashboardEditUsers, $dashboardEditUsers);
            $dashboardEditUsers = array_keys($dashboardEditUsers);

            $dashboardUsers = array();

            foreach ($dashboardViewUsers as $viewUser) {
                $dashboardUsers[$viewUser]['view'] = 1;
            }

            foreach ($dashboardEditUsers as $editUser) {
                $dashboardUsers[$editUser]['edit'] = 1;
            }

            /*Collecting Roles Permissions Data*/
            $dashboardViewRoles = $this->getRequest()->getParam('in_dashboard_view_role', null);
            parse_str($dashboardViewRoles, $dashboardViewRoles);
            $dashboardViewRoles = array_keys($dashboardViewRoles);

            $dashboardEditRoles = $this->getRequest()->getParam('in_dashboard_edit_role', null);
            parse_str($dashboardEditRoles, $dashboardEditRoles);
            $dashboardEditRoles = array_keys($dashboardEditRoles);

            $dashboardDefaultRoles = $this->getRequest()->getParam('in_dashboard_default_role', null);
            parse_str($dashboardDefaultRoles, $dashboardDefaultRoles);
            $dashboardDefaultRoles = array_keys($dashboardDefaultRoles);

            $dashboardRoles = array();

            foreach ($dashboardViewRoles as $viewRole) {
                $dashboardRoles[$viewRole]['view'] = 1;
            }

            foreach ($dashboardEditRoles as $editRole) {
                $dashboardRoles[$editRole]['edit'] = 1;
            }

            foreach ($dashboardDefaultRoles as $defaultRole) {
                $dashboardRoles[$defaultRole]['is_default'] = 1;
            }

            /*Collecting Dashboard Data*/
            $modelDashboard = Mage::getModel('oro_dashboard/dashboard');
            $modelDashboard->setData($data)
                ->setId($dashboardId);
            if (!$dashboardId) {
                $modelDashboard->setCreatedAt(now());
                $modelDashboard->setCreatedBy(Mage::getSingleton('admin/session')->getUser()->getId());
            }

            try {

                $modelDashboard->save();

                $dashboardId = $modelDashboard->getId();

                /*Saving Roles Permissions Data*/
                Mage::getResourceModel('oro_dashboard/role')->remove($dashboardId);
                foreach ($dashboardRoles as $roleId => $actions) {
                    $modelRole = Mage::getModel('oro_dashboard/role');
                    $modelRole->setDashboardId($dashboardId)
                        ->setUserRoleId($roleId)
                        ->setView(isset($actions['view']) ? $actions['view'] : 0)
                        ->setEdit(isset($actions['edit']) ? $actions['edit'] : 0)
                        ->setIsDefault(isset($actions['is_default']) ? $actions['is_default'] : 0)
                        ->save();
                }

                /*Saving Users Permissions Data*/
                Mage::getResourceModel('oro_dashboard/user')->remove($dashboardId);
                $modelUser = Mage::getModel('oro_dashboard/user');
                foreach ($dashboardUsers as $userId => $actions) {
                    $modelUser->setDashboardId($dashboardId)
                        ->setUserId($userId)
                        ->setView(isset($actions['view']) ? $actions['view'] : 0)
                        ->setEdit(isset($actions['edit']) ? $actions['edit'] : 0)
                        ->save();
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('oro_dashboard')->__('Dashboard was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $modelDashboard->getId()));

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));

                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('oro_dashboard')->__('Unable to find dashboard to save'));
        $this->_redirect('*/*/');
    }

    /**
     * Delete action
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('oro_dashboard/dashboard');

                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Dashboard was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Massive Delete action
     */
    public function massDeleteAction()
    {
        $dashboardIds = $this->getRequest()->getParam('dashboard');
        if (!is_array($dashboardIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($dashboardIds as $dashboardId) {
                    $dashboard = Mage::getModel('oro_dashboard/dashboard')->load($dashboardId);
                    $dashboard->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($dashboardIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Users grid action
     */
    public function userGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('oro_dashboard/adminhtml_dashboard_edit_tab_users', 'dashboard.users.grid')
                ->toHtml()
        );
    }

    /**
     * Roles grid action
     */
    public function roleGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('oro_dashboard/adminhtml_dashboard_edit_tab_roles', 'dashboard.roles.grid')
                ->toHtml()
        );
    }
}
