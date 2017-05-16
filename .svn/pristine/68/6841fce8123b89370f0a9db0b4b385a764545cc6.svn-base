<?php

abstract class Testimonial_MageDoc_Model_Source_Abstract extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    protected $_collectionArray = array();
    protected $_sortOrder = 'asc';

    public function setSortOrder($order)
    {
        $this->_sortOrder = $order;
        return $this;
    }

    public function getSortOrder()
    {
        return $this->_sortOrder;
    }
    
    public function getAllOptions($withEmpty = true)
    {
        if (is_null($this->_options)) {
            $this->_options = $this->getCollectionArray();
            
        }
        $options = $this->_options;
        if ($withEmpty){
            array_unshift($options, array('value'=>'', 'label'=>Mage::helper('magedoc')->__('--Please Select--')));
        }
        return $options;
    }
    
    public function getOptionArray()
    {
        $options = $this->getAllOptions(false);
        $optionArray = array();
        foreach($options as $option){
            $optionArray[$option['value']] = $option['label'];
        }
        return $optionArray;
    }

    public function getCollectionArray()
    {
        return array();
    }

    public function getExtendedOptionArray($fields)
    {
        if (!is_array($fields)){
            $fields = array($fields => $fields);
        }
        $options = $this->getAllOptions(false);
        $optionArray = array();
        $sortOrder = $this->_sortOrder == 'asc' ? 0 : 9999999999;
        foreach($options as $option){
            $optionArray[$option['value']] = array(
                'label' => $option['label'],
                'value' => $option['value']
            );
            foreach ($fields as $field => $index){
                if (is_int($field)){
                    $field = $index;
                }
                if (isset($option[$index])){
                    $optionArray[$option['value']][$field] = $option[$index];
                } elseif ($field == 'sort_order'){
                    $optionArray[$option['value']][$field] = $sortOrder;
                    $sortOrder = $this->_sortOrder == 'asc'
                        ? $sortOrder + 1
                        : $sortOrder - 1;
                }
            }
        }
        return $optionArray;
    }
}
