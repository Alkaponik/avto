<?php

class Testimonial_MageDoc_Model_Adminhtml_System_Config_Source_TecdocCategory
{
    protected $_collection;
    
    public function toOptionArray($addEmpty = true)
    {
        $_storeCode = Mage::app()->getFrontController()->getAction()->getRequest()->getParam('store');
        
        if ($_storeCode) {
            $categoryId = Mage::app()->getStore($_storeCode)->getRootCategoryId();
            $pathFilter = '^'.Mage_Catalog_Model_Category::TREE_ROOT_ID."/$categoryId/[0-9]+$";
        }
        else {
            $pathFilter = '^'.Mage_Catalog_Model_Category::TREE_ROOT_ID."/[0-9]+/[0-9]+$";
        }
                
        if (!isset($this->_collection)){
            $this->_collection = Mage::getResourceModel('catalog/category_collection');
            if (Mage::helper('core')->isModuleEnabled('MageDoc_DirectoryTecdoc')) {
                $searchTreeResource = Mage::getResourceModel('magedoc/tecdoc_searchTree');
                $this->_collection->getSelect()
                    ->joinInner(array('td_searchTree' => $searchTreeResource->getMainTable()),
                        "td_searchTree.STR_ID = td_str_id", '')
                    ->order('td_searchTree.STR_SORT');
                $this->_collection->addAttributeToFilter('td_str_id', array('notnull' => true));
            }
            $this->_collection->addAttributeToSelect('name')
                ->load();
        }

        $options = array();

        if ($addEmpty) {
            $options[] = array(
                'label' => Mage::helper('adminhtml')->__('-- Please Select a Category --'),
                'value' => ''
            );
        }
        foreach ($this->_collection as $category) {
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