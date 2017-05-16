<?php

class Phoenix_LayeredNav_Model_Catalog_Layer_Filter_Attribute extends Mage_Catalog_Model_Layer_Filter_Attribute
{
    protected $_isApplied = array();

    /**
     * Retrieve resource instance
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute
     */
    protected function _getResource()
    {
        if (is_null($this->_resource)) {
            $this->_resource = Mage::getResourceModel('phoenix_layerednav/catalog_layer_filter_attribute');
        }
        return $this->_resource;
    }

    /**
     * Apply attribute option filter to product collection
     *
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Varien_Object $filterBlock
     * @return  Mage_Catalog_Model_Layer_Filter_Attribute
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $categoryId = (int)$this->getLayer()->getCurrentCategory()->getId();
        if (!empty($this->_isApplied[$categoryId])){
            return $this;
        }
        $filters = $request->getParam($this->_requestVar);
        if(!$filters){
            return $this;
        }
        if (!is_array($filters)) {
            $filters = explode(',',$filters);
        }
        foreach ($filters as $key => $filter){
            if (!$text = $this->_getOptionText($filter)){
                unset($filters[$key]);
            }else{
                $this->getLayer()->getState()->addFilter($this->_createItem($text, $filter));
            }
        }
        if ($filters) {
            $this->_getResource()->applyFilterToCollection($this, $filters);
            if (!Mage::helper('phoenix_layerednav')->isMultipleSelectFilter($this)){
                $this->_items = array();
            }
            $this->_isApplied[$categoryId] = true;
        }
        return $this;
    }
}