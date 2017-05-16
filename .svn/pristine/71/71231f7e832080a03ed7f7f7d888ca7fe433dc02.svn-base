<?php

class Testimonial_CustomerNotification_Block_Adminhtml_Message_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {

        $form = new Varien_Data_Form(
            array('id' => 'edit_form', 'action' => $this->getUrl('*/*/sendMessage'), 'method' => 'post')
        );

        $fieldset = $form->addFieldset('notification', array('legend'=>Mage::helper('customernotification')->__('Notification')));

        $fieldset->addField('recipient', 'text', array(
            'name'      => 'recipient',
            'label'     => Mage::helper('customernotification')->__('Telephone'),
            'title'     => Mage::helper('customernotification')->__('Telephone'),
            'onchange' => $this->getDataByTelephone(),
            'required'  => true,
        ));

        $fieldset->addField('customer_name', 'text', array(
            'name'      => 'customer_name',
            'label'     => Mage::helper('customernotification')->__('Name'),
            'title'     => Mage::helper('customernotification')->__('Name'),
            'required'  => true,
        ));

        $fieldset->addField('channel', 'select', array(
            'name'      => 'channel',
            'label'     => Mage::helper('customernotification')->__('Channel'),
            'title'     => Mage::helper('customernotification')->__('Channel'),
            'options'   => array('sms' => 'SMS'),
            'required'  => true,
        ));

        $fieldset->addField('text', 'textarea', array(
            'name'      => 'text',
            'label'     => Mage::helper('customernotification')->__('Message'),
            'title'     => Mage::helper('customernotification')->__('Message'),
            'required'  => true,
        ));

        $fieldset->addField('customer_id', 'hidden', array(
            'name'      => 'customer_id',
            'value'     => '',
        ));


        //$form->setValues($model->getData());

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getDataByTelephone()
    {
        $url = Mage::getUrl('adminhtml/customerNotification_message/getDataByTelephone');
        $request =
            <<<EOT
            new Ajax.Request('$url', {
                    method: 'post',
                    parameters: { value: $('recipient').getValue()},
                    onSuccess: function(response) {
                        var data = response.responseText.evalJSON();
                        var name = $('customer_name');
                        var customerId = $('customer_id');
                        if(name && customerId){
                            name.value = data.name;
                            customerId.value = data.customer_id;
                        }
                    }
                })
EOT;
        return $request;
    }

}
