<?php

class Testimonial_MageDoc_Model_Mysql4_Import_Retailer_Data extends Testimonial_MageDoc_Model_Resource_Abstract
{
    protected function _construct()
    {
        $this->_isPkAutoIncrement = false;
        $this->_init('magedoc/import_retailer_data', 'data_id');
    }    
    
    public function loadByAttributeSet($object, $attributes = array())
    {
        $read = $this->_getReadAdapter();
        $fields = array();
        if ($read && !empty($attributes)) {
            $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable());
            foreach($attributes as $name => $value){
                $field = $this->_getReadAdapter()
                    ->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), $name));
                $select->where($field . '=?', $value);
            }
            $data = $read->fetchRow($select);
            if ($data) {
                $object->setData($data);
            }
        }

        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
    }
}
