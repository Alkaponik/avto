<?php

class Testimonial_MageDoc_Model_Source_Type_Article extends Testimonial_MageDoc_Model_Source_Abstract
{
    protected $_typeId;
    protected $_strId;
    protected $_supplierId;
    protected $_searchNumber;

    public function getCollectionArray()
    {
        if (empty($this->_collectionArray)) {
            if ($this->getStrId() && $this->getSupplierId()) {
                $collection = Mage::getResourceModel('magedoc/tecdoc_linkGAStr_collection');
                $articleTable = 'td_article';
                if ($this->getTypeId() !== null) {
                    $collection->getSelect()
                        ->joinInner(array('td_linkLaTyp' => $collection->getTable('magedoc/tecdoc_linkLaTyp')),
                        "td_linkLaTyp.LAT_TYP_ID = {$this->getTypeId()} AND td_linkLaTyp.LAT_GA_ID = main_table.LGS_GA_ID
                            AND td_linkLaTyp.LAT_SUP_ID = {$this->getSupplierId()}",
                        array())
                        ->joinInner(array('td_linkArt' => $collection->getTable('magedoc/tecdoc_linkArt')),
                        "td_linkArt.LA_ID = td_linkLaTyp.LAT_LA_ID", array())
                        ->joinInner(array("{$articleTable}" => $collection->getTable('magedoc/tecdoc_article')),
                        "{$articleTable}.ART_ID = td_linkArt.LA_ART_ID",
                        array('art_id' => "{$articleTable}.ART_ID",
                            'art_article_nr' => "{$articleTable}.ART_ARTICLE_NR",
                            'art_article_nr_normalized' => new Zend_Db_Expr("REPLACE({$articleTable}.ART_ARTICLE_NR, ' ', '')")))
                        ->where("main_table.LGS_STR_ID = {$this->getStrId()}");
                } else {
                    $collection->getSelect()
                        ->joinInner(array('td_linkArtGA' => $collection->getTable('magedoc/tecdoc_linkArtGA')),
                        "td_linkArtGA.LAG_GA_ID = {$articleTable}.LGS_GA_ID",
                        array())
                        ->joinInner(array($articleTable => $collection->getTable('magedoc/tecdoc_article')),
                        "{$articleTable}.ART_ID = td_linkArtGA.LAG_ART_ID AND {$articleTable}.ART_SUP_ID = {$this->getSupplierId()}",
                        array('art_id' => "{$articleTable}.ART_ID",
                            'art_article_nr' => "{$articleTable}.ART_ARTICLE_NR",
                            'art_article_nr_normalized' => new Zend_Db_Expr("REPLACE({$articleTable}.ART_ARTICLE_NR, ' ', '')")))
                        ->where("main_table.LGS_STR_ID = {$this->getStrId()}");
                }
            }elseif ($this->getSearchNumber()){
                $numberNormalized = preg_replace('/[^a-zA-Z0-9]*/', '', $this->getSearchNumber());
                $articleTable = 'main_table';
                $articleCollection = $collection = Mage::getResourceModel('magedoc/tecdoc_article_collection');
                $collection->getSelect()->joinInner(array('td_artLookup' => $collection->getTable('magedoc/tecdoc_artLookup')),
                        "td_artLookup.ARL_ART_ID = main_table.ART_ID",
                        array('art_id' => "{$articleTable}.ART_ID",
                            'art_article_nr' => "{$articleTable}.ART_ARTICLE_NR",
                            'art_article_nr_normalized' => new Zend_Db_Expr("REPLACE({$articleTable}.ART_ARTICLE_NR, ' ', '')")))
                        ->where("td_artLookup.ARL_SEARCH_NUMBER = '{$numberNormalized}' AND td_artLookup.ARL_KIND IN (1,2,4)");

                $collection->getSelect()->joinInner(array('td_supplier' => $collection->getTable('magedoc/tecdoc_supplier')),
                        "{$articleTable}.ART_SUP_ID = td_supplier.SUP_ID",
                        array('sup_brand' => 'td_supplier.SUP_BRAND'));

                $collection->getSelect()->group('main_table.ART_ID');
                //print_r((string)$collection->getSelect());die;
            }
            if (isset($collection)) {
                $productTable = Mage::getResourceSingleton('catalog/product')->getEntityTable();
                $collection->joinDesignation($collection, $articleTable, 'ART_COMPLETE_DES_ID', 'name', null, true);

                $collection->getSelect()->joinLeft(array('td_artInfo' => $collection->getTable('magedoc/tecdoc_articleInfo')),
                    "td_artInfo.AIN_ART_ID = {$articleTable}.ART_ID", array())
                    ->joinLeft(array('td_textModule' => $collection->getTable('magedoc/tecdoc_textModule')),
                    'td_textModule.TMO_ID = td_artInfo.AIN_TMO_ID', array())
                    ->joinLeft(array('td_textMT' => $collection->getTable('magedoc/tecdoc_textModuleText')),
                    'td_textMT.TMT_ID = td_textModule.TMO_TMT_ID', array('additional_info' => 'TMT_TEXT'))
                    ->joinLeft(array('catalog_product_entity' => $productTable),
                    "catalog_product_entity.td_art_id = {$articleTable}.ART_ID",
                    array('product_id' => 'catalog_product_entity.entity_id'));
                if (!isset($articleCollection)){
                    $articleCollection = Mage::getResourceModel('magedoc/tecdoc_article_collection');
                }
                $articleCollection->joinAttribute('cost', $collection);
                $articleCollection->joinAttribute('price', $collection);
                $articleCollection->joinAttribute('retailer_id', $collection);

                while ($item = $collection->fetchItem()){
                    $this->_collectionArray[] = array(
                        'label'     => $this->_getFullItemName($item),
                        'code'      => $item->getArtArticleNrNormalized(),
                        'sup_id'    => $item->getArtSupId(),
                        'sup_brand' => $item->getSupBrand(),
                        'product_id'=> $item->getProductId(),
                        'cost'      => $item->getCost(),
                        'price'     => $item->getPrice(),
                        'retailer_id'=> $item->getRetailerId(),
                        'value' => $item->getArtId());
                }
            }
        }
        return $this->_collectionArray;
    }
    
    public function getTypeId()
    {
        if(!isset($this->_typeId)){
            return null;
        }
        return $this->_typeId;
    }

    public function getStrId()
    {
        if(!isset($this->_strId)){
            return null;
        }
        return $this->_strId;
    }

    public function setStrId($strId)
    {
        $this->_strId = $strId;
        return $this;
    }

    public function setTypeId($typeId)
    {
        $this->_typeId = $typeId;
        return $this;
    }
    
    public function getSupplierId()
    {
        if(!isset($this->_supplierId)){
            return null;
        }
        return $this->_supplierId;
    }

    public function setSupplierId($supId)
    {
        $this->_supplierId = $supId;
        return $this;
    }

    public function getSearchNumber()
    {
        if(!isset($this->_searchNumber)){
            return null;
        }
        return $this->_searchNumber;
    }

    public function setSearchNumber($searchNumber)
    {
        $this->_searchNumber = $searchNumber;
        return $this;
    }

    protected function _getFullItemName($item)
    {
        $template = $item->getNameTemplate()
            ? $item->getNameTemplate()
            : $item->getName();
        if (strpos($template, '%s') === false){
            $template .= ' %s';
        }
        $name = str_replace('%s', $item->getSupBrand() . ' ' . $item->getArtArticleNr(), $template)
            . ' ' . $item->getAdditionalInfo();

        return $name;
    }
}
