<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Block_Adminhtml_Userdashboard extends Mage_Adminhtml_Block_Template
{
    protected $_objectId;

    protected $_isCustomRange = true;

    protected function _construct()
    {
        $this->_objectId = 'id';
    }

    protected function _prepareLayout()
    {
        $this->setChild('edit_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                'label' => Mage::helper('oro_dashboard')->__('Edit Details'),
                'onclick' => 'setLocation(\'' . $this->getEditUrl() . '\')',
                'class' => 'edit'
            )));

        $this->setChild('create_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                'label' => Mage::helper('oro_dashboard')->__('Add Dashboard'),
                'onclick' => 'setLocation(\'' . $this->getCreateUrl() . '\')',
                'class' => 'add'
            )));

        if ($this->getDashboards()->count() > 1) {
            $this->setChild('delete_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                    'label' => Mage::helper('oro_dashboard')->__('Delete'),
                    'onclick' => 'deleteDashboard()',
                    'class' => 'delete'
                )));
        }

        $this->setChild('widget_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                'label' => Mage::helper('oro_dashboard')->__('Add Widget'),
                'onclick' => "addWidget()",
                'class' => 'add'
            )));

        $this->setChild('print_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                'label' => Mage::helper('oro_dashboard')->__('Print Page'),
                'class' => 'customPrintThisPage'
            )));

        return parent::_prepareLayout();
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
     * Get create button html
     *
     * @return string
     */
    protected function getCreateButtonHtml()
    {
        if (Mage::helper('oro_dashboard')->isSectionAllowed('dashboards_create')) {
            return $this->getChildHtml('create_button');
        } else {
            return '';
        }
    }

    /**
     * Get delete button html
     *
     * @return string
     */
    protected function getDeleteButtonHtml()
    {
        if ($this->canEdit()) {
            return $this->getChildHtml('delete_button');
        } else {
            return '';
        }
    }

    /**
     * Get edit button html
     *
     * @return string
     */
    protected function getEditButtonHtml()
    {
        if ($this->canEdit()) {
            return $this->getChildHtml('edit_button');
        } else {
            return '';
        }
    }

    /**
     * Get print button html
     *
     * @return string
     */
    protected function getPrintButtonHtml()
    {
        return $this->getChildHtml('print_button');
    }

    /**
     * Get widget button html
     *
     * @return string
     */
    protected function getWidgetButtonHtml()
    {
        if ($this->canEdit()) {
            return $this->getChildHtml('widget_button');
        } else {
            return '';
        }
    }

    /**
     * Get dashboard new url
     *
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new');
    }

    /**
     * Get dashboard edit url
     *
     * @return string
     */
    public function getEditUrl()
    {
        return $this->getUrl('*/*/edit', array($this->_objectId => $this->getCurrentId()));
    }

    /**
     * Get dashboard delete url
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array($this->_objectId => $this->getCurrentId()));
    }

    /**
     * Get dashboard print url
     *
     * @return string
     */
    public function getPrintUrl()
    {
        return $this->getUrl('*/*/print', array($this->_objectId => $this->getCurrentId(),"store" => Mage::helper('oro_dashboard')->getStore()));
    }

    /**
     * Checks if user can edit current dashboard
     *
     * @return boolean
     */
    public function canEdit()
    {
        if ($currentDashboard = $this->getCurrentDashboard()) {
            $modelDashboard = Mage::getModel('oro_dashboard/dashboard')->load($currentDashboard->getId());
            $currentUserId = Mage::getSingleton('admin/session')->getUser()->getUserId();

            return $modelDashboard->canEdit($currentUserId);
        } else {
            return false;
        }
    }

    /**
     * Get add widget url
     *
     * @return string
     */
    public function getSaveWidgetUrl()
    {
        return $this->getUrl('*/*/savewidget', array($this->_objectId => $this->getCurrentId()));
    }

    /**
     * Get move widget url
     *
     * @return string
     */
    public function getMoveWidgetUrl()
    {
        return $this->getUrl('*/*/movewidget', array($this->_objectId => $this->getCurrentId()));
    }

    /**
     * Get delete widget url
     *
     * @return string
     */
    public function getDeleteWidgetUrl()
    {
        return $this->getUrl('*/*/deletewidget', array($this->_objectId => $this->getCurrentId()));
    }

    /**
     * Get move widget url
     *
     * @return string
     */
    public function getWidgetTabContentUrl()
    {
        return $this->getUrl('*/*/loadwidgetconfig', array($this->_objectId => $this->getCurrentId()));
    }

    /**
     * Get move widget url
     *
     * @return string
     */
    public function getAggregationStartUrl()
    {
        return $this->getUrl('*/*/aggregationStart');
    }

    /**
     * Get current user dashboard
     *
     * @return Varien_Object|null
     */
    public function getCurrentDashboard()
    {
        if (Mage::registry('dashboard_data')) {
            return Mage::registry('dashboard_data');
        }

        return '';
    }

    /**
     * Get dashboard view url
     *
     * @param  int    $dashboardId
     * @return string
     */
    public function getViewUrl($dashboardId)
    {
        $params = array('id' => $dashboardId, 'store' => Mage::getModel('oro_dashboard/dashboard')->load($dashboardId)->getDefaultStoreId());

        return $this->getUrl('*/*/view', $params);
    }

    /**
     * Get date submit url
     *
     * @param  int    $dashboardId
     * @return string
     */
    public function getDateSubmitUrl($dashboardId)
    {
        $params = array('id' => $dashboardId, 'store' => Mage::helper('oro_dashboard')->getStore());

        return $this->getUrl('*/*/date', $params);
    }

    /**
     * Get user dashboards collection
     *
     * @return Oro_Dashboard_Model_Resource_Dashboard_Collection
     */
    public function getDashboards()
    {
        $collection = Mage::getModel('oro_dashboard/dashboard')->getCollection();
        if (!Mage::helper('oro_dashboard')->canManageDashboards()) {
            $user = Mage::getSingleton('admin/session')->getUser();
            $collection->addUserToFilter($user);
        }

        return $collection;
    }

    /**
     * Get current widgets
     *
     * @param  int                           $columnId
     * @return Oro_Dashboard_Model_Dashboard
     */
    public function getWidgets($columnId)
    {
        $collection = Mage::getModel('oro_dashboard/widget')->getCollection();
        $collection->addDashboardToFilter($this->getCurrentId())->addFieldToFilter('position_column', $columnId);

        return $collection;
    }

    /**
     * Get tab content
     *
     * @param  string                     $tabName
     * @param  Oro_Dashboard_Model_Widget $widget
     * @return string
     */
    public function getTabContent($tabName, $widget = null)
    {
        $html = $this->getLayout()->createBlock('oro_dashboard/adminhtml_userdashboard_tab_' . $tabName)->toHtml();

        return $html;
    }

    /**
     * Get date ranges
     *
     * @return array
     */
    public function getDateRanges()
    {
        $ranges = array();
        $ranges[] = array('label' => $this->__('Today'), 'value' => $this->helper('oro_dashboard')->getDateRange('today'));
        $ranges[] = array('label' => $this->__('Yesterday'), 'value' => $this->helper('oro_dashboard')->getDateRange('yesterday'));
        $ranges[] = array('label' => $this->__('Last Week'), 'value' => $this->helper('oro_dashboard')->getDateRange('last week'));
        $ranges[] = array('label' => $this->__('Last Month'), 'value' => $this->helper('oro_dashboard')->getDateRange('last month'));

        return $ranges;
    }

    /**
     * Check if range is selected
     *
     * @param  string  $range
     * @return boolean
     */
    public function rangeIsSelected($range)
    {
        $isSelected = false;
        $currentRange = $this->helper('oro_dashboard')->getDateFrom() . '#' . $this->helper('oro_dashboard')->getDateTo();
        if ($currentRange == $range) {
            $this->_isCustomRange = false;
            $isSelected = true;
        }

        return $isSelected;
    }

    /**
     * Check if custom range is selected
     *
     * @return boolean
     */
    public function getIsCustomRange()
    {
        return $this->_isCustomRange;
    }
}
