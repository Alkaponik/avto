<?php

class Testimonial_MageDoc_Model_Source_Supplier extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions($withEmpty = true)
    {
        if (is_null($this->_options)) {
            $collection =  Mage::getResourceModel('magedoc/supplier_collection');
            $collection->getSelect()->order('title');
            $this->_options = $collection->toOptionArray();
        }
        $options = $this->_options;
        if ($withEmpty){
            array_unshift($options, array('value'=>'', 'label'=>Mage::helper('magedoc')->__('--Please Select--')));
        }
        return $options;
    }
    
    public function getOptionArray($withEmpty = false)
    {
        $options = $this->getAllOptions($withEmpty);

        $optionArray = array();
        foreach($options as $option){
            $optionArray[$option['value']] = $option['label'];
        }


        return $optionArray;
    }
    
    /**
     * Retrieve Column(s) for Flat
     *
     * @return array
     */
    public function getFlatColums()
    {
        $columns = array();
        $attribute = $this->getAttribute();
        switch ($attribute->getBackendType()) {
            case 'static':
                $describe = $attribute->_getResource()
                    ->describeTable($attribute->getBackend()->getTable());
                if (!isset($describe[$attribute->getAttributeCode()])) {
                    break;
                }
                $prop = $describe[$attribute->getAttributeCode()];
                $columns[$attribute->getAttributeCode()] = array(
                    'type'      => $prop['DATA_TYPE'] . ($prop['LENGTH'] ? "({$prop['LENGTH']})" : ""),
                    'unsigned'  => $prop['UNSIGNED'] ? true: false,
                    'is_null'   => $prop['NULLABLE'],
                    'default'   => $prop['DEFAULT'],
                    'extra'     => null
                );
                break;
            case 'datetime':
                $columns[$attribute->getAttributeCode()] = array(
                    'type'      => 'datetime',
                    'unsigned'  => false,
                    'is_null'   => true,
                    'default'   => null,
                    'extra'     => null
                );
                break;
            case 'decimal':
                $columns[$attribute->getAttributeCode()] = array(
                    'type'      => 'decimal(12,4)',
                    'unsigned'  => false,
                    'is_null'   => true,
                    'default'   => null,
                    'extra'     => null
                );
                break;
            case 'int':
                $columns[$attribute->getAttributeCode()] = array(
                    'type'      => 'int',
                    'unsigned'  => false,
                    'is_null'   => true,
                    'default'   => null,
                    'extra'     => null
                );
                break;
            case 'text':
                $columns[$attribute->getAttributeCode()] = array(
                    'type'      => 'text',
                    'unsigned'  => false,
                    'is_null'   => true,
                    'default'   => null,
                    'extra'     => null
                );
                break;
            case 'varchar':
                $columns[$attribute->getAttributeCode()] = array(
                    'type'      => 'varchar(255)',
                    'unsigned'  => false,
                    'is_null'   => true,
                    'default'   => null,
                    'extra'     => null
                );
                break;
        }
        return $columns;
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param int $store
     * @return Varien_Db_Select
     */
    public function getFlatUpdateSelect($store) {
        if (is_null($store)) {            
            return $this->getAttribute();
        }

        if ($this->getAttribute()->getBackendType() == 'static') {
            return null;
        }

        return $this->getAttribute()->getResource()->getFlatUpdateSelect($this->getAttribute(), $store);
    }
}
