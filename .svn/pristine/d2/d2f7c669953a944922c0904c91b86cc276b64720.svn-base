<?php

class Testimonial_MageDoc_Block_Adminhtml_Customer_Edit_Tab_Vehicles extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('magedoc/customer/tab/vehicles.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'  => Mage::helper('customer')->__('Delete Vehicle'),
                    'name'   => 'delete_vehicle',
                    'element_name' => 'delete_vehicle',
                    'disabled' => $this->isReadonly(),
                    'class'  => 'delete' . ($this->isReadonly() ? ' disabled' : '')
                ))
        );
        $this->setChild('add_vehicle_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'  => Mage::helper('customer')->__('Add New Vehicle'),
                    'id'     => 'add_vehicle_button',
                    'name'   => 'add_vehicle_button',
                    'element_name' => 'add_vehicle_button',
                    'disabled' => $this->isReadonly(),
                    'class'  => 'add'  . ($this->isReadonly() ? ' disabled' : ''),
                    'onclick'=> 'customerVehicles.addNewAddress()'
                ))
        );
        $this->setChild('cancel_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'  => Mage::helper('customer')->__('Cancel'),
                    'id'     => 'cancel_add_vehicle'.$this->getTemplatePrefix(),
                    'name'   => 'cancel_vehicle',
                    'element_name' => 'cancel_vehicle',
                    'class'  => 'cancel delete-address'  . ($this->isReadonly() ? ' disabled' : ''),
                    'disabled' => $this->isReadonly(),
                    'onclick'=> 'customerVehicles.cancelAdd(this)',
                ))
        );
        return parent::_prepareLayout();
    }

    /**
     * Check block is readonly.
     *
     * @return boolean
     */
    public function isReadonly()
    {
        $customer = Mage::registry('current_customer');
        return $customer->isReadonly();
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    /**
     * Initialize form object
     *
     * @return Testimonial_MageDoc_Block_Adminhtml_Customer_Edit_Tab_Vehicles
     */
    public function initForm()
    {
        /* @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::registry('current_customer');
        $vehiclesCollection = $customer->getVehiclesCollection();

         $form = new Testimonial_MageDoc_Block_Adminhtml_Form_Extended();
        $fieldset = $form->addFieldset('vehicle_fieldset', array(
            'legend'    => Mage::helper('magedoc')->__("Edit Customer's Vehicle"))
        );

        $vehicle = $fieldset->addField('vehicle', 'chooser', array())
            ->setRenderer(Mage::app()->getLayout()->createBlock('magedoc/adminhtml_widget_chooser_customer_vehicle'));
        $vehicle->getRenderer()->setForm($form);

        $fieldset->addField('vin', 'text', array(
            'name' => "vin",
            'label' => Mage::helper('magedoc')->__('VIN'),
            'title'     => Mage::helper('magedoc')->__('VIN'),
            'class' => 'validate-alphanum',
            'maxlength' => 17,
        ));

        $fieldset->addField('mileage', 'text', array(
            'name' => "mileage",
            'label' => Mage::helper('magedoc')->__('Mileage'),
            'title' => Mage::helper('magedoc')->__('Mileage'),
            'class' => 'validate-digits',
        ));

        $this->assign('vehiclesCollection', $vehiclesCollection);
        $this->setForm($form);

        return $this;
    }

    public function getCancelButtonHtml()
    {
        return $this->getChildHtml('cancel_button');
    }

    public function getAddNewButtonHtml()
    {
        return $this->getChildHtml('add_vehicle_button');
    }

    public function getTemplatePrefix()
    {
        return '_template_';
    }

    /**
     * Return predefined additional element types
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return array(
            'file'      => Mage::getConfig()->getBlockClassName('adminhtml/customer_form_element_file'),
            'image'     => Mage::getConfig()->getBlockClassName('adminhtml/customer_form_element_image'),
            'boolean'   => Mage::getConfig()->getBlockClassName('adminhtml/customer_form_element_boolean'),
        );
    }

    /**
     * Add specified values to name prefix element values
     *
     * @param string|int|array $values
     * @return Mage_Adminhtml_Block_Customer_Edit_Tab_Addresses
     */
    public function addValuesToNamePrefixElement($values)
    {
        if ($this->getForm() && $this->getForm()->getElement('prefix')) {
            $this->getForm()->getElement('prefix')->addElementValues($values);
        }
        return $this;
    }

    /**
     * Add specified values to name suffix element values
     *
     * @param string|int|array $values
     * @return Mage_Adminhtml_Block_Customer_Edit_Tab_Addresses
     */
    public function addValuesToNameSuffixElement($values)
    {
        if ($this->getForm() && $this->getForm()->getElement('suffix')) {
            $this->getForm()->getElement('suffix')->addElementValues($values);
        }
        return $this;
    }

    public function getChooserRequestUrl()
    {
        return $this->getUrl('magedoc/sales_order/request/');
    }
}
