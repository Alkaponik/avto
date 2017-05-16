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
class Testimonial_MageDoc_Model_Mysql4_Tecdoc_ArtLookup_Original_Collection extends Testimonial_MageDoc_Model_Mysql4_Tecdoc_ArtLookup_Collection
{

    public function getOrigianlLookupCollection($product)
    {
        if (!$product->getTdArtId()){
            $this->_setIsLoaded(true);
            return $this;
        }
         $this->getSelect()
                ->joinInner(array('td_article'=> $this->getTable('magedoc/tecdoc_article')),
                        'main_table.ARL_ART_ID = td_article.ART_ID', '')
                ->joinInner(array('td_brand'=> $this->getTable('magedoc/tecdoc_brand')),
                        'td_brand.BRA_ID = main_table.ARL_BRA_ID', '')
                ->where("td_article.ART_ID = {$product->getTdArtId()}  AND main_table.ARL_KIND = 3")
                ->group('main_table.ARL_BRA_ID')
                ->limit('100')
                ->columns(array('brand' => 'td_brand.BRA_BRAND',
                    'number' => new Zend_Db_Expr("
                        GROUP_CONCAT(main_table.ARL_DISPLAY_NR SEPARATOR ', ')")));
         $this->_setIdFieldName('td_article.ART_ID');
          
         return $this;
    }
    
}


