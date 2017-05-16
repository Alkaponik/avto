<?php

class Testimonial_MageDoc_Block_Adminhtml_Order_Create_Form_Inquiry extends Mage_Adminhtml_Block_Sales_Order_Create_Form_Abstract
{
    public function getHeaderCssClass()
    {
        return 'head-account';
    }

    protected function _prepareForm()
    {

        $this->_form = new Varien_Data_Form();
        $inquiries = $this->_form->addFieldset('inquiries', array('legend'=>Mage::helper('magedoc')->__('Customer Inquiries')));
        $inquiries->setRenderer(Mage::getBlockSingleton('magedoc/adminhtml_widget_form_renderer_fieldset'));
        foreach($this->getVehicles() as $vehicle){
            $this->addInquiry($inquiries, $vehicle, "inquiry_{$vehicle->getId()}", 'block');
        }
        $this->addInquiry($inquiries);
    }


    public function addInquiry($fieldset = null, $data = null, $containerId = 'inquiry-template', $visibility = 'none')
    {
        $dataId = '';
        $disabled = 'disabled';
        if(is_null($fieldset)){
            $fieldset = $this->_form;
        }
        if(!is_null($data)){
            $dataId = $data->getId();
            $disabled = '';
        }
        //$customerId = !is_null($data)? $data->getCustomerId(): null;
        $customer = $this->getCustomer();

        $inquiry = $fieldset->addFieldset('inquiry' . $dataId, array('legend'=>Mage::helper('magedoc')->__('Items Inquired')));
        $inquiry->setFieldsetContainerId($containerId);
        $inquiry->setVisibility($visibility);
        $inquiry->setRenderer(Mage::getBlockSingleton('magedoc/adminhtml_widget_form_renderer_fieldset_inquiry'));
        $this->addInquiryTypes($inquiry);

        $vehicleId = $dataId? $dataId: '#{_vehicle_id}';
        $customerVehicleId = !is_null($data) && $data->getCustomerVehicleId()
            ? $data->getCustomerVehicleId()
            : $customer->getVehiclesCollection()->getFirstItem()->getVehicleId();
        $element = $inquiry->addField('customer_vehicle' . $dataId, 'select', array(
            'name'      => "inquiry[$vehicleId][vehicle][customer_vehicle_id]",
            'label'     => Mage::helper('magedoc')->__('Select from existing customer vehicles:'),
            'title'     => Mage::helper('magedoc')->__('Select from existing customer vehicles:'),
            'value'     => $customerVehicleId,
            'options'   => Mage::getModel('magedoc/source_customer_vehicle')->setCustomerId($customer->getId())->getOptionArray(),
            'disabled'  => $disabled,
            'class'     => 'customer-vehicle',
            'after_element_html' => "<script>var customerVehicles = {$this->getCustomerVehiclesCollectionJson()}</script>",
        ));
        $element->setRenderer(Mage::getBlockSingleton('magedoc/adminhtml_widget_form_renderer_element'));

        $element = $inquiry->addField('is_filter' . $dataId, 'checkbox', array('label' => 'Filter', 'checked' => 'checked'));
        $element->setRenderer(Mage::getBlockSingleton('magedoc/adminhtml_widget_form_renderer_element'));

        $inquiry->addField('vehicle' . $dataId, 'chooser', array())
            ->setRenderer(Mage::app()->getLayout()->createBlock('magedoc/adminhtml_widget_chooser_vehicle')
            ->setData(array('id' => 'chooser_' . $containerId, 'vehicle' => $data, 'disabled' => $disabled)));

        $vin = !is_null($data)? $data->getVin(): null;
        $element = $inquiry->addField('vin' . $dataId, 'text', array(
            'name'      => "inquiry[$vehicleId][vehicle][vin]",
            'label'     => Mage::helper('magedoc')->__('VIN'),
            'title'     => Mage::helper('magedoc')->__('VIN'),
            'class'     => 'validate-alphanum vin',
            'disabled'  => $disabled,
            'value'     => $vin,
            'maxlength' => 17,
        ));
        $element->setRenderer(Mage::getBlockSingleton('magedoc/adminhtml_widget_form_renderer_element'));

        $mileage = !is_null($data)? $data->getMileage(): null;
        $element = $inquiry->addField('mileage' . $dataId, 'text', array(
            'name'      => "inquiry[$vehicleId][vehicle][mileage]",
            'label'     => Mage::helper('magedoc')->__('Mileage'),
            'title'     => Mage::helper('magedoc')->__('Mileage'),
            'class'     => 'validate-digits mileage',
            'value'     => $mileage,
            'disabled'  => $disabled,
        ));
        $element->setRenderer(Mage::getBlockSingleton('magedoc/adminhtml_widget_form_renderer_element'));

        $inquiry->addField('inquiries_grid' . $dataId, 'grid', array())
            ->setData(array('id' => 'grid_' . $containerId, 'vehicle' => $data))
            ->setRenderer(Mage::app()->getLayout()->createBlock('magedoc/adminhtml_order_create_inquiry_grid'));

        
        return $inquiry;
    }
    
    public function addInquiryTypes($fieldset)
    {
        $fieldset->addType('chooser', 'Testimonial_MageDoc_Block_Adminhtml_Form_Element_Chooser');
        $fieldset->addType('grid', 'Testimonial_MageDoc_Block_Adminhtml_Form_Element_Grid');
    }
    
    
    public function getVehicles()
    {
        return Mage::getModel('magedoc/session_quote')->getQuote()->getVehiclesCollection();
    }

    public function getCustomerVehiclesCollectionJson()
    {
        $data = array();
        foreach($this->getCustomer()->getVehiclesCollection() as $vehicle){
            $data[$vehicle->getId()] = $vehicle->toArray();
        }
        return Mage::helper('core')->jsonEncode($data);
    }
}
