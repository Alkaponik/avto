<?php

abstract class Testimonial_MageDoc_Model_Mysql4_Collection_Abstract extends Testimonial_MageDoc_Model_Resource_Collection_Abstract
{
    public function load($printQuery = false, $logQuery = false)
    {
        
        if ($this->isLoaded()) {
            return $this;
        }

        $this->_beforeLoad();

        $this->_renderFilters()
             ->_renderOrders()
             ->_renderLimit();

        $this->printLogQuery($printQuery, $logQuery);
        $data = $this->getData();
        $this->resetData();

        if (is_array($data)) {
            
            foreach ($data as $row) {
                $item = $this->getNewEmptyItem();
                if ($this->getIdFieldName()) {
                    
                    $item->setIdFieldName($this->getIdFieldName());
                }
                $item->addData(array_change_key_case($row, CASE_LOWER));
                $this->addItem($item);
            }
        }

        $this->_setIsLoaded();
        $this->_afterLoad();
        return $this;
    }

    public function getLastTexAlias()
    {
        return $this->_lastTexAlias;
    }

    public function setLastTexAlias($alias)
    {
        $this->_lastTexAlias = $alias;
    }

    
    public function joinDesignation($collection = null, $joinTableAlias = 'main_table',
            $joinTableColumn = 'ART_COMPLETE_DES_ID', $columns = '', $desTextAlias = null, $joinTemplates = false)
    {
        if(is_null($collection)){
            $collection = $this;
        }
        $select = $collection->getSelect();
        $this->getResource()->joinDesignation($select, $joinTableAlias,
            $joinTableColumn, $columns, $desTextAlias, $joinTemplates);

        foreach ($this->getResource()->getLastDesignationColumns() as $column => $expression){
            $collection->addFilterToMap($column, $expression);
        }

        return $this;
    }

    public function joinCountryDesignation($collection = null, $joinTableAlias = 'main_table',
            $joinTableColumn = 'TYP_CDS_ID', $columns = '', $desTextAlias = null)
    {
        if(is_null($collection)){
            $collection = $this;
        }
        $select = $collection->getSelect();
        $this->getResource()->joinCountryDesignation($select, $joinTableAlias,
            $joinTableColumn, $columns, $desTextAlias);
               
        return $this;
    }
    
    public function getLngId()
    {
        return $this->getResource()->getLngId();
    }

    public function getStatement()
    {
        if (null === $this->_fetchStmt) {
            $this->_fetchStmt = $this->getConnection()
                ->query($this->getSelect());
        }
        return $this->_fetchStmt;
    }

    public function clear()
    {
        parent::clear();
        $this->_fetchStmt = null;
        return $this;
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
}
