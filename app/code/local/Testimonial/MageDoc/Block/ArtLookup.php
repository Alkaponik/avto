<?php
class Testimonial_MageDoc_Block_ArtLookup extends Mage_Core_Block_Template
{
    /**
     * @var Testimonial_MageDoc_Model_Mysql4_Tecdoc_ArtLookup_Original_Collection
     */
    protected $_originalLookupCollection;
    /**
     * @var Testimonial_MageDoc_Model_Mysql4_Tecdoc_ArtLookup_Collection
     */
    protected $_lookupCollection;
        
    public function getOriginalLookupCollection()
    {
        if(!isset($this->_originalLookupCollection)){
        $this->_originalLookupCollection = Mage::getResourceModel("magedoc/tecdoc_artLookup_original_collection")
                ->getOrigianlLookupCollection($this->getProduct());
        }
    
        return $this->_originalLookupCollection;
    }


    public function getLookupCollection()
    {
        if(!isset($this->_lookupCollection)){
            $product = $this->getProduct();
            $this->_lookupCollection = $this->getOriginalLookupCollection();
            $lookUpCollection = Mage::getResourceModel("magedoc/tecdoc_artLookup_collection")
                    ->getLookupCollection($product);
            foreach($lookUpCollection as $item){
                 $this->_lookupCollection->addItem($item);
            }
        }
    
        return $this->_lookupCollection;
    }

    public function getProduct()
    {      
        return $this->hasProduct()
                ? $this->getData('product')
                : Mage::registry('product');
        
    }
   
}