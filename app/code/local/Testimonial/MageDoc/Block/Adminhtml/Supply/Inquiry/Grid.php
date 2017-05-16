<?php

class Testimonial_MageDoc_Block_Adminhtml_Supply_Inquiry_Grid 
    extends Testimonial_MageDoc_Block_Adminhtml_Widget_Grid_Supply_Abstract
{
    protected $_collectionModelName = 'magedoc/order_inquiry_collection';

    public function __construct() 
    {
        parent::__construct();
        $this->setId('inquiry');
    }

    protected function _prepareCollectionAfter($collection)
    {
        $nameExpression = new Zend_Db_Expr(
            'IF (main_table.article_id IS NULL,
                    CONCAT (IFNULL(main_table.name, \'\'), \' \', IFNULL(main_table.supplier, \'\'), \' \', IFNULL(main_table.code, \'\')),
                    main_table.name)');
        $rowTotalExpression = new Zend_Db_Expr('main_table.cost * main_table.qty_ordered');
        $collection->getSelect()
            ->joinLeft(array('artLookUp' => Mage::getResourceSingleton('magedoc/tecdoc_artLookup')->getTable('magedoc/tecdoc_artLookup')),
                "main_table.article_id = artLookUp.ARL_ART_ID AND artLookUp.ARL_KIND IN ({$this->_getArlKind()})",
                array('ean' => new Zend_Db_Expr('GROUP_CONCAT(artLookUp.ARL_SEARCH_NUMBER SEPARATOR ", ")')))
            ->columns(array(
            'art_id'    =>  'main_table.article_id',
            'name'      =>  $nameExpression,
            'row_total' =>  $rowTotalExpression
        ))
        ->group('inquiry_id');
        $collection->addFilterToMap('name', $nameExpression);
        return $collection;
    }

    protected function _prepareMassaction()
    {
        return $this;
        $this->setMassactionIdField('inquiry_id');
        $this->getMassactionBlock()->setFormFieldName('inquiry_id');
    }
}