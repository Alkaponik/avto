<?php

class Testimonial_MageDoc_Block_Adminhtml_Widget_Chooser_Customer_Vehicle
    extends Testimonial_MageDoc_Block_Adminhtml_Widget_Chooser_Vehicle
{

    public function _construct()
    {
        $this->setTemplate('magedoc/widget/customer/chooser.phtml');
    }

    public function getVehicle()
    {
        if($this->getData('vehicle') === null){
            $this->setVehicle(Mage::getModel('magedoc/customer_vehicle'));
        }

        return $this->getData('vehicle');
    }

    public function addCombobox($comboId, $options = array())
    {
        $combobox = $this->getLayout()->createBlock('magedoc/adminhtml_widget_chooser_customer_combobox');
        $combobox->setData($options);
        $combobox->setId($comboId);
        $combobox->setForm($this->getForm());
        $combobox->prepareElementName($this->getVehicle()->getId());
        $this->_comboboxes[$comboId]=$combobox;
        return $this;
    }
}
