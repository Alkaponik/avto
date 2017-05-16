<?php

class Testimonial_MageDoc_Block_Adminhtml_Model_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('model_form', array('legend'=>Mage::helper('magedoc')->__('Item information')));

        $this->_addElementTypes($fieldset);

        $fieldset->addField('td_mod_id', 'hidden', array(
            'name' => 'td_mod_id',
        ));

        /*
        $fieldset->addField('mod_mfa_id', 'select', array(
            'label'     => Mage::helper('magedoc')->__('Manufacturer'),
            'name'      => 'mod_mfa_id',
            'values'    => Mage::getModel('magedoc/source_manufacturer')->getCollectionArray(),

        ));*/

        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('magedoc')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
        ));
     
        $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('magedoc')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
        ));

        $fieldset->addField('url_key', 'text', array(
            'label'     => Mage::helper('magedoc')->__('Url Key'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'url_key',
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

        $fieldset->addField('visible', 'select', array(
            'label'     => Mage::helper('magedoc')->__('Visible'),
            'name'      => 'visible',
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
        
        $fieldset->addField('description', 'editor', array(
          'name'      => 'description',
          'label'     => Mage::helper('magedoc')->__('Description'),
          'style'     => 'width:700px; height:500px;',
          'required'  => false,
        ));

        if ( Mage::getSingleton('adminhtml/session')->getModelData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getModelData());
            Mage::getSingleton('adminhtml/session')->setModelData(null);
        } elseif ( Mage::registry('model') ) {
            $form->setValues(Mage::registry('model')->getData());
        }
        return parent::_prepareForm();
    }

    protected function _getAdditionalElementTypes()
    {
        return array(
            'image' => Mage::getConfig()->getBlockClassName('magedoc/adminhtml_model_helper_image'));
    }
}