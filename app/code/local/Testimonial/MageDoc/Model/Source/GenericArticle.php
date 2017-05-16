<?php

class Testimonial_MageDoc_Model_Source_GenericArticle extends Testimonial_MageDoc_Model_Source_Abstract
{
    protected $_enabledOnly = false;
    protected $_addTitles = true;
    protected $_valueField = 'ga_id';
    protected $_labelField = 'name';
    
    public function getCollectionArray()
    {
        if (empty($this->_collectionArray)) {
            $items = Mage::getResourceModel('magedoc/tecdoc_genericArticle_collection');
            $items->joinDesignation($items, 'main_table', 'GA_DES_ID', 'name', null, true);
            $valueField = $this->_valueField;
            $labelField = $this->_labelField;

            $items->setOrder($labelField, Varien_Data_Collection::SORT_ORDER_ASC);

            foreach($items as $item){
                $this->_collectionArray[] = array('value' => $item->getData($valueField), 'label' => $item->getData($labelField));
            }
        }

        return $this->_collectionArray;
    }
    
    public function setEnabledFilter($enabled)
    {
        $this->_enabledOnly = (bool)$enabled;
        return $this;
    }

    public function addTitles($addTitles = true)
    {
        $this->_addTitles = $addTitles;

        return $this;
    }
    
}
