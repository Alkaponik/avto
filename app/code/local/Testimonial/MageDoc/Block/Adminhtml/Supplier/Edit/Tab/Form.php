<?php

class Testimonial_MageDoc_Block_Adminhtml_Supplier_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('supplier_form', array('legend'=>Mage::helper('magedoc')->__('Supplier information')));

        $this->_addElementTypes($fieldset);

        $fieldset->addField('td_sup_id', 'hidden', array(
                'name' => 'td_sup_id',
        ));

        $fieldset->addField('title', 'text', array(
                'label'     => Mage::helper('magedoc')->__('Title'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'title',
        ));

        $fieldset->addField('logo', 'image', array(
                'label'     => Mage::helper('magedoc')->__('Logo'),
                'required'  => false,
                'name'      => 'logo',
        ));

        $fieldset->addField('enabled', 'select', array(
                'label'     => Mage::helper('magedoc')->__('Status'),
                'name'      => 'enabled',
                'values'    => array(
                        array(
                                'value'     => 0,
                                'label'     => Mage::helper('magedoc')->__('Disabled'),
                        ),

                        array(
                                'value'     => 1,
                                'label'     => Mage::helper('magedoc')->__('Enabled'),
                        ),
                ),
        ));

        if ($supplier = Mage::registry('supplier')){
            $form->setValues($supplier->getData());
        }

        return parent::_prepareForm();
    }

    protected function _getAdditionalElementTypes()
    {
        return array(
                'image' => Mage::getConfig()->getBlockClassName('magedoc/adminhtml_supplier_helper_image'));
    }
}