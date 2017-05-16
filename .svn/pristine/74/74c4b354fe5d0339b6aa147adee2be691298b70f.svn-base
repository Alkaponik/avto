<?php

class Testimonial_MageDoc_Block_Adminhtml_Retailer_Edit_Tab_Config_Supply_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $this->setForm($form);

        $fieldset = $form->addFieldset('retailer_config_supply', array('legend'=>Mage::helper('magedoc')->__('Retailer supply config')));
        $formSuffix = 'retailer_config_supply';

        $fieldset->addField('delivery_type', 'select',
            array(
                'label'     => Mage::helper('magedoc')->__('Delivery type'),
                'required'  => true,
                'name'      => $form->addSuffixToName('delivery_type', $formSuffix),
                'values'    => Mage::getSingleton('magedoc/source_retailer_config_supply')->toOptionArray( )
        ));

        $fieldset->addField('delivery_term_days', 'text',
            array(
                 'label'     => Mage::helper('magedoc')->__('Delivery term days'),
                 'name'      => $form->addSuffixToName('delivery_term_days', $formSuffix),
            ));

        $fieldset->addField('order_hours_end_formatted', 'time',
            array(
                 'label'     => Mage::helper('magedoc')->__('Order hours end'),
                 'name'      => $form->addSuffixToName('order_hours_end_formatted', $formSuffix),
            ));

        $fieldset->addField('express_delivery_cost', 'text',
            array(
                 'label'     => Mage::helper('magedoc')->__('Express Delivery Cost'),
                 'name'      => $form->addSuffixToName('express_delivery_cost', $formSuffix),
            ));

        $fieldset->addField('description', 'text',
            array(
                 'label'     => Mage::helper('magedoc')->__('Description'),
                 'name'      => $form->addSuffixToName('description', $formSuffix),
            ));

        if ( Mage::getSingleton('adminhtml/session')->getRetailerData() ) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getData('retailer_data/retailer_config_supply'));
            Mage::getSingleton('adminhtml/session')->setRetailerData(null);
        } elseif ( Mage::registry('retailer') ) {
            $form->setValues(Mage::registry('retailer')->getSupplyConfig()->getData());
        }
        return parent::_prepareForm();
    }
}