<?php

class Testimonial_MageDoc_Model_Import_Retailer_Data extends Mage_Core_Model_Abstract 
{
    protected function _construct()
    {
        $this->_init('magedoc/import_retailer_data');
    }   
    
    protected function _beforeSave() 
    {
        if($this->getOriginalData('data_id') === null){
            if($this->getCode() && $this->getSupplierId()){
                $code = Mage::helper('magedoc')->normalizeCode($this->getCode() );
                $article = Mage::getModel('magedoc/tecdoc_article');
                $articleSelect = $article->getResource()->getReadConnection()->select()
                ->from(array('main_table' => $article->getResource()->getMainTable()))
                ->joinLeft(array('catalog_product' => 
                            $this->getResource()->getTable('catalog/product')),
                    'catalog_product.td_art_id = main_table.ART_ID',
                        array('product_id' => 'catalog_product.entity_id'))
                ->joinInner(array('td_article_normalized' =>
                            $this->getResource()->getTable('magedoc/tecdoc_articleNormalized')),
                        'main_table.ART_ID  = td_article_normalized.ARN_ART_ID',
                        array())
                ->where("td_article_normalized.ARN_SUP_ID = '{$this->getSupplierId()}'
                    AND td_article_normalized.ARN_ARTICLE_NR_NORMALIZED = '{$code}'");
                $data = $article->getResource()->getReadConnection()->fetchRow($articleSelect);
                if($data['ART_ID']){
                    $this->setTdArtId($data['ART_ID']);
                }
                if($data['product_id']){
                    $this->setProductId($data['product_id']);
                }
            }
        }
        
        parent::_beforeSave();
    }
    
    public function loadByAttributeSet($attributes = array())
    {
        $this->_getResource()->loadByAttributeSet($this, $attributes);
        $this->_afterLoad();
        $this->setOrigData();
        $this->_hasDataChanges = false;
        return $this;

    }
    
}


