<?php
class Phoenix_Brands_Model_Adminhtml_System_Config_Source_Category
{
    public function toOptionArray($addEmpty = true)
    {
        $_storeCode = Mage::app()->getFrontController()->getAction()->getRequest()->getParam('store');
        $categoryId = Mage::app()->getStore($_storeCode)->getRootCategoryId();
        $category = Mage::getModel('catalog/category')->load($categoryId);
        $tree = Mage::getResourceModel('catalog/category_tree');

        $collection = Mage::getResourceModel('catalog/category_collection');

        $collection->addAttributeToSelect('name')
            ->addAttributeToFilter('path', array('like' => "{$category->getPath()}/%"))
            ->addAttributeToFilter('level', array('in' => array(1,2,3)))
            ->addAttributeToSort('path')
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
               'label' => str_repeat('-', $category->getLevel()-2).$category->getName(),
               'value' => $category->getId()
            );
        }

        return $options;
    }
}
