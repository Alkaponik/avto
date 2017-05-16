<?php

abstract class Testimonial_MageDoc_Model_Resource_Collection_Abstract extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected $_joins = array();

    public function setIdFieldName($fieldName)
    {
        $this->_setIdFieldName($fieldName);
        return $this;
    }

    public function setJoin($joinAlias, $collection)
    {
        $this->_joins[$joinAlias] = $collection;
        return $this;
    }

    public function hasJoin($joinAlias)
    {
        return isset($this->_joins[$joinAlias]);
    }

    public function getJoin($joinAlias)
    {
        return isset($this->_joins[$joinAlias])
            ? $this->_joins[$joinAlias]
            : null;
    }

    public function setIsLoaded($flag = true)
    {
        $this->_setIsLoaded($flag);
    }

    public function renderAll()
    {
        $this->_renderFilters()
            ->_renderOrders()
            ->_renderLimit();
    }
}
