<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Block_Adminhtml_Dashboard_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $this->setForm($form);
        $fieldset = $form->addFieldset('dashboard_form', array('legend' => Mage::helper('oro_dashboard')->__('Dashboard Details')));

        $fieldset->addField('name', 'text', array(
            'label' => Mage::helper('oro_dashboard')->__('Name'),
            'required' => true,
            'name' => 'name',
        ));

        $fieldset->addField('description', 'textarea', array(
            'label' => Mage::helper('oro_dashboard')->__('Description'),
            'required' => false,
            'name' => 'description',
        ));

        /*$fieldset->addField('layout', 'select', array(
            'label' => Mage::helper('oro_dashboard')->__('Layout'),
            'required' => true,
            'name' => 'layout',
            'values' => Mage::helper('oro_dashboard')->getLayoutOptions()
        ));*/

        $storeValues = Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true);
        if (count(Mage::app()->getStores()) > 1) {
            $fieldset->addField('default_store_id', 'select', array(
                'label' => Mage::helper('oro_dashboard')->__('Default Store'),
                'required' => false,
                'name' => 'default_store_id',
                'values' => $storeValues
            ));
        }

        $fieldset->addField('dashboard_id', 'hidden',
            array(
                'name' => 'dashboard_id',
                'id' => 'dashboard_id',
            )
        );

        $fieldset->addField('in_dashboard_view_user', 'hidden',
            array(
                'name' => 'in_dashboard_view_user',
                'id' => 'in_dashboard_view_user',
            )
        );

        $fieldset->addField('in_dashboard_view_user_old', 'hidden',
            array('name' => 'in_dashboard_view_user_old')
        );

        $fieldset->addField('in_dashboard_edit_user', 'hidden',
            array(
                'name' => 'in_dashboard_edit_user',
                'id' => 'in_dashboard_edit_user',
            )
        );

        $fieldset->addField('in_dashboard_edit_user_old', 'hidden',
            array('name' => 'in_dashboard_edit_user_old')
        );

        $fieldset->addField('in_dashboard_view_role', 'hidden',
            array(
                'name' => 'in_dashboard_view_role',
                'id' => 'in_dashboard_view_role',
            )
        );

        $fieldset->addField('in_dashboard_view_role_old', 'hidden',
            array('name' => 'in_dashboard_view_role_old')
        );

        $fieldset->addField('in_dashboard_edit_role', 'hidden',
            array(
                'name' => 'in_dashboard_edit_role',
                'id' => 'in_dashboard_edit_role',
            )
        );

        $fieldset->addField('in_dashboard_edit_role_old', 'hidden',
            array('name' => 'in_dashboard_edit_role_old')
        );

        $fieldset->addField('in_dashboard_default_role', 'hidden',
            array(
                'name' => 'in_dashboard_default_role',
                'id' => 'in_dashboard_default_role',
            )
        );

        $fieldset->addField('in_dashboard_default_role_old', 'hidden',
            array('name' => 'in_dashboard_default_role_old')
        );

        if (Mage::getSingleton('adminhtml/session')->getDashboardData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getDashboardData());
            Mage::getSingleton('adminhtml/session')->setDashboardData(null);
        } elseif (Mage::registry('dashboard_data')) {
            $form->setValues(Mage::registry('dashboard_data')->getData());
        }

        return parent::_prepareForm();
    }
}
