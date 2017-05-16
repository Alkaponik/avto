<?php

abstract class Testimonial_MageDoc_Model_Resource_Abstract extends Mage_Core_Model_Resource_Db_Abstract
{
    public function setIdFieldName($idFieldName)
    {
        $this->_idFieldName = $idFieldName;
        return $this;;
    }

    public function massUpdate($data, $fields = array())
    {
        return $this->_getWriteAdapter()->insertOnDuplicate($this->getMainTable(), $data, $fields);
    }
}