<?php

class Testimonial_MageDoc_Block_Adminhtml_Manufacturer_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('manufacturer_form', array('legend' => Mage::helper('magedoc')->__('Item information')));

        $this->_addElementTypes($fieldset);

        $fieldset->addField('td_mfa_id', 'hidden', array(
            'name' => 'td_mfa_id',
        ));

        $fieldset->addField('name', 'text', array(
            'label' => Mage::helper('magedoc')->__('Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'name',
        ));

        $fieldset->addField('title', 'text', array(
            'label' => Mage::helper('magedoc')->__('Title'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'title',
        ));

        $fieldset->addField('url_key', 'text', array(
            'label' => Mage::helper('magedoc')->__('Url Key'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'url_key',
        ));

        $fieldset->addField('enabled', 'select', array(
            'label' => Mage::helper('magedoc')->__('Status'),
            'name' => 'enabled',
            'values' => array(
                array(
                    'value' => 0,
                    'label' => Mage::helper('magedoc')->__('Disabled'),
                ),
                array(
                    'value' => 1,
                    'label' => Mage::helper('magedoc')->__('Enabled'),
                ),
            ),
        ));

        $fieldset->addField('logo', 'image', array(
            'label' => Mage::helper('magedoc')->__('Logo'),
            'required' => false,
            'name' => 'logo',
        ));

        $fieldset->addField('description', 'editor', array(
            'name' => 'description',
            'label' => Mage::helper('magedoc')->__('Description'),
            'style' => 'width:700px; height:200px;',
            'required' => false,
        ));

        $fieldset->addField('bottom_content', 'editor', array(
            'name' => 'bottom_content',
            'label' => Mage::helper('magedoc')->__('Bottom Content'),
            'style' => 'width:700px; height:200px;',
            'required' => false,
        ));

        $fieldset->addField('meta_keywords', 'textarea', array(
            'name' => 'meta_keywords',
            'label' => Mage::helper('magedoc')->__('Meta Keywords'),
            'required' => false,
        ));

        $fieldset->addField('meta_description', 'textarea', array(
            'name' => 'meta_description',
            'label' => Mage::helper('magedoc')->__('Meta Description'),
            'required' => false,
        ));

        if (Mage::getSingleton('adminhtml/session')->getManufacturerData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getManufacturerData());
            Mage::getSingleton('adminhtml/session')->setManufacturerData(null);
        } elseif (Mage::registry('manufacturer')) {
            $form->setValues(Mage::registry('manufacturer')->getData());
        }
        return parent::_prepareForm();
    }

    protected function _getAdditionalElementTypes()
    {
        return array(
            'image' => Mage::getConfig()->getBlockClassName('magedoc/adminhtml_manufacturer_helper_image'));
    }
}