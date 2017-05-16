<?php

class Phoenix_LayeredNav_Model_Mysql4_Catalog_Layer_Filter_Attribute extends Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute
{
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
        }elseif (!Mage::helper('phoenix_layerednav')->isMultipleSelectFilter($filter)){
            return parent::applyFilterToCollection($filter, $value);
        }
        $collection = $filter->getLayer()->getProductCollection();
        $attribute  = $filter->getAttributeModel();
        $connection = $this->_getReadAdapter();
        $tableAlias = $attribute->getAttributeCode() . '_idx';
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId()),
            $connection->quoteInto("{$tableAlias}.value IN (?)", $value)
        );

        $collection->getSelect()->distinct(true);
        $collection->getSelect()->join(
            array($tableAlias => $this->getMainTable()),
            join(' AND ', $conditions),
            array()
        );

        return $this;
    }

    public function applyStaticFilterToCollection($filter, $value)
    {
        $collection = $filter->getLayer()->getProductCollection();
        $attribute  = $filter->getAttributeModel();
        $condition = Mage::helper('phoenix_layerednav')->isMultipleSelectFilter($filter)
                ? array('in' => !is_array($value)
                    ? explode(',', $value)
                    : $value)
                : $value;
        $collection->addAttributeToFilter($attribute->getAttributeCode(), $condition);

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
        if ($filter->getAttributeModel()->getBackendType() == 'static'){
            return $this->getStaticFilterCount($filter);
        }elseif (!Mage::helper('phoenix_layerednav')->isMultipleSelectFilter($filter)){
            return parent::getCount($filter);
        }
        
        // clone select from collection with filters
        $select = clone $filter->getLayer()->getProductCollection()->getSelect();
        // reset columns, order and limitation conditions
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        $connection = $this->_getReadAdapter();
        $attribute  = $filter->getAttributeModel();
        $tableAlias = $attribute->getAttributeCode() . '_idx';
        if (Mage::helper('phoenix_layerednav')->isMultipleSelectFilter($filter)){
            $from = $select->getPart(Zend_Db_Select::FROM);
            if (isset($from[$tableAlias])){
                unset($from[$tableAlias]);
            }
            $select->setPart(Zend_Db_Select::FROM, $from);
        }

        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $filter->getStoreId()),
        );

        $select
            ->join(
                array($tableAlias => $this->getMainTable()),
                join(' AND ', $conditions),
                array('value', 'count' => "COUNT({$tableAlias}.entity_id)"))
            ->group("{$tableAlias}.value");

        return $connection->fetchPairs($select);
    }

    public function getStaticFilterCount($filter)
    {
        // clone select from collection with filters
        $select = clone $filter->getLayer()->getProductCollection()->getSelect();
        // reset columns, order and limitation conditions
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        $connection = $this->_getReadAdapter();
        $attribute  = $filter->getAttributeModel();
        $where = $select->getPart(Zend_Db_Select::WHERE);
        foreach ($where as $key => $condition){
            if (strpos($condition, "`{$attribute->getAttributeCode()}`") !== false){
                unset($where[$key]);
            }
        }
        $select->setPart(Zend_Db_Select::WHERE, $where);
        $select
            ->columns(array(
                'value' => "e.{$attribute->getAttributeCode()}",
                'count' => new Zend_Db_Expr("COUNT(e.entity_id)")))
            ->where("e.{$attribute->getAttributeCode()} IS NOT NULL")
            ->group("e.{$attribute->getAttributeCode()}");

        return $connection->fetchPairs($select);
    }
}