<?php

class Testimonial_MageDoc_Block_Adminhtml_Report_Filter_Form_Order extends Mage_Sales_Block_Adminhtml_Report_Filter_Form_Order
{
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $form = $this->getForm();
        $htmlIdPrefix = $form->getHtmlIdPrefix();
        /** @var Varien_Data_Form_Element_Fieldset $fieldset */
        $fieldset = $this->getForm()->getElement('base_fieldset');

        $fieldset->removeField('period_type');

        $fieldset->addField('period_type', 'select', array(
            'name' => 'period_type',
            'options' => array(
                'day'   => Mage::helper('reports')->__('Day'),
                'week'  => Mage::helper('reports')->__('Week'),
                'month' => Mage::helper('reports')->__('Month'),
                'year'  => Mage::helper('reports')->__('Year')
            ),
            'label' => Mage::helper('reports')->__('Period'),
            'title' => Mage::helper('reports')->__('Period')
        ), 'report_type');

        if (is_object($fieldset) && $fieldset instanceof Varien_Data_Form_Element_Fieldset) {
            $fieldset->addField('show_supply_status', 'select', array(
                'name'       => 'show_supply_status',
                'options'    => array(
                    '1' => Mage::helper('reports')->__('Yes'),
                    '0' => Mage::helper('reports')->__('No')
                ),
                'label'      => Mage::helper('reports')->__('Show Supply Status'),
            ));

            $fieldset->addField('supply_status', 'multiselect', array(
                'name'       => 'supply_status',
                'values'     => Mage::getSingleton('magedoc/source_order_supply_status')->toOptionArray(),
                'label'      => Mage::helper('magedoc')->__('Supply Status'),
            ));

            $fieldset->addField('show_shipping_method', 'select', array(
                'name'       => 'show_shipping_method',
                'options'    => array(
                    '1' => Mage::helper('reports')->__('Yes'),
                    '0' => Mage::helper('reports')->__('No')
                ),
                'label'      => Mage::helper('magedoc')->__('Show Shipping Method'),
            ));

            $fieldset->addField('show_payment_method', 'select', array(
                'name'       => 'show_payment_method',
                'options'    => array(
                    '1' => Mage::helper('reports')->__('Yes'),
                    '0' => Mage::helper('reports')->__('No')
                ),
                'label'      => Mage::helper('magedoc')->__('Show Payment Method'),
            ));

            if(Mage::getSingleton('admin/session')->isAllowed('report/salesroot/sales/view_all')){
                $fieldset->addField('show_manager', 'select', array(
                    'name'       => 'show_manager',
                    'options'    => array(
                        '1' => Mage::helper('reports')->__('Yes'),
                        '0' => Mage::helper('reports')->__('No')
                    ),
                    'label'      => Mage::helper('reports')->__('Show Manager'),
                ));

                $fieldset->addField('manager_id', 'select', array(
                    'name'       => 'manager_id',
                    'options'    => Mage::getSingleton('magedoc/source_orderManager')->getOptionArray(),
                    'label'      => Mage::helper('magedoc')->__('Manager'),
                ));
            }
        }

        return $this;
    }
}
