<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Collection
 *
 * @author Oleg
 */
class Testimonial_MageDoc_Model_Mysql4_Tecdoc_ArtLookup_Collection extends Testimonial_MageDoc_Model_Mysql4_Collection_Abstract
{

    protected function _construct() 
    {
        $this->_init('magedoc/tecdoc_artLookup');
    }    

    public function getLookupCollection($product)
    {
        if (!$product->getTdArtId()){
            $this->_setIsLoaded(true);
            return $this;
        }
        $resource = Mage::getModel('catalog/product')->getResource();
        $attributeName = 'url_path';
        $attribute = $resource->getAttribute($attributeName);
        $attributeTable = $attribute->getBackendTable();
        $alias = 'at_'.$attributeName;

        $this->getSelect()
                ->joinInner(array('td_artLookup'=> $this->getTable('magedoc/tecdoc_artLookup')),
                    "td_artLookup.ARL_SEARCH_NUMBER = main_table.ARL_SEARCH_NUMBER
                    AND td_artLookup.ARL_KIND = 4
                    AND td_artLookup.ARL_BRA_ID = {$product->getSupplier()}",
                    array('number' => 'td_article.ART_ARTICLE_NR',
                            'art_id' => 'td_article.ART_ID'))
                ->joinInner(array('td_article'=> $this->getTable('magedoc/tecdoc_article')),
                    "td_article.ART_ID = td_artLookup.ARL_ART_ID",
                    array('number' => 'td_article.ART_ARTICLE_NR',
                        'art_id' => 'td_article.ART_ID'))
                ->joinInner(array('td_supplier'=> $this->getTable('magedoc/tecdoc_supplier')),
                        'td_supplier.SUP_ID = td_article.ART_SUP_ID',
                        array('brand' => 'td_supplier.SUP_BRAND'))
                ->joinLeft(array('catalog_product' => $this->getTable('catalog/product')),
                    'catalog_product.td_art_id = td_article.ART_ID', array('entity_id'))
                ->where("main_table.ARL_ART_ID = {$product->getTdArtId()}
                        AND main_table.ARL_KIND IN (1,2)
                        AND main_table.ARL_BRA_ID = 0")
                ->group('td_article.ART_ID')
                ->limit('100');
                $this->_setIdFieldName('art_id');

        $this->getSelect()->joinLeft(array($alias => $attributeTable),
            "{$alias}.entity_id = catalog_product.entity_id
                         AND {$alias}.attribute_id = {$attribute->getId()}
                         AND {$alias}.store_id = {$product->getStoreId()}",
            array ($attributeName => "$alias.value")
        );

         return $this;
    }
    
}


