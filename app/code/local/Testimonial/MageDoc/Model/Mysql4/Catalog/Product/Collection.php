<?php

class Testimonial_MageDoc_Model_Mysql4_Catalog_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->reset(Zend_Db_Select::GROUP);

        $countSelect->columns("COUNT(DISTINCT e.{$this->getResource()->getIdFieldName()})");
        
        return $countSelect;
    }
    
    public function setOrder($attribute, $dir = self::SORT_ORDER_ASC)
    {
        if (isset($this->_staticFields[$attribute])) {
            $this->getSelect()->order("e.{$attribute} {$dir}");
            return $this;
        }
        $attrInstance = $this->getEntity()->getAttribute($attribute);
        if(!$attrInstance){
            $this->getSelect()->order($attribute . " " . $dir);
            return $this;
        }
        return parent::setOrder($attribute, $dir);
    }

    
    
    
}