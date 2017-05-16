<?php

class Testimonial_MageDoc_Model_Resource_Catalog_Layer_Filter_Price extends Mage_Catalog_Model_Resource_Layer_Filter_Price
{
    /**
     * Retrieve clean select with joined price index table
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @return Varien_Db_Select
     */
    protected function _getSelect($filter)
    {
        $collection = $filter->getLayer()->getProductCollection();
        $collection->addPriceData($filter->getCustomerGroupId(), $filter->getWebsiteId());

        if (!is_null($collection->getCatalogPreparedSelect())) {
            $select = clone $collection->getCatalogPreparedSelect();
        } else {
            $select = clone $collection->getSelect();
        }

        // reset columns, order and limitation conditions
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        // remove join with main table
        $fromPart = $select->getPart(Zend_Db_Select::FROM);
        if (!isset($fromPart[Mage_Catalog_Model_Resource_Product_Collection::INDEX_TABLE_ALIAS])
            || !isset($fromPart[Mage_Catalog_Model_Resource_Product_Collection::MAIN_TABLE_ALIAS])
        ) {
            return $select;
        }

        // processing FROM part
        $priceIndexJoinPart = $fromPart[Mage_Catalog_Model_Resource_Product_Collection::INDEX_TABLE_ALIAS];
        $priceIndexJoinConditions = explode('AND', $priceIndexJoinPart['joinCondition']);
        $priceIndexJoinPart['joinType'] = Zend_Db_Select::FROM;
        $priceIndexJoinPart['joinCondition'] = null;
        $fromPart['p'] = $fromPart[Mage_Catalog_Model_Resource_Product_Collection::MAIN_TABLE_ALIAS];
        $fromPart['p']['joinCondition'] = 'e.entity_id = p.entity_id';
        $fromPart[Mage_Catalog_Model_Resource_Product_Collection::MAIN_TABLE_ALIAS] = $priceIndexJoinPart;
        unset($fromPart[Mage_Catalog_Model_Resource_Product_Collection::INDEX_TABLE_ALIAS]);
        $select->setPart(Zend_Db_Select::FROM, $fromPart);
        foreach ($fromPart as $key => $fromJoinItem) {
            $fromPart[$key]['joinCondition'] = $this->_replaceTableAlias($fromJoinItem['joinCondition']);
        }
        $select->setPart(Zend_Db_Select::FROM, $fromPart);

        // processing WHERE part
        $wherePart = $select->getPart(Zend_Db_Select::WHERE);
        foreach ($wherePart as $key => $wherePartItem) {
            $wherePartItem = $this->_replaceEntityTableAlias($wherePartItem);
            $wherePart[$key] = $this->_replaceTableAlias($wherePartItem);
        }
        $select->setPart(Zend_Db_Select::WHERE, $wherePart);
        $excludeJoinPart = Mage_Catalog_Model_Resource_Product_Collection::MAIN_TABLE_ALIAS . '.entity_id';
        foreach ($priceIndexJoinConditions as $condition) {
            if (strpos($condition, $excludeJoinPart) !== false) {
                continue;
            }
            $select->where($this->_replaceTableAlias($condition));
        }
        $select->where($this->_getPriceExpression($filter, $select) . ' IS NOT NULL');

        return $select;
    }

    /**
     * Replace table alias in condition string
     *
     * @param string|null $conditionString
     * @return string|null
     */
    protected function _replaceEntityTableAlias($conditionString)
    {
        if (is_null($conditionString)) {
            return null;
        }
        $adapter = $this->_getReadAdapter();
        $oldAlias = array(
            Mage_Catalog_Model_Resource_Product_Collection::MAIN_TABLE_ALIAS . '.',
            $adapter->quoteIdentifier(Mage_Catalog_Model_Resource_Product_Collection::MAIN_TABLE_ALIAS) . '.',
        );
        $newAlias = array(
            'p' . '.',
            $adapter->quoteIdentifier('p') . '.',
        );
        return str_replace($oldAlias, $newAlias, $conditionString);
    }
}
