<?php

class Testimonial_MageDoc_Model_Catalog_Layer_Filter_Type extends Mage_Catalog_Model_Layer_Filter_Abstract
{
    protected $_typeModel;
    protected $_resource;
    protected $_isApplied = array();

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->_requestVar = 'vehicle_type';
    }

    /**
     * Retrieve resource instance
     *
     * @return Testimonial_MageDoc_Model_Mysql4_Catalog_Layer_Filter_Type
     */
    protected function _getResource()
    {
        if (is_null($this->_resource)) {
            $this->_resource = Mage::getResourceModel('magedoc/catalog_layer_filter_type');
        }
        return $this->_resource;
    }

    /**
     * Get filter name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('magedoc')->__('Vehicle');
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
        $filters = $request->getParam($this->_requestVar)
            ? $request->getParam($this->_requestVar)
            : Mage::getSingleton('core/session')->getTypeIds();
        if ($filters === 'clear'){
            Mage::getSingleton('core/session')->unsTypeIds();
            $filters = null;
        }
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
            Mage::getSingleton('core/session')->setTypeIds($filters);
            if(Mage::registry('current_magedoc_type_ids') !== null){
                Mage::unregister('current_magedoc_type_ids');
            }
            Mage::register('current_magedoc_type_ids', current($filters));
            $this->_isApplied[$categoryId] = true;
        }
        return $this;
    }

    protected function getTypeModel()
    {
        if (!isset($this->_typeModel))
        {
            $this->_typeModel = Mage::getModel('magedoc/tecdoc_type');
        }
        return $this->_typeModel;
    }

    protected function _getOptionText($filter)
    {
        $type = $this->getTypeModel();
        $type->setData(array());
        $type->setOrigData();
        $type->isPartialLoad(false);
        $type->load($filter);
        
        return $type->getId()
               ? (string)$type
               : null;
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $key = $this->getLayer()->getStateKey().'_'.$this->_requestVar;
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        if ($data === null) {
            $optionsCount = $this->_getResource()->getCount($this);
            $options = $optionsCount;
            $data = array();

            foreach ($options as $key => $option) {
                $label = $this->_getOptionText($key);
                if (!$label) {
                    continue;
                }
                $option = array(
                    'label' => $label,
                    'value' => $key
                );
                if (Mage::helper('core/string')->strlen($option['value'])) {
                    // Check filter type
                    if (!empty($optionsCount[$option['value']])) {
                        $data[] = array(
                            'label' => $option['label'],
                            'value' => $option['value'],
                            'count' => $optionsCount[$option['value']],
                        );
                    }
                }
            }

            $tags = array(
                Mage_Eav_Model_Entity_Attribute::CACHE_TAG.':'.$this->_requestVar
            );

            $tags = $this->getLayer()->getStateTags($tags);
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }
        return $data;
    }

    public function getResetValue()
    {
        return 'clear';
    }
}