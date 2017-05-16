<?php

class Testimonial_MageDoc_Block_Adminhtml_Retailer_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $this->setForm($form);
        $form->setFieldNameSuffix('retailer');
        $fieldset = $form->addFieldset('retailer_form', array('legend'=>Mage::helper('magedoc')->__('Retailer information')));
        $formSuffix = 'retailer';
        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('magedoc')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
        ));

        $fieldset->addField('model', 'select', array(
            'label'     => Mage::helper('magedoc')->__('Retailer Import Model'),
            'name'      => 'model',
            'values'    =>  Mage::getModel('magedoc/source_retailer_data_import_model')->getOptionArray()
        ));


        $fieldset->addField('rate', 'text', array(
            'label'     => Mage::helper('magedoc')->__('Rate'),
            'class'     => 'validate-number',
            'required'  => false,
            'name'      => 'rate',
        ));


        $fieldset->addField('enabled', 'select', array(
            'label'     => Mage::helper('magedoc')->__('Enabled'),
            'name'      => 'enabled',
            'values'    =>  Mage::getModel('eav/entity_attribute_source_boolean')->getAllOptions()
        ));


        $fieldset->addField('is_import_enabled', 'select', array(
            'label'     => Mage::helper('magedoc')->__('Import Enabled'),
            'name'      => 'is_import_enabled',
            'values'    =>  Mage::getModel('eav/entity_attribute_source_boolean')->getAllOptions()
        ));



        $fieldset->addField('use_for_autopricing', 'select', array(
            'label'     => Mage::helper('magedoc')->__('Use retailers prices in autopricing'),
            'name'      => 'use_for_autopricing',
            'values'    =>  Mage::getModel('eav/entity_attribute_source_boolean')->getOptionArray()
        ));

        $fieldset->addField('show_on_frontend', 'select',
            array(
                'label'     => Mage::helper('magedoc')->__('Show on frontend'),
                'name'      => 'show_on_frontend',
                'values'    =>  Mage::getModel('eav/entity_attribute_source_boolean')->getAllOptions()
            ));

        $fieldset->addField('margin_ratio', 'text', array(
            'label'     => Mage::helper('magedoc')->__('Retailer Margin Ratio'),
            'name'      => 'margin_ratio',
            'class'     => 'required-entry validate-not-negative-number',
            'required'  => true,
        ));

        $fieldset->addField(
            'stock_status', 'select',
            array(
                'label' => Mage::helper('magedoc')->__('Stock status'),
                'name'  => 'stock_status',
                'values'    =>  Mage::getModel('magedoc/source_stock_status')->getOptionArray()
            )
        );

        $fieldset->addField(
            'fixed_fee', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('Fixed fee'),
                'name'  => 'fixed_fee',
            )
        );

        $fieldset->addField(
            'price_validity_term', 'text',
            array(
                 'label' => Mage::helper('magedoc')->__('Price validity term'),
                 'name'  => 'price_validity_term',
            )
        );

        $fieldset->addField(
            'discount_table', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('Discount percent table'),
                'name'  => 'discount_table',
                'required'    => true,
                'class' => 'required-entry',
                'cols_titles' => array(
                    Mage::helper('magedoc')->__('Lower limit'),
                    Mage::helper('magedoc')->__('Value'),
                    Mage::helper('magedoc')->__('Action'),
                )
            )
        );

        $fieldset->addField(
            'margin_table', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('Margin percent table'),
                'name'  => 'margin_table',
                'required'    => true,
                'class' => 'required-entry',
                'cols_titles' => array(
                    Mage::helper('magedoc')->__('Lower limit'),
                    Mage::helper('magedoc')->__('Value'),
                    Mage::helper('magedoc')->__('Action'),
                )
            )
        );

        $tableFieldRenderer = $this->getLayout()
            ->createBlock('magedoc/adminhtml_widget_form_renderer_discountTable')
                ->setForm($form);

        $form->getElement('discount_table')->setRenderer(
            $tableFieldRenderer
        );

        $form->getElement('margin_table')->setRenderer(
            $tableFieldRenderer
        );

        if ( Mage::getSingleton('adminhtml/session')->getRetailerData() ) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getRetailerData());
        } elseif ( Mage::registry('retailer') ) {
            $form->setValues(Mage::registry('retailer')->getData());
        }
        return parent::_prepareForm();
    }
}