<?php

class Testimonial_MageDoc_Block_Adminhtml_Retailer_Edit_Tab_Price_Upload_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareLayout()
    {
        $this->setChild('form_after',
            $this->getLayout()->createBlock('magedoc/adminhtml_retailer_edit_tab_form_session_stat')
        );

        if ($retailer = Mage::registry('retailer')
            and $session = $retailer->getLastSession()
            and !$session->isFailed()) {
            $preview = $this->getLayout()->createBlock('magedoc/adminhtml_retailer_edit_tab_price_upload_preview_grid');
            $this->getChild('form_after')
                ->append( $preview );
        }

        return parent::_prepareLayout();
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $this->setForm($form);
        $fieldset = $form->addFieldset('retailer_form', array('legend'=>Mage::helper('magedoc')->__('Price Upload')));
        $formSuffix = 'retailer';


        $fieldset->addField('price', 'file',
            array(
                'label'     => Mage::helper('magedoc')->__('Add price file'),
                'required'  => false,
                'name'      => $form->addSuffixToName('price', $formSuffix),
            ));

        $fieldset->addField('import_adapter_config', 'select',
            array(
                'label'     => Mage::helper('magedoc')->__('Retailer import adapter configuration'),
                'name'      => $form->addSuffixToName('import_adapter_config', $formSuffix),
                'values'    => Mage::getModel('magedoc/retailer_data_import_adapter_config')
                    ->getCollection()
                    ->addFieldToFilter('retailer_id', Mage::registry('retailer')->getId())
                    ->toOptionArray( )
            ));

        $fieldset->addField('prepare_base_table', 'checkbox',
            array(
                'label'  => Mage::helper('magedoc')->__('Start new session'),
                'name'   => $form->addSuffixToName('prepare_base_table', $formSuffix),
                'checked' => 1,
            )
        );

        return parent::_prepareForm();
    }
}