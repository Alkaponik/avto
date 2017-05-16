<?php

class Testimonial_MageDoc_Block_Criteria extends Mage_Core_Block_Template
{
    protected $_completeCriteriaCollection;

    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getCriteriaCollection()
    {
        if(!isset($this->_completeCriteriaCollection)){
            $criteriaCollection = Mage::getResourceModel("magedoc/tecdoc_artCriteria_collection")
                    ->addProductFilter($this->getProduct());
            $criteriaCollection->joinCriteriaDesignation();
            $product = $this->getProduct();
            $articleCollection = Mage::getResourceModel('magedoc/tecdoc_article_collection')
                    ->addProductFilter($product);
            if($this->getData('magedoc_type') !== null){
                $typeId = $this->getData('magedoc_type')->getId();
            }elseIf(Mage::registry('current_magedoc_type_ids') !== null){
                $typeId = Mage::registry('current_magedoc_type_ids');
            }elseif(Mage::getSingleton('core/session')->getTypeIds() !== null){
                $typeId = Mage::getSingleton('core/session')->getTypeIds();
            }
            if(isset($typeId)){
                foreach($articleCollection->getArticleAdditionalCriteria($typeId) as $item){
                    $item->setId('lac_'.$item->getId());
                    $criteriaCollection->addItem($item);
                }
            }
            $this->_completeCriteriaCollection = $criteriaCollection;
        }
        return $this->_completeCriteriaCollection;
    }
    
    public function getProduct()
    {      
        return $this->hasProduct()
                ? $this->getData('product')
                : Mage::registry('product');
        
    }
    
}


