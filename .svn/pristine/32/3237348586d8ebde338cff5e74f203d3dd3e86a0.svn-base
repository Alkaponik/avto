<?php

class Testimonial_MageDoc_Block_Adminhtml_Price_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm() 
    {
        $form = new Testimonial_MageDoc_Block_Adminhtml_Form_Extended();
        $this->setForm($form);
        $fieldset = $form->addFieldset('price_form', array('legend' => Mage::helper('magedoc')->__('Price information')));
        
        $fieldset->addField('name', 'text', array(
            'label' => Mage::helper('magedoc')->__('Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'name',
        ));

        $manufacturer = Mage::getModel('catalog/product')->getResource()->getAttribute('manufacturer');
        
        $element = $fieldset->addField('manufacturer_id', 'combobox', array(
            'name'          => "manufacturer",
            'label'         => 'Manufacturer',
            'values'        => $manufacturer->getSource()->getAllOptions(true),
            'text_index'    => 'manufacturer',
            'input_name'    => "manufacturer",
            'select_name'   => "manufacturer_id"
        ));        

        $fieldset->addField('supplier_id', 'select', array(
            'label' => Mage::helper('magedoc')->__('Supplier'),
            'required' => true,
            'name' => 'supplier_id',
            'values' => Mage::getModel('magedoc/source_supplier')->getOptionArray()
        ));


        $fieldset->addField('code', 'text', array(
            'label' => Mage::helper('magedoc')->__('Code'),
            'required' => true,
            'name' => 'code',
            'class' => 'required-entry',
        ));

        $fieldset->addField('cost', 'text', array(
            'label' => Mage::helper('magedoc')->__('Cost'),
            'required' => true,
            'name' => 'cost',
            'class' => 'required-entry',
        ));

        $fieldset->addField('price', 'text', array(
            'label' => Mage::helper('magedoc')->__('Price'),
            'required' => true,
            'name' => 'price',
            'class' => 'required-entry',
        ));


        $fieldset->addField('retailer_id', 'select', array(
            'label' => Mage::helper('magedoc')->__('Retailer'),
            'name' => 'retailer_id',
            'values' => Mage::getModel('magedoc/source_retailer')->getOptionArray()
        ));

        $fieldset->addField('domestic_stock_qty', 'text', array(
            'label' => Mage::helper('magedoc')->__('Domestic stock qty'),
            'name' => 'domestic_stock_qty',
            'class' => 'required-entry validate-not-negative-number',
            'required' => true,
        ));

        $fieldset->addField('general_stock_qty', 'text', array(
            'label' => Mage::helper('magedoc')->__('General stock qty'),
            'name' => 'general_stock_qty',
            'class' => 'required-entry validate-not-negative-number',
            'required' => true,
        ));


        if (Mage::getSingleton('adminhtml/session')->getRetailerData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getFormData());
            Mage::getSingleton('adminhtml/session')->setFormData(null);
        } elseif (Mage::registry('price_data')) {
            $form->setValues(Mage::registry('price_data')->getData());
        }
        return parent::_prepareForm();
    }
}