<?php

class Testimonial_MageDoc_Model_Source_OrderManager extends Testimonial_MageDoc_Model_Source_Abstract
{
    protected $_isActive;

    public function getCollectionArray()
    {
        if (empty($this->_collectionArray)) {
            $items = Mage::getResourceModel('admin/user_collection');
            if (!is_null($this->_isActive)){
                $items->addFieldToFilter('is_active', $this->_isActive);
            }
            foreach($items as $item){
                $this->_collectionArray[] = array(
                    'value' => $item->getUserId(), 
                    'label' => $item->getName());
            }
        }

        return $this->_collectionArray;
    }
 
    
    public function getAllOptions($withUnassigned = true)
    {
        if (is_null($this->_options)) {
            $this->_options = $this->getCollectionArray();
            
        }
        $options = $this->_options;
        if ($withUnassigned){
            array_unshift($options, array('value'=> 0, 'label'=>Mage::helper('magedoc')->__('Unassigned')));
        }
        return $options;
    }
    
    public function getOptionArray()
    {
        $options = $this->getAllOptions();
        $optionArray = array();
        foreach($options as $option){
            $optionArray[$option['value']] = $option['label'];
        }
        return $optionArray;
    }

    public function setIsActive($isActive)
    {
        $this->_isActive = $isActive;
        return $this;
    }
}
