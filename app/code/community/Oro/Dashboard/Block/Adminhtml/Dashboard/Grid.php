<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Block_Adminhtml_Dashboard_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('dashboardGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
    }

    /**
     * Get current user
     *
     * @return Mage_Admin_Model_User || boolean
     */
    protected function _getCurrentUser()
    {
        if (Mage::helper('oro_dashboard')->canManageDashboards()) { //don't need user filter for administrator

            return false;
        }

        $currentUser = Mage::getSingleton('admin/session')->getUser();

        return $currentUser;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('oro_dashboard/dashboard')->getCollection()->joinRolesUsers($this->_getCurrentUser());
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header' => Mage::helper('oro_dashboard')->__('Name'),
            'align' => 'left',
            'index' => 'name',
        ));

        $this->addColumn('description', array(
            'header' => Mage::helper('oro_dashboard')->__('Description'),
            'align' => 'left',
            'index' => 'description',
        ));

        $this->addColumn('users', array(
            'header' => Mage::helper('oro_dashboard')->__('Users'),
            'align' => 'left',
            'index' => 'users',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('roles', array(
            'header' => Mage::helper('oro_dashboard')->__('Roles'),
            'align' => 'left',
            'index' => 'roles',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('is_default_for', array(
            'header' => Mage::helper('oro_dashboard')->__('Is Default For'),
            'align' => 'left',
            'index' => 'is_default_for',
            'filter' => false,
            'sortable' => false,
        ));

        /*$this->addColumn('created_at', array(
            'header'    => Mage::helper('oro_dashboard')->__('Created At'),
            'align'     =>'left',
            'index'     => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));*/

        $this->addColumn('action',
            array(
                'header' => Mage::helper('oro_dashboard')->__('Action'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('oro_dashboard')->__('Edit'),
                        'url' => array('base' => '*/*/edit'),
                        'field' => 'id'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('dashboard');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('oro_dashboard')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('oro_dashboard')->__('Are you sure?')
        ));

        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}
