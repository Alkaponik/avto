<?php

class Testimonial_MageDoc_Block_Product_Information extends Mage_Core_Block_Template
{
    
    public function getProductInformation()
    {
        $product = $this->getProduct();
        if (!$product->getTdArtId()) {
            return $product;
        }
        $collection = Mage::getResourceModel('magedoc/tecdoc_article_collection');
        $collection->joinDesignation($collection, 'main_table', 'ART_COMPLETE_DES_ID', 'name');
        $collection->addFieldToFilter('main_table.art_id', $product->getTdArtId());
        $collection->getSelect()
            ->joinInner(array('td_supplier' => $collection->getTable('magedoc/tecdoc_supplier')),
                'td_supplier.SUP_ID = main_table.ART_SUP_ID', array('supplier_name' => 'td_supplier.SUP_BRAND'))    
            ->joinLeft(array('td_artInfo' => $collection->getTable('magedoc/tecdoc_articleInfo')),
                'td_artInfo.AIN_ART_ID = main_table.ART_ID', array())
            ->joinLeft(array('td_textModule' => $collection->getTable('magedoc/tecdoc_textModule')),
                'td_textModule.TMO_ID = td_artInfo.AIN_TMO_ID', array())
            ->joinLeft(array('td_textMT' => $collection->getTable('magedoc/tecdoc_textModuleText')),
                'td_textMT.TMT_ID = td_textModule.TMO_TMT_ID', array('additional_info' => 'TMT_TEXT'));

        if ($article = $collection->fetchItem()){
            $product->addData($article->getData());
        }
        return $product;
    }
    
    
    public function getProduct()
    {      
        return $this->hasProduct()
                ? $this->getData('product')
                : Mage::registry('product');
        
    }
    
}


