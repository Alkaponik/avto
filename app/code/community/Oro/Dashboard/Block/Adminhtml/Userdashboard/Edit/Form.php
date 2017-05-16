<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Block_Adminhtml_Userdashboard_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

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
        $fieldset->addField('is_default', 'checkbox', array(
            'label' => Mage::helper('oro_dashboard')->__('Is Default'),
            'required' => false,
            'name' => 'is_default',
            'checked' => (Mage::helper('oro_dashboard')->getDefaultDashboardId() == $this->getRequest()->getParam('id'))
        ));

        $fieldset->addField('dashboard_id', 'hidden',
            array(
                'name' => 'dashboard_id',
                'id' => 'dashboard_id',
            )
        );

        if (Mage::getSingleton('adminhtml/session')->getDashboardData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getDashboardData());
            Mage::getSingleton('adminhtml/session')->setDashboardData(null);
        } elseif (Mage::registry('dashboard_data')) {
            $form->setValues(Mage::registry('dashboard_data')->getData());
        }

        $form->setAction($this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))));
        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('edit_form');

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
