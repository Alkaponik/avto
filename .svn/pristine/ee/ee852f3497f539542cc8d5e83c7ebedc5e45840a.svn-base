<?php

class Testimonial_MageDoc_Block_Adminhtml_Permission_Tab_Supply extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $role = Mage::registry('current_role');

        $fieldset = $form->addFieldset('supply_fieldset', array('legend'=>Mage::helper('adminhtml')->__('Supply Permissions')));

        $orderStatuses = array();

        foreach (Mage::getSingleton('sales/order_config')->getStatuses() as $status => $label){
            $orderStatuses []= array (
                'value' => $status,
                'label' => $label
            );
        }

        $fieldset->addField('visible_order_statuses', 'multiselect',
            array(
                'name'  	=> 'visible_order_statuses',
                'label' 	=> Mage::helper('adminhtml')->__('Visible Order Statuses'),
                'id'    	=> 'visible_order_statuses',
                'title' 	=> Mage::helper('adminhtml')->__('Visible Order Statuses'),
                'class' 	=> 'input-select',
                'required' 	=> false,
                'values'	=> $orderStatuses
            )
        );

        $fieldset->addField('visible_order_supply_statuses', 'multiselect',
            array(
                'name'  	=> 'visible_order_supply_statuses',
                'label' 	=> Mage::helper('adminhtml')->__('Visible Order Supply Statuses'),
                'id'    	=> 'visible_order_supply_statuses',
                'title' 	=> Mage::helper('adminhtml')->__('Visible Order Supply Statuses'),
                'class' 	=> 'input-select',
                'required' 	=> false,
                'values'	=> Mage::getModel('magedoc/source_order_supply_status')->getAllOptions(false)
            )
        );

        $data = $role->getData();

        $form->setValues($data);

        $this->setForm($form);
    }

}
