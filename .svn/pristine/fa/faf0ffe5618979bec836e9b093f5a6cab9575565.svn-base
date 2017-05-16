<?php

class Testimonial_MageDoc_Model_Abstract extends Mage_Core_Model_Abstract
{
    protected $_isPartialLoad = true;

    public function getId()
    {
        $fieldName = strtolower($this->getIdFieldName());
        if ($fieldName) {
            return $this->_getData($fieldName);
        } else {
            return $this->_getData('id');
        }
    }

    public function setId($id)
    {
        if ($this->getIdFieldName()) {
            $this->setData(strtolower($this->getIdFieldName()), $id);
        } else {
            $this->setData('id', $id);
        }
        return $this;
    }
    
    public function setData($key, $value=null)
    {
        $this->_hasDataChanges = true;
        if(is_array($key)) {
            $this->_data = array_change_key_case($key, CASE_LOWER);
            $this->_addFullNames();
        } else {
            $this->_data[$key] = $value;
            if (isset($this->_syncFieldsMap[$key])) {
                $fullFieldName = $this->_syncFieldsMap[$key];
                $this->_data[$fullFieldName] = $value;
            }
        }
        return $this;
    }

    public function isPartialLoad($flag = null)
    {
        $result = $this->_isPartialLoad;
        if ($flag !== null) {
            $this->_isPartialLoad = (bool)$flag;
        }
        return is_null($flag) ? $result : $this;
    }
}