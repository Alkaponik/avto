<?php

class Testimonial_MageDoc_Block_Adminhtml_Widget_Chooser_Vehicle extends Testimonial_MageDoc_Block_Adminhtml_Widget_Chooser 
    implements Varien_Data_Form_Element_Renderer_Interface
{

    public function getVehicle()
    {
        if($this->getData('vehicle') === null){
            $this->setVehicle(Mage::getModel('magedoc/order_vehicle'));
        }

        return $this->getData('vehicle');
    }

    public function addCombobox($comboId, $options = array())
    {
        $combobox = $this->getLayout()->createBlock('magedoc/adminhtml_widget_combobox');
        $combobox->setData($options);
        $combobox->setId($comboId);
        $combobox->prepareElementName($this->getVehicle()->getId());
        $this->_comboboxes[$comboId]=$combobox;
        return $this;
    }

    public function prepareCombos()
    {
        $vehicle = $this->getVehicle();
        
        $this->addCombobox('manufacturer', array(
            'name'          => "manufacturer",
            'label'         => $this->__('Manufacturer'),
            'default_text'  => $vehicle->getManufacturer(),
            'value'         => $vehicle->getManufacturerId(),
            'values'       => Mage::getModel('magedoc/source_manufacturer')
                                ->addTitles(false)
                                ->getOptionArray(),
            'is_default'    => true,
            'disabled'      => $this->getDisabled(),
        ));
        
       $this->addCombobox('date', array(
            'name'          => "production_start_year",
            'values'       => Mage::getModel('magedoc/source_date')->getOptionArray(),
            'label'         => $this->__('Date'),
            'default_text'  => $vehicle->getProductionStartYear(),
            'value'         => !$vehicle->getProductionStartYear() 
                                    ? '' 
                                    : $vehicle->getProductionStartYear() . '00',
            'disabled'      => $this->getDisabled()
            
        ));
       
       if(($vehicle->getProductionStartYear() !== null) && ($vehicle->getManufacturerId() !== null)) {
           $options = Mage::getModel('magedoc/source_model')
                    ->setYearStart($vehicle->getProductionStartYear())
                    ->setManufacturerId($vehicle->getManufacturerId())
                    ->getOptionArray();
       }else{
           $options = array();
       }
       $this->addCombobox('model', array(
            'name'          => "model",
            'values'       => $options,
            'label'         => $this->__('Model'),
            'default_text'  => $vehicle->getModel(),
            'disabled'      => $this->getDisabled(),
            'value'         => $vehicle->getModelId(),
            'class'         => 'expand'
        ));
       
       if($vehicle->getModelId() !== null) {
            $options = Mage::getModel('magedoc/source_type')
                    ->setModelId($vehicle->getModelId())
                    ->getOptionArray();
            
       }else{
           $options = array();
       }
       $this->addCombobox('type', array(
            'name'          => "type",
            'values'       => $options,
            'label'         => $this->__('Type'),
            'default_text'  => $vehicle->getType(),
            //'required'      => true,
            //'select_required' => false,
            'disabled'      => $this->getDisabled(),
            'value'         => $vehicle->getTypeId(),
            'class'         => 'expand'
        ));
        return $this;
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        if($element->getValue() !== null){
            $this->setMagedocType($element->getValue());
        }
        return $this->toHtml();
    }

    public function getCurrentValuesJson()
    {
        $values = array();
        $vehicle = $this->getVehicle();
        if($vehicle->getTypeId() !== null){
            $values = array('manufacturer' => $vehicle->getModMfaId(),
                'date' => $vehicle->getProductionStartYear(),
                'model' => $vehicle->getModId(),
                'type' => $vehicle->getId()
            );
        }

        return Mage::helper('core')->jsonEncode($values);
    }
}
