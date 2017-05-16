<?php

class Testimonial_MageDoc_Model_Mysql4_Order_Item_Collection 
    extends Mage_Sales_Model_Resource_Order_Item_Collection
{
    /**
     * Get SQL for get record count
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);

        $groupPart = $countSelect->getPart(Zend_Db_Select::GROUP);
        if (count($groupPart)){
            $countSelect->columns(new Zend_Db_Expr('1'));
            $select = $this->getConnection()->select()
                ->from($countSelect, new Zend_Db_Expr('COUNT(*)'));
        }else{
            $countSelect->columns('COUNT(*)');
            $select = $countSelect;
        }

        return $select;
    }

    public function _getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->reset(Zend_Db_Select::GROUP);

        $countSelect->columns("COUNT(DISTINCT {$this->getResource()->getIdFieldName()})");

        return $countSelect;
    }

    public function setIdFieldName($idFieldName)
    {
        return $this->_setIdFieldName($idFieldName);
    }

    /**
     * Add field having filter to collection
     *
     * @see self::_getConditionSql for $condition
     *
     * @param   string|array $field
     * @param   null|string|array $condition
     *
     * @return  Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addFieldToHavingFilter($field, $condition = null)
    {
        if (!is_array($field)) {
            $resultCondition = $this->_translateCondition($field, $condition);
        } else {
            $conditions = array();
            foreach ($field as $key => $currField) {
                $conditions[] = $this->_translateCondition(
                    $currField,
                    isset($condition[$key]) ? $condition[$key] : null
                );
            }

            $resultCondition = '(' . join(') ' . Zend_Db_Select::SQL_OR . ' (', $conditions) . ')';
        }

        $this->_select->having($resultCondition);

        return $this;
    }
}