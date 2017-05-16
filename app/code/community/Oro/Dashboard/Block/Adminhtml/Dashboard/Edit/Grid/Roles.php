<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Block_Adminhtml_Dashboard_Edit_Grid_Roles extends Mage_Adminhtml_Block_Widget_Grid
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
        $this->setDefaultSort('role_name');
        $this->setDefaultDir('asc');
        $this->setId('dashboardRoleGrid');
        $this->setDefaultFilter('');
        $this->setUseAjax(true);
        $this->setCheckboxColumns(array('view','edit','is_default'));
    }

    protected function _prepareCollection()
    {
        $dashboardId = $this->getRequest()->getParam('id');
        Mage::register('dashboard_id', $dashboardId);
        $collection = Mage::getModel('admin/role')->getCollection()->setRolesFilter();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('view', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'field_name' => 'view[]',
            'values' => $this->_getSelectedRoles('view'),
            'align' => 'center',
            'index' => 'role_id',
            'column_label' => $this->__('View'),
            'renderer' => 'oro_dashboard/adminhtml_dashboard_edit_grid_widget_renderer_checkbox'
        ));

        $this->addColumn('edit', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'field_name' => 'edit[]',
            'values' => $this->_getSelectedRoles('edit'),
            'align' => 'center',
            'index' => 'role_id',
            'column_label' => $this->__('Edit'),
            'renderer' => 'oro_dashboard/adminhtml_dashboard_edit_grid_widget_renderer_checkbox'
        ));

        $this->addColumn('is_default', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'field_name' => 'is_default[]',
            'values' => $this->_getSelectedRoles('is_default'),
            'align' => 'center',
            'index' => 'role_id',
            'column_label' => $this->__('Is Default'),
            'renderer' => 'oro_dashboard/adminhtml_dashboard_edit_grid_widget_renderer_checkbox'
        ));

        $this->addColumn('role_name', array(
            'header' => Mage::helper('adminhtml')->__('Role Name'),
            'index' => 'role_name'
        ));

        return parent::_prepareColumns();
    }

    /**
     * Add column filter to collection
     *
     * @param  Varien_Object                                           $column
     * @return Oro_Dashboard_Block_Adminhtml_Dashboard_Edit_Grid_Roles
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for checkboxes columns
        if (in_array($column->getId(), $this->getCheckboxColumns()) !== 'false') {

            $roleIds = $this->_getSelectedRoles($column->getId());

            if (empty($roleIds)) {
                $roleIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('role_id', array('in' => $roleIds));
            } elseif (!empty($roleIds)) {
                $this->getCollection()->addFieldToFilter('role_id', array('nin' => $roleIds));
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

        return $this->getUrl('*/*/roleGrid', array('id' => $dashboardId));
    }

    /**
     * Get selected roles
     *
     * @param  string  $type
     * @param  boolean $json
     * @return array
     */
    protected function _getSelectedRoles($type, $json = false)
    {
        if ($this->getRequest()->getParam('in_dashboard_' . $type . '_role') != "") {
            return $this->getRequest()->getParam('in_dashboard_' . $type . '_role');
        }
        $dashboardId = ($this->getRequest()->getParam('id') > 0) ? $this->getRequest()->getParam('id') : Mage::registry('dashboard_id');
        $roles = Mage::getModel('oro_dashboard/role')->getRoleIds($dashboardId, $type, 1);
        if (sizeof($roles) > 0) {
            if ($json) {
                $jsonRoles = Array();
                foreach ($roles as $usrid) $jsonRoles[$usrid] = 0;

                return Mage::helper('core')->jsonEncode((object) $jsonRoles);
            } else {
                return array_values($roles);
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
