<?php

class Testimonial_MageDoc_Model_Source_Language extends Testimonial_MageDoc_Model_Source_Abstract
{
    protected $_enabledOnly = false;
    
    public function getCollectionArray()
    {
        if (empty($this->_collectionArray)) {
            $items = Mage::getResourceModel('magedoc/tecdoc_language_collection');
            $items->joinDesignation($items, 'main_table', 'LNG_DES_ID', 'title');
            $items->setOrder('title', Varien_Data_Collection::SORT_ORDER_ASC);
            foreach($items as $item){
                $this->_collectionArray[] = array('value' => $item->getLngId(), 'label' => $item->getTitle());
            }
        }

        return $this->_collectionArray;
    }
}
