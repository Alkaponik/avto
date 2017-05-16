<?php

class Testimonial_SugarCRM_Block_Adminhtml_Permission_User_Edit_Tab_Main extends Mage_Adminhtml_Block_Permissions_User_Edit_Tab_Main
{
    protected function _prepareForm()
    {
        $result = parent::_prepareForm();
        $model = Mage::registry('permissions_user');
        $form = $this->getForm();

        $fieldset = $form->getElements()->searchById('base_fieldset');

        $fieldset->addField('sugarcrm_user_id', 'text', array(
            'name'  => 'sugarcrm_user_id',
            'label' => Mage::helper('adminhtml')->__('SugarCRM User Id'),
            'id'    => 'sugarcrm_user_id',
            'title' => Mage::helper('adminhtml')->__('SugarCRM User Id'),
            'required' => false,
        ));

        $data = $model->getData();

        unset($data['password']);

        $form->setValues($data);

        return $result;
    }
}
