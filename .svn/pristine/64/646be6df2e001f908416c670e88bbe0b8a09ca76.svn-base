<?php

class Testimonial_MageDoc_Block_Product_Image extends Mage_Core_Block_Template
{
    const DEFAULT_WIDTH = 240;
    const DEFAULT_HEIGHT = 240;
    
    public function getProductImages()
    {
        $product = $this->getProduct();
        $article = Mage::getModel('magedoc/tecdoc_article');
        $article->isPartialLoad(false);
        $article->load($product->getTdArtId());
        return $article->getImagePath()
                ? $article->getImagePath()
                : array();
    }
    
    public function getImageUrl($imagePath)
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) 
                . 'import' . '/'. $imagePath;
    }
    
    public function getProduct()
    {      
        return $this->hasProduct()
                ? $this->getData('product')
                : Mage::registry('product');
    }

    public function getWidth()
    {
        if ($this->hasWidth()){
            return $this->getData('width');
        }
        return self::DEFAULT_WIDTH;
    }

    public function getHeight()
    {
        if ($this->hasHeight()){
            return $this->getData('height');
        }
        return self::DEFAULT_HEIGHT;
    }
    
}


