<?php

class Testimonial_MageDoc_Model_Quote_Inquiry extends Mage_Sales_Model_Quote_Item_Abstract
{
    protected $_quote = null;

    protected function _construct()
    {
        $this->_init('magedoc/quote_inquiry');
        
    }
        
    protected function _beforeSave()
    {
        parent::_beforeSave();
        if ($this->getQuote()) {
            $this->setQuoteId($this->getQuote()->getId());
        }
        if($this->getVehicle()){
            if($this->getVehicle()->getId()){
                $this->setQuoteVehicleId($this->getVehicle()->getId());
            }
        }
        if ($this->getBaseCost() === null){
            $this->setBaseCost($this->getCost());
        }
        if ($this->dataHasChangedFor('article_id') && $this->getArticleId()){
            $article = Mage::getModel('magedoc/tecdoc_article');
            $article->isPartialLoad(false);
            $this->setName($article->load($this->getArticleId())->getName());
        }
        return $this;
    }

    public function setQuote(Mage_Sales_Model_Quote $quote)
    {
        $this->_quote = $quote;
        $this->setQuoteId($quote->getId());
        return $this;
    }

    public function getQuote()
    {
        return $this->_quote;
    }

    public function getOptionByCode($code)
    {
        return null;
    }

    public function getProduct()
    {
        return Mage::getSingleton('catalog/product');
    }

    public function getBaseCalculationCost()
    {
        return $this->getBaseCost();
    }
}
