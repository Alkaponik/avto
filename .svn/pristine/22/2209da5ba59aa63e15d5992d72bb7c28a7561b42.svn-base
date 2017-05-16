<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Articles
 *
 * @author Oleg
 */
class Testimonial_MageDoc_Model_Mysql4_Tecdoc_Article extends Testimonial_MageDoc_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('magedoc/tecdoc_article', 'ART_ID');
    }  
    
        
    public function joinGraphics($select, $joinAlias = 'main_table', $columns = null)
    {
        if (is_null($columns)){
            $columns = array(
                'image_path' => new Zend_Db_Expr("GROUP_CONCAT(CONCAT(
                    GRA_TAB_NR, '/', GRA_GRD_ID, '.', IF(LOWER(
                    td_docType.DOC_EXTENSION)='jp2', 'jpg', LOWER(
                    td_docType.DOC_EXTENSION))))"),
                'image_sort' => 
                    new Zend_Db_Expr('GROUP_CONCAT(td_linkGraArt.LGA_SORT)')
                );
        }
        
        $select 
            ->joinLeft(array('td_linkGraArt' => $this->getTable('magedoc/tecdoc_linkGraArt')),
                "td_linkGraArt.LGA_ART_ID = {$joinAlias}.ART_ID", '')
            ->joinLeft(array('td_graphic' => $this->getTable('magedoc/tecdoc_graphic')),
                "td_graphic.GRA_ID = td_linkGraArt.LGA_GRA_ID AND td_graphic.GRA_LNG_ID = 255 AND td_graphic.GRA_DOC_TYPE <> 2", 
                '')
            ->joinLeft(array('td_docType' => $this->getTable('magedoc/tecdoc_docType')),
                'td_docType.DOC_TYPE = td_graphic.GRA_DOC_TYPE', 
                $columns);
                                     
        return $this;
    }

    protected function _prepareFullSelect($select)
    {
        $mainTable = $this->getTable('magedoc/tecdoc_article', false);
        $this->joinGraphics($select, $mainTable);
        $this->joinName($select, $mainTable);
        return $select;
    }

    public function joinName($select, $mainTable = 'main_table', $fieldName = 'name')
    {
        $this->joinDesignation($select, $mainTable, 'ART_COMPLETE_DES_ID', '', null, true);
        $this->joinDesignation($select, $mainTable, 'ART_DES_ID');
        $supplierTableAlias = 'td_supplier'.$fieldName;
        $select->joinLeft(
            array($supplierTableAlias => $this->getTable('magedoc/tecdoc_supplier')),
            "{$mainTable}.ART_SUP_ID = {$supplierTableAlias}.SUP_ID",
        array(
            'supplier'      => "{$supplierTableAlias}.SUP_ID",
            'sku'           => new Zend_Db_Expr("CONCAT({$supplierTableAlias}.SUP_BRAND,
                                    '-', REPLACE({$mainTable}.ART_ARTICLE_NR, ' ', ''))"),
            'code'          => "{$mainTable}.ART_ARTICLE_NR",
            $fieldName      => $this->getNameFieldExpression($mainTable, $supplierTableAlias),
        ));
    }

    public function getNameFieldExpression($mainTable = 'main_table', $supplierTableAlias = 'td_supplier')
    {
        return new Zend_Db_Expr("
                            CONCAT(
                                IF(des_text_template.text IS NULL,
                                    CONCAT(td_desText.TEX_TEXT, ' ', {$supplierTableAlias}.SUP_BRAND,' ', {$mainTable}.ART_ARTICLE_NR),
                                    IF(LOCATE('%s', des_text_template.text),
                                        REPLACE(des_text_template.text,
                                            '%s',
                                            CONCAT({$supplierTableAlias}.SUP_BRAND,' ',{$mainTable}.ART_ARTICLE_NR)
                                        ),
                                        CONCAT(des_text_template.text, ' ', {$supplierTableAlias}.SUP_BRAND,' ',{$mainTable}.ART_ARTICLE_NR)
                                    )
                                ),
                                IF(td_desText1.TEX_TEXT IS NOT NULL,
                                    CONCAT(' ',td_desText1.TEX_TEXT),
                                    ''
                                )
                            )");
    }
}
