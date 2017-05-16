<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Block_Adminhtml_Dashboard_Edit_Grid_Users extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Columns ids with checkboxes
     *
     * @var array
     */
    protected $checkboxColumns = array();

    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('username');
        $this->setDefaultDir('asc');
        $this->setId('dashboardUserGrid');
        $this->setDefaultFilter('');
        $this->setUseAjax(true);
        $this->setCheckboxColumns(array('view','edit'));
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('admin/roles')->getUsersCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('view', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'field_name' => 'view[]',
            'values' => $this->_getSelectedUsers('view'),
            'align' => 'center',
            'index' => 'user_id',
            'column_label' => $this->__('View'),
            'renderer' => 'oro_dashboard/adminhtml_dashboard_edit_grid_widget_renderer_checkbox'
        ));

        $this->addColumn('edit', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'field_name' => 'edit[]',
            'values' => $this->_getSelectedUsers('edit'),
            'align' => 'center',
            'index' => 'user_id',
            'column_label' => $this->__("Edit"),
            'renderer' => 'oro_dashboard/adminhtml_dashboard_edit_grid_widget_renderer_checkbox'
        ));

        $this->addColumn('username', array(
            'header' => Mage::helper('adminhtml')->__('User Name'),
            'index' => 'username'
        ));

        $this->addColumn('email', array(
            'header' => Mage::helper('adminhtml')->__('Email'),
            'index' => 'email'
        ));

        $this->addColumn('firstname', array(
            'header' => Mage::helper('adminhtml')->__('First Name'),
            'index' => 'firstname'
        ));

        $this->addColumn('lastname', array(
            'header' => Mage::helper('adminhtml')->__('Last Name'),
            'index' => 'lastname'
        ));

        return parent::_prepareColumns();
    }

    /**
     * Add column filter to collection
     *
     * @param  Varien_Object                                           $column
     * @return Oro_Dashboard_Block_Adminhtml_Dashboard_Edit_Grid_Users
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for checkboxes columns
        if (in_array($column->getId(), $this->getCheckboxColumns()) !== 'false') {

            $userIds = $this->_getSelectedUsers($column->getId());

            if (empty($userIds)) {
                $userIds = 0;
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('user_id', array('in' => $userIds));
            } elseif (!empty($userIds)) {
                $this->getCollection()->addFieldToFilter('user_id', array('nin' => $userIds));
            }

        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * Get row url
     * @param  Varien_Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return "#";
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        $dashboardId = $this->getRequest()->getParam('id');

        return $this->getUrl('*/*/userGrid', array('id' => $dashboardId));
    }

    /**
     * Get selected users
     *
     * @param  string  $type
     * @param  boolean $json
     * @return array
     */
    protected function _getSelectedUsers($type, $json = false)
    {
        if ($this->getRequest()->getParam('in_dashboard_' . $type . '_user') != "") {
            return $this->getRequest()->getParam('in_dashboard_' . $type . '_user');
        }
        $dashboardId = ($this->getRequest()->getParam('id') > 0) ? $this->getRequest()->getParam('id') : Mage::registry('dashboard_id');
        $users = Mage::getModel('oro_dashboard/user')->getUserIds($dashboardId, $type, 1);
        if (sizeof($users) > 0) {
            if ($json) {
                $jsonUsers = Array();
                foreach ($users as $usrid) $jsonUsers[$usrid] = 0;

                return Mage::helper('core')->jsonEncode((object) $jsonUsers);
            } else {
                return array_values($users);
            }
        } else {
            if ($json) {
                return '{}';
            } else {
                return array();
            }
        }
    }
}
