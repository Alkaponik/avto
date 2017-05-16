<?php

class Testimonial_MageDoc_Model_Source_Manufacturer extends Testimonial_MageDoc_Model_Source_Abstract
{
    public function getCollectionArray()
    {
        if (empty($this->_collectionArray)) {
            $items = Mage::getResourceModel('magedoc/manufacturer_collection');
            foreach($items as $item){
                $this->_collectionArray[] = array('value' => $item->getTdMfaId(), 'label' => $item->getTitle());
            }
        }

        return $this->_collectionArray;
    }
    
}
