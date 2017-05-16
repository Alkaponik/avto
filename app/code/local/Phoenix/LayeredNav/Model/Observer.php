<?php

class Phoenix_LayeredNav_Model_Observer
{
    public function adminhtml_catalog_product_attribute_edit_prepare_form(Varien_Event_Observer $observer)
    {
        $form = $observer->getEvent()->getForm();
        $attributeObject = $observer->getEvent()->getAttribute();

        $fieldset = $form->getElement('front_fieldset');

        $fieldset->addField('is_multiple_select_filter', 'select', array(
            'name' => 'is_multiple_select_filter',
            'label' => Mage::helper('catalog')->__('Is Multiple Select Filter'),
            'title' => Mage::helper('catalog')->__('Is Multiple Select Filter'),
            'values' => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            'note' => Mage::helper('catalog')->__('Can be used only with catalog input type Dropdown, Multiple Select'),
            ), 'is_filterable_in_search');

        $form->setValues($attributeObject->getData());
    }
}