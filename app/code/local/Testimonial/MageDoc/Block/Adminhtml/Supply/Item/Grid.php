<?php

class Testimonial_MageDoc_Block_Adminhtml_Supply_Item_Grid     
    extends Testimonial_MageDoc_Block_Adminhtml_Widget_Grid_Supply_Abstract
{
    protected $_collection;
    protected $_collectionModelName = 'magedoc/order_item_collection';

    public function __construct()
    {
        parent::__construct();
        $this->setId('item');
    }

    protected function _prepareCollectionAfter($collection)
    {
        $rowTotalExpression = new Zend_Db_Expr('main_table.cost * main_table.qty_ordered');
        $collection->getSelect()
            ->joinInner(array('catalog_product' => $collection->getTable('catalog/product')),
                "catalog_product.entity_id = main_table.product_id",
                array('art_id' => 'catalog_product.td_art_id'))
            ->columns(array(
                'row_total' =>  $rowTotalExpression
            ));

        if ($this->getFilterValue('ean')) {
            $collection->getSelect()
                ->joinLeft(array('artLookUp' => Mage::getResourceSingleton('magedoc/tecdoc_artLookup')->getTable('magedoc/tecdoc_artLookup')),
                "catalog_product.td_art_id = artLookUp.ARL_ART_ID
                            AND artLookUp.ARL_KIND IN ({$this->_getArlKind()})",
                array('ean' => new Zend_Db_Expr('GROUP_CONCAT(artLookUp.ARL_SEARCH_NUMBER SEPARATOR ", ")')))
                ->group('item_id');
        }
        return $collection;
    }

    protected function _prepareMassaction()
    {
        return $this;
        $this->setMassactionIdField('item_id');
        $this->getMassactionBlock()->setFormFieldName('item_ids');
        //$this->getMassactionBlock()->setUseSelectAll(false);

        $this->getMassactionBlock()->addItem('pdf_routing_supply', array(
            'label' => Mage::helper('magedoc')->__('Print Routing Sheet'),
            'url' => $this->getUrl('*/*/pdfrouting'),
        ));

        return $this;
    }
}