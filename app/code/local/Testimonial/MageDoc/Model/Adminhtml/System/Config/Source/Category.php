<?php

class Testimonial_MageDoc_Model_Adminhtml_System_Config_Source_Category
{
    protected $_levels = array(1,2,3);

    public function setLevels($levels)
    {
        $this->_levels = $levels;
        return $this;
    }

    public function toOptionArray($addEmpty = true)
    {                
        $collection = Mage::getResourceModel('catalog/category_collection');

        $collection->addAttributeToSelect('name')
            ->addAttributeToFilter('level', array('in' => $this->_levels))
            ->setOrder('path')
            ->load();

        $options = array();

        if ($addEmpty) {
            $options[] = array(
                'label' => Mage::helper('adminhtml')->__('-- Please Select a Category --'),
                'value' => ''
            );
        }
        foreach ($collection as $category) {
            $options[] = array(
               'label' => str_repeat('-', $category->getLevel()-1).$category->getName(),
               'value' => $category->getId()
            );
        }

        return $options;
    }
    
    public function getOptionArray()
    {
        $options = $this->toOptionArray(false);
        $optionArray = array();
        foreach ($options as $option){
            $optionArray[$option['value']] = $option['label'];
        }
        return $optionArray;
    }

}