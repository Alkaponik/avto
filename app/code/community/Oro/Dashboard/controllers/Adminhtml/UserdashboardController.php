<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Adminhtml_UserdashboardController extends Mage_Adminhtml_Controller_Action
{
    const WIDGET_WAS_REMOVED_MESSAGE = 'Sorry, widget was probably removed by another user, please refresh the page';

    protected function _construct()
    {
        $this->setUsedModuleName('Oro_Dashboard');
    }

    /**
     * Init action
     * @return Oro_Dashboard_Adminhtml_UserdashboardController
     */
    protected function _initAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('dashboard');

        return $this;
    }

    /**
     * Checks if user can edit dashboard
     * @param  Oro_Dashboard_Model_Dashboard $dashboard
     * @return boolean
     */
    protected function canEdit(Oro_Dashboard_Model_Dashboard $dashboard)
    {
        $currentUserId = Mage::getSingleton('admin/session')->getUser()->getUserId();

        return $dashboard->canEdit($currentUserId);
    }

    /**
     * Checks if user can create dashboards
     *
     * @return boolean
     */
    protected function canCreate()
    {
        return Mage::helper('oro_dashboard')->isSectionAllowed('dashboards_create');
    }

    /**
     * Get initial user dashboard
     *
     * @return Oro_Dashboard_Model_Dashboard
     */
    public function getFirstDashboard()
    {
        $collection = Mage::getModel('oro_dashboard/dashboard')->getCollection();
        if (!Mage::helper('oro_dashboard')->canManageDashboards()) {
            $user = Mage::getSingleton('admin/session')->getUser();
            $collection->addUserToFilter($user);
        }

        return $collection->getFirstItem();
    }

    /**
     * aggregation Start action
     */
    public function aggregationStartAction()
    {
	Mage::log('aggregationStartAction');
	Mage::log($this->getRequest()->getParams());
        $result = new Varien_Object();
        $result->setError(false);
        try {
            $data = Mage::getModel('oro_analytics/dataAggregation')->parseDataCronJob(
                array(
                     'goToDown' => false,
                     'dateStart' => $this->getRequest()->getParam('from'),
                     'dateEnd' => $this->getRequest()->getParam('to'),
                     'dateStartFirst' => $this->getRequest()->getParam('fromFirst'),
                     'dateEndFirst' => $this->getRequest()->getParam('toFirst'),
                     'countIteration' => $this->getRequest()->getParam('countIteration'),
                     'currentIteration' => $this->getRequest()->getParam('currentIteration'),
                ));
            $result->setData($data);
        } catch (Exception $e) {
            $result->setError(true);
            $result->setMessage($e->getMessage());
        }
	Mage::log($result->getData());

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result->getData()));
    }

    /**
     * Daily aggregation start action
     */
    public function dailyAggregationStartAction()
    {
        Mage::helper('oro_dashboard')->saveTimezoneConfig();
        Mage::getResourceModel('oro_analytics/dailyAggregation')->truncateTables();
        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Daily aggregation process action
     */
    public function dailyAggregationProcessAction()
    {
        $result = new Varien_Object();
        $result->setError(false);
        $records = Mage::getModel('oro_analytics/dailyAggregation')->parseDataCronJob(array('goToDown' => true));
        $result->setRecords($records);

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result->getData()));
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $defaultDashboard = Mage::getModel("oro_dashboard/dashboard")->load(Mage::helper('oro_dashboard')->getDefaultDashboardId());
        if (!$defaultDashboard->getId()) {
            $defaultDashboard = $this->getFirstDashboard();
        }
        if ($defaultDashboard->getId()) {
            $params = array("id" => $defaultDashboard->getId(), "store" => $defaultDashboard->getDefaultStoreId());
            /*Redirect to default dashboard*/
            $this->_redirect('*/*/view', $params);
        } else {
            $this->_redirect("*/*/new");
        }
    }

    /**
     * Date submit action
     */
    public function dateAction()
    {
        $helper = Mage::helper('oro_dashboard');
        if ($data = $this->getRequest()->getPost()) {
            if ($data['from'] && $data['to']) {
                try {
                    $from = $helper->processDate($data['from'] . " 00:00");
                    $to = $helper->processDate($data['to'] . " 23:00");
                    if (!$helper->isValidPeriod($from, $to)) {
                        Mage::throwException("Not valid date period");
                    }
                    Mage::getSingleton('adminhtml/session')->setDashboardDateFrom($from);
                    Mage::getSingleton('adminhtml/session')->setDashboardDateTo($to);
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($helper->__('Please select valid date period'));
                }
            }
        }
        $storeId = Mage::helper('oro_dashboard')->getStore();
        $dashboardId = $this->getRequest()->getParam('id');
        if (null === $storeId) {
            $modelDashboard = Mage::getModel('oro_dashboard/dashboard')->load($dashboardId);
            $storeId = $modelDashboard->getDefaultStoreId();
        }
        $this->_redirect('*/*/view', array("id" => $dashboardId, "store" => $storeId));
    }

    /**
     * View or print action processing
     */
    public function viewOrPrint()
    {
        $id = $this->getRequest()->getParam('id');
        $modelDashboard = Mage::getModel('oro_dashboard/dashboard')->load($id);

        if ($modelDashboard->getId()) {

            $currentUserId = Mage::getSingleton('admin/session')->getUser()->getUserId();

            if ($modelDashboard->canView($currentUserId)) {

                Mage::register('dashboard_data', $modelDashboard);

                $this->_initAction();

                $this->renderLayout();
            } else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('oro_dashboard')->__("You don't have permissions to view this dashboard"));
                $this->_redirect('*/*/');
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('oro_dashboard')->__('Dashboard does not exist'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * View action
     */
    public function viewAction()
    {
        Mage::helper('oro_dashboard')->checkTimezoneChange();
        $this->viewOrPrint();
    }

    /**
     * Edit action
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $modelDashboard = Mage::getModel('oro_dashboard/dashboard')->load($id);

        if ($modelDashboard->getId() || $id == 0) {

            if (($id == 0 && $this->canCreate()) || ($this->canEdit($modelDashboard))) {

                $data = Mage::getSingleton('adminhtml/session')->getFormData(true);

                if (!empty($data)) {
                    $modelDashboard->setData($data);
                }

                Mage::register('dashboard_data', $modelDashboard);

                $this->_initAction();

                $this->_addContent($this->getLayout()->createBlock('oro_dashboard/adminhtml_userdashboard_edit'));

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
        if ($this->canCreate()) {
            $this->_forward('edit');
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('oro_dashboard')->__("You don't have permissions to create new dashboard"));
            $this->_redirect('*/*/denied');
        }
    }

    /**
     * Denied action
     */
    public function deniedAction()
    {
        $this->_initAction();

        $this->renderLayout();
    }

    /**
     * Print action
     */
    public function printAction()
    {
        $this->viewOrPrint();
    }

    /**
     * Save action
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {

            $dashboardId = $this->getRequest()->getParam('id');
            /*Collecting Dashboard Data*/
            $modelDashboard = Mage::getModel('oro_dashboard/dashboard');
            $modelDashboard->setData($data)
                ->setId($dashboardId);
            if (!$dashboardId) {
                $modelDashboard->setCreatedAt(now());
                $modelDashboard->setCreatedBy(Mage::getSingleton('admin/session')->getUser()->getId());
            }

            try {
                if (($dashboardId == 0 && $this->canCreate()) || ($this->canEdit($modelDashboard))) {
                    $modelDashboard->save();

                    $defaultDashboardId = Mage::helper('oro_dashboard')->getDefaultDashboardId();
                    $userId = Mage::getSingleton('admin/session')->getUser()->getId();

                    if ($dashboardId == 0) {
                        /*Saving permissions for current user*/
                        $modelUser = Mage::getModel('oro_dashboard/user');
                        $modelUser->setDashboardId($modelDashboard->getId())
                            ->setUserId($userId)
                            ->setView(1)
                            ->setEdit(1)
                            ->save();
                    }

                    /* Set Default Dashboard */
                    if (isset($data['is_default'])) {
                        Mage::getResourceModel('oro_dashboard/user')->setDefault($modelDashboard->getId(), $userId);
                    } else {
                        /* Unset Default Dashboard if checkbox was unchecked*/
                        if ($defaultDashboardId == $modelDashboard->getId()) {
                            Mage::getResourceModel('oro_dashboard/user')->setNotDefault($modelDashboard->getId(), $userId);
                        }
                    }

                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('oro_dashboard')->__('Dashboard was successfully saved'));
                    Mage::getSingleton('adminhtml/session')->setFormData(false);
                }
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/view', array('id' => $modelDashboard->getId(), "store" => $modelDashboard->getDefaultStoreId()));

                    return;
                }

                $this->_redirect('*/*/view', array("id" => $modelDashboard->getId(), "store" => $modelDashboard->getDefaultStoreId()));

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
     * add widget action
     */
    public function savewidgetAction()
    {
        $result = new Varien_Object();
        $result->setError(false);
        if ($data = $this->getRequest()->getPost()) {
            $dashboardId = $this->getRequest()->getParam('id');
            if ($this->canEdit(Mage::getModel('oro_dashboard/dashboard')->load($dashboardId))) {
                $widgetId = $this->getRequest()->getParam('widget_id');
                if (!$widgetId) {
                    $widgetId = null;
                }
                /*Collecting Widget Data*/
                $modelWidget = Mage::getModel('oro_dashboard/widget');
                $modelWidget->setData($data)->setId($widgetId);
                if (!$widgetId) {
                    $modelWidget->setCreatedAt(now());
                }
                try {
                    $config = new Varien_Object();

                    $config->setType($data['type']);
                    $config->setMetric($data['metric']);
                    if (isset($data['metric_compare'])) {
                        $config->setMetricCompare($data['metric_compare']);
                    }
                    if (isset($data['lines_number'])) {
                        $config->setLinesNumber($data['lines_number']);
                    }
                    if (isset($data['grouped_by'])) {
                        $config->setGroupedByAttribute($data['grouped_by']);
                    }
                    if (isset($data['sectors_number'])) {
                        $config->setSectorsNumber($data['sectors_number']);
                    }
                    if (isset($data['filters'])) {
                        $filters = array();
                        foreach ($data['filters'] as $filter) {
                            parse_str($filter, $filter_array);
                            $filters[] = $filter_array;
                        }
                        $config->setFilters($filters);
                    }

                    $modelWidget->setWidgetConfig(Mage::helper('core')->jsonEncode($config->getData()));

                    $modelWidget->save();

                    if (!$widgetId) {
                        $modelWidget->assignToDashboard($dashboardId);
                    }

                    $html = $this->getLayout()->createBlock('oro_dashboard/adminhtml_userdashboard_widget_' . $modelWidget->getRawWidgetConfig()->getType())
                        ->setWidget($modelWidget)
                        ->toHtml();
                    $result->setHtml($html);

                    if (!$widgetId) {
                        $result->setWidgetId($modelWidget->getId());
                    }

                    Mage::getSingleton('adminhtml/session')->setFormData(false);
                } catch (Exception $e) {
                    $result->setError(true);
                    $result->setMessage($e->getMessage());
                }
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result->getData()));
    }

    /**
     * move widget action
     */
    public function movewidgetAction()
    {
        $result = new Varien_Object();
        $result->setError(false);
        try {
            if ($data = $this->getRequest()->getPost()) {
                $dashboardId = $this->getRequest()->getParam('id');
                if ($this->canEdit(Mage::getModel('oro_dashboard/dashboard')->load($dashboardId))) {
                    $positions = $data['positions'];
                    $widgetResource = Mage::getResourceModel("oro_dashboard/widget");
                    foreach ($positions as $columnId => $position) {
                        $columnId = $columnId + 1;
                        $widgetIds = explode("&", $position);
                        $widgetIds = array_reverse($widgetIds);
                        foreach ($widgetIds as $key => $widgetId) {
                            if ($widgetId) {
                                $widgetResource->changePosition($widgetId, $dashboardId, $columnId, 100 - $key);
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $result->setError(true);
            $result->setMessage($this->__(self::WIDGET_WAS_REMOVED_MESSAGE));
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result->getData()));
    }

    /**
     * load widget config action
     */
    public function loadwidgetconfigAction()
    {
        $result = new Varien_Object();
        $result->setError(false);
        try {
            $widgetId = $this->getRequest()->getParam('widget_id');
            $widget = Mage::getModel("oro_dashboard/widget")->load($widgetId);
            if (!$widget->getId()) Mage::throwException($this->__(self::WIDGET_WAS_REMOVED_MESSAGE));
            $tab = '';
            if ($widgetId) {
                $tab = $widget->getRawWidgetConfig()->getType();
            } else {
                $tab = $this->getRequest()->getParam('tab');
            }
            $html = $this->getLayout()->createBlock('oro_dashboard/adminhtml_userdashboard_tab_' . $tab)
                ->setWidget($widget)
                ->toHtml();
            $result->setTab($tab);
            $result->setHtml($html);
        } catch (Exception $e) {
            $result->setError(true);
            $result->setMessage($e->getMessage());
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result->getData()));
    }

    /**
     * Delete widget action
     */
    public function deletewidgetAction()
    {
        $result = new Varien_Object();
        $result->setError(false);
        if ($this->getRequest()->getParam('widget_id') > 0) {
            $dashboardId = $this->getRequest()->getParam('id');
            try {
                if ($this->canEdit(Mage::getModel('oro_dashboard/dashboard')->load($dashboardId))) {
                    $model = Mage::getModel('oro_dashboard/widget');
                    $model->setId($this->getRequest()->getParam('widget_id'));
                    $model->delete();
                }
            } catch (Exception $e) {
                $result->setError(true);
                $result->setMessage($e->getMessage());
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result->getData()));
    }

    /**
     * Delete action
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('oro_dashboard/dashboard');

                $model->setId($this->getRequest()->getParam('id'));

                if ($this->canEdit($model)) {
                    $model->delete();
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Dashboard was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }
}