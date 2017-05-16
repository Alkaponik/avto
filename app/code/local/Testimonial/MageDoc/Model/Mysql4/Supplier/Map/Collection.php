<?php

class Testimonial_MageDoc_Model_Mysql4_Supplier_Map_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
            $this->_init('magedoc/supplier_map');
    }

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

