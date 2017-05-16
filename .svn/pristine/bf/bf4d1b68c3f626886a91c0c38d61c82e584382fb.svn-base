<?php

class Testimonial_MageDoc_Block_Adminhtml_Retailer_Edit_Tab_Settings_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $this->setForm($form);

        $retailer = Mage::registry('retailer');

        if ($retailer->getImportSourceCollection()->getSize() &&
            $retailer->getImportConfigCollection()->getSize()) {
            $scheduleFieldset = $form->addFieldset('schedule_retailer_form', array('legend'=>Mage::helper('magedoc')->__('Import Schedule')));

            /** @var  $scheduleCollection Testimonial_MageDoc_Model_Mysql4_Retailer_Data_Price_Upload_Schedule_Collection */
            $crontabCollection = $retailer->getPriceCrontabCollection();
            $scheduleFieldset->addField(
                'import_schedule', 'text',
                array(
                    'label' => Mage::helper('magedoc')->__('Import Schedule'),
                    'name'  => 'import_schedule',
                    'required'    => true,
                    'class' => 'required-entry',
                    'cols_titles' => array(
                        Mage::helper('magedoc')->__('Schedule'),
                        Mage::helper('magedoc')->__('Source config'),
                        Mage::helper('magedoc')->__('Adapter config'),
                        Mage::helper('magedoc')->__('Start new session'),
                        Mage::helper('magedoc')->__('Action'),
                    )
                )
            );

            $form->getElement('import_schedule')->setRenderer(
                $this->getLayout()->createBlock('magedoc/adminhtml_widget_form_renderer_retailerSchedule')->setForm($form)
            );
            $form->getElement('import_schedule')->setValue($crontabCollection);
        }

        $model = Mage::registry('retailer')->getImportSettingsRule();

        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('*/adminhtml_retailer/newConditionHtml/form/rule_conditions_fieldset'));

        $fieldset = $form->addFieldset('conditions_fieldset', array(
                'legend'=>Mage::helper('catalogrule')->__('Conditions (leave blank for all products)'))
        )->setRenderer($renderer);

        $fieldset->addField('conditions', 'text', array(
            'name' => 'conditions',
            'label' => Mage::helper('catalogrule')->__('Conditions'),
            'title' => Mage::helper('catalogrule')->__('Conditions'),
            'required' => true,
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        return parent::_prepareForm();
    }
}