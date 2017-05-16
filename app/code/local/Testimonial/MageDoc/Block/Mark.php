<?php

class Testimonial_MageDoc_Block_Mark extends Mage_Core_Block_Template
{
    
    
    protected $_marksCollection;
    
    protected $_types;

    public function __construct()
    {
        return parent::__construct();
    }
    
   
    public function getMarksCollection()
    {
        if (!isset($this->_marksCollection)){
            $manufacturer = Mage::registry('manufacturer');
            $this->_marksCollection = Mage::getResourceModel('magedoc/tecdoc_mark_collection')
                    ->addManufacturerFilter($manufacturer)
                    ->addYearStartFilter()
                    ->joinDesignations();
        }
        return $this->_marksCollection;
    }
            
    public function getTypeByMarkId($id)
    {
       $markTypesCollection = Mage::getResourceModel('magedoc/tecdoc_type_collection');
        
        return $this->_types = $markTypesCollection->getTypeByMarkId($id);
    }

}
