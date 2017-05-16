<?php
class Testimonial_MageDoc_Block_Price extends Mage_Core_Block_Template
{
    protected $_priceCollection;

    public function getPriceCollection()
    {
        if(!isset($this->_priceCollection)){
            $this->_priceCollection = Mage::getResourceModel('magedoc/import_retailer_data_collection')
                ->joinRetailer()
                ->addProductFilter($this->getProduct());
        }   
        return $this->_priceCollection;
    }
    
    public function getPricesArray()
    {
        $pricesArray = array();
        foreach($this->getPriceCollection() as $item){
            $pricesArray[$item->getRetailerId()] = $item->getData();
        }
        return $pricesArray;
    }
    
    public function getProduct()
    {
        return $this->hasProduct()
                ? $this->getData('product')
                : Mage::registry('product');
    }
}