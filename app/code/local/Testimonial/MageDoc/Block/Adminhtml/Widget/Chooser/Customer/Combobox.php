<?php

class Testimonial_MageDoc_Block_Adminhtml_Widget_Chooser_Customer_Combobox
    extends Testimonial_MageDoc_Block_Adminhtml_Widget_Combobox
{
    protected $_containerId;
    
    public function __construct() 
    {
        parent::__construct();
        
        $this->setTemplate('magedoc/widget/customer/combobox.phtml');
    }
    
    public function prepareElementName($vehicleId = null)
    {
        if(is_null($vehicleId)){
            $vehicleId = '#{_vehicle_id}';
            if($this->getForm() && $this->getForm()->getHtmlIdPrefix()){
                $vehicleId = $this->getForm()->getHtmlIdPrefix();
            }
        }
        $this->setInputName("vehicle[{$vehicleId}][{$this->getName()}]");
        $this->setSelectName("vehicle[{$vehicleId}][{$this->getName()}_id]");
        
        return $this;
    }

    public function getInputId()
    {
        $id = $this->getId();
        $id = $id != 'date'? $id: 'production_start_year';
        if($this->getForm() && $this->getForm()->getHtmlIdPrefix()){
            $htmlIdPrefix = $this->getForm()->getHtmlIdPrefix();
            $id = $htmlIdPrefix.$id;
        }

        return $id;
    }

    public function getSelectId()
    {
        $id = $this->getInputId();
        return $id.'_id';
    }
}