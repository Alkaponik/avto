<?php

class Testimonial_MageDoc_Block_Catalog_Layer_Chooser 
    extends Testimonial_MageDoc_Block_Vehicle_Chooser
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('magedoc/catalog/layer/chooser.phtml');
    }
    
    public function prepareCombos()
    {
        $type = $this->getMagedocType();
        
        $this->addCombobox('manufacturer', array(
            'name'          => 'manufacturer',
            'container_id'  => 'manufacturer',
            'default_text'  => $type->getManufacturerName(),
            'options'       => Mage::getModel('magedoc/source_manufacturer')
                                        ->setEnabledFilter(true)
                                        ->getExtendedOptionArray('sort_order'),
            'is_default'    => true,
            'placeholder'   => $this->__('Choose manufacturer')
        ));

       $this->addCombobox('date', array(
            'name'          => 'date',
            'container_id'  => 'date',
            'options'       => Mage::getModel('magedoc/source_date')->getOptionArray(),
            'disabled'      => 'disabled',
            'placeholder'   => $this->__('Choose date')
            
        ));
       
       $this->addCombobox('model', array(
            'disabled'      => 'disabled',
            'name'          => 'model',
            'placeholder'   => $this->__('Choose model')
        ));
       
       $this->addCombobox('type', array(
            'disabled'      => 'disabled',
            'container_id'  => 'type',
            'name'          => 'vehicle_type',
            'required'      => true,
            'placeholder'   => $this->__('Choose type')
        )); 
        return $this;
    }
    
    
}
