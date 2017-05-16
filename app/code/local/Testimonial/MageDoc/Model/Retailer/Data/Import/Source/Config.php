<?php
class Testimonial_MageDoc_Model_Retailer_Data_Import_Source_Config extends Mage_Core_Model_Abstract
{
    protected $_retailer;

    protected function _construct()
    {
        $this->_init('magedoc/retailer_data_import_source_config');
    }

    public function setRetailer(Testimonial_MageDoc_Model_Retailer $retailer)
    {
        $this->_retailer = $retailer;
        $this->setRetailerId($retailer->getId());
        return $this;
    }

    public function getRetailer()
    {
        if(is_null($this->_retailer)){
            $this->_retailer = Mage::getModel('magedoc/retailer')->load( $this->getRetailerId() );
        }

        return $this->_retailer;
    }
}