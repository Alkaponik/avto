<?php

class Testimonial_FlatCatalog_Model_Resource_Layer_Filter_Attribute extends Mage_Catalog_Model_Resource_Layer_Filter_Attribute
{
    protected $_directoryOfferLinkFieldMap = array(
        'manufacturer' => 'supplier_id',
        'generic_article' => 'generic_article_id',
    );
    /**
     * Apply attribute filter to product collection
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @param int $value
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute
     */
    public function applyFilterToCollection($filter, $value)
    {
        if ($filter->getAttributeModel()->getBackendType() == 'static'){
            return $this->applyStaticFilterToCollection($filter, $value);
        }else {
            return parent::applyFilterToCollection($filter, $value);
        }
        return $this;
    }
        /**
     * Retrieve array with products counts per attribute option
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @return array
     */
    public function getCount($filter)
    {
        if (!isset($this->_directoryOfferLinkFieldMap[$filter->getAttributeModel()->getAttributeCode()])){
            return parent::getCount($filter);
        }
        $field = $this->_directoryOfferLinkFieldMap[$filter->getAttributeModel()->getAttributeCode()];
        // clone select from collection with filters
        $select = clone $filter->getLayer()->getProductCollection()->getSelect();
        // reset columns, order and limitation conditions
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::GROUP);
        $select->columns(array("dol.{$field}", 'count' => new Zend_Db_Expr("COUNT(main_table.data_id)")));
        $select->group("dol.{$field}");
        $connection = $this->_getReadAdapter();

        //print_r((string)$select);die;
        return $connection->fetchPairs($select);
    }

    public function applyStaticFilterToCollection($filter, $value)
    {
        $collection = $filter->getLayer()->getProductCollection();
        $attribute  = $filter->getAttributeModel();
        $condition = $value == 'other' ? array('null' => true) : $value;
        $collection->addAttributeToFilter($attribute->getAttributeCode(), $condition);

        return $this;
    }
}