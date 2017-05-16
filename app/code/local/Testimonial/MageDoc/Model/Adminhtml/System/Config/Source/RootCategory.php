<?php

class Testimonial_MageDoc_Model_Adminhtml_System_Config_Source_Category
{
    public function toOptionArray($addEmpty = true)
    {
        $_storeCode = Mage::app()->getFrontController()->getAction()->getRequest()->getParam('store');
        
        if ($_storeCode) {
            $categoryId = Mage::app()->getStore($_storeCode)->getRootCategoryId();
            $categoryId = $store->getRootCategoryId();
            $pathFilter = '^'.Mage_Catalog_Model_Category::TREE_ROOT_ID."/$categoryId/[0-9]+$";
        }
        else {
            $pathFilter = '^'.Mage_Catalog_Model_Category::TREE_ROOT_ID."/[0-9]+/[0-9]+$";
        }
                
        $tree = Mage::getResourceModel('catalog/category_tree');

        $collection = Mage::getResourceModel('catalog/category_collection');

        $collection->addAttributeToSelect('name')
            ->addPathFilter($pathFilter)
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
               'label' => $category->getName(),
               'value' => $category->getId()
            );
        }

        return $options;
    }
}