<?php

class Testimonial_MageDoc_Block_Model_List extends Mage_Core_Block_Template
{
    /**
     * @var Testimonial_MageDoc_Model_Mysql4_Tecdoc_Model_Collection
     */
    protected $_models;

    public function __construct()
    {
        return parent::__construct();
    }
    
   
    public function getModelCollection($manufacturer = null)
    {
        if (!isset($this->_models)){
            if (is_null($manufacturer)){
                $manufacturer = $this->getManufacturer();
            }
            $this->_models = Mage::getResourceModel('magedoc/tecdoc_model_collection')
                    ->addManufacturerFilter($manufacturer)
                    ->addYearStartFilter();
            $this->_models->getResource()->prepareFullSelect($this->_models->getSelect(), 'main_table');
            $this->_models->getSelect()->order(array('MOD_CDS_TEXT', 'MOD_PCON_START DESC'));
        }
        return $this->_models;
    }

    public function getManufacturer()
    {
        return $this->hasManufacturer()
                ? $this->getData('manufacturer')
                : Mage::registry('manufacturer');
    }

}
