<?php

class Testimonial_MageDoc_Model_Source_Date extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const START_YEAR = 1970;

    protected $_sortOrder = 'desc';
    
    public function getAllOptions($withEmpty = true)
    {
        if (is_null($this->_options)) {
            $storeId = Mage::app()->getStore()->getId();
            $startYear = Mage::helper('magedoc')->getProductionStartYear($storeId);
            $this->_options = $this->getDateArray($startYear);
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

    public function setSortOrder($sort)
    {
        $this->_sortOrder = $sort;
        return $this;
    }

    public function getDateArray($startYear)
    {
        $dateArray = array();
        $currentYear = Mage::app()->getLocale()->date()->get(Zend_Date::YEAR);
        $sortOrder = 0;
        if ($this->_sortOrder == 'asc'){
            for($i = $startYear; $i <= $currentYear; $i++){
                $dateArray[] = array(
                    'value' => $i,
                    'label' => $i,
                    'sort_order' => $sortOrder++
                );
            }
        }else{
            for($i = $currentYear; $i >= $startYear; $i--){
                $dateArray[] = array(
                    'value' => $i,
                    'label' => $i,
                    'sort_order' => $sortOrder++
                );
            }
        }
        
        return $dateArray;
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
            $optionArray[$option['value']] = array('label' => $option['label']);
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

