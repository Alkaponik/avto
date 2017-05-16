<?php

class Testimonial_MageDoc_Model_Source_Category extends Testimonial_MageDoc_Model_Source_Abstract
{
    public function getCollectionArray()
    {                
        $collection = Mage::getResourceModel('catalog/category_collection');

        $collection->addAttributeToSelect('name')
                ->getSelect()
                ->order('path')
                ->where('level > 1');
        $collection->load();

        $options = array();

        foreach ($collection as $category) {
            $options[] = array(
               'label' => str_repeat('-', $category->getLevel()-1).$category->getName(),
               'value' => $category->getId()
            );
        }

        return $options;
    }
       
}
