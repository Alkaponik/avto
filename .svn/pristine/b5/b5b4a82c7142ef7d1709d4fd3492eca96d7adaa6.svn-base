<?php

class Testimonial_CustomerNotification_Model_Source_AdminUser extends MageDoc_Bookkeeping_Model_Source_Abstract
{
    protected $_arrayWithEmpty = false;
    protected $_collectionArray = array();

    public function getAllOptions($withEmpty = true)
    {
        if (is_null($this->_options)) {
            $this->_options = $this->getCollectionArray();
        }
        $options = $this->_options;
        if ($withEmpty){
            array_unshift($options, array('value'=>'', 'label'=>Mage::helper('customernotification')->__('--Please Select--')));
        }
        return $options;
    }

    public function toOptionArray($withEmpty = true)
    {
        $withEmpty = $withEmpty || $this->_arrayWithEmpty;
        return $this->getOptionArray($withEmpty);
    }

    public function getOptionArray($withEmpty = true)
    {
        $options = $this->getAllOptions($withEmpty);
        $optionArray = array();
        foreach($options as $option){
            $optionArray[$option['value']] = $option['label'];
        }
        return $optionArray;
    }

    public function getCollectionArray()
    {
        if (empty($this->_collectionArray)) {
            $items = Mage::getResourceModel('admin/user_collection');
            foreach($items as $item){
                $this->_collectionArray[] = array(
                    'value' => $item->getUserId(),
                    'label' => $item->getFirstname(). ' ' . $item->getLastname());
            }
        }
        return $this->_collectionArray;
    }
    
}
