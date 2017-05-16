<?php

class Phoenix_LayeredNav_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_REQUESTVAR_ALIAS_MAP = 'phoenix_layerednav/settings/requestvar_alias_map';
    const XML_PATH_SEO_URL_ENABLED = 'phoenix_layerednav/settings/is_seo_url_enabled';
    const CACHE_GROUP = 'eav';

    protected $_requestVarAliases;
    protected $_storeCategories = null;
    protected $_storesByCategoryId = array();
    protected $_filterAliases;
    protected $_filterAliasByRequestVar;
    protected $_attributeOptionIds;
    protected $_attributeOptionIdsChanged = false;
    protected $_categoryIds;
    protected $_categoryIdsChanged = false;

    public function  __destruct() {
        if ($this->_attributeOptionIdsChanged){
            $this->_saveCache($this->_attributeOptionIds,$this->_getAttributeOptionIdsCacheKey());
        }
        if ($this->_categoryIdsChanged){
            $this->_saveCache($this->_categoryIds,$this->_getCategoryIdsCacheKey());
        }
    }

    public function isMultipleSelectFilter($filter)
    {
        if (is_string($filter)){
            return $this->_isMultipleSelectFilter($filter);
        }
        return $filter->hasAttributeModel()
            ? $filter->getAttributeModel()->getIsMultipleSelectFilter()
            : null;
    }
    
    protected function _isMultipleSelectFilter($filterRequestVar)
    {
        $attribute = Mage::getResourceSingleton('catalog/product')->getAttribute($filterRequestVar);
        return $attribute
            ? $attribute->getIsMultipleSelectFilter()
            : null;
    }

    public function isFilterApplied($filterRequestVar, $value)
    {
        if ($filterValues = Mage::app()->getRequest()->getParam($filterRequestVar)){
            $filterValues = !is_array($filterValues)
                ? explode(',',$filterValues)
                : $filterValues;
            return array_search($value, $filterValues) !== false;
        }
        return false;
    }

    public function getRequestVarAliases()
    {
        if (!isset($this->_requestVarAliases)){
            $this->_requestVarAliases = unserialize(Mage::getStoreConfig(self::XML_PATH_REQUESTVAR_ALIAS_MAP));
            if (!is_array($this->_requestVarAliases)){
                $this->_requestVarAliases = array();
            }else{
                foreach ($this->_requestVarAliases['alias'] as $key => $alias){
                    if (!strlen($alias)){
                        unset($this->_requestVarAliases['alias'][$key]);
                        unset($this->_requestVarAliases['request_var'][$key]);
                        unset($this->_requestVarAliases['attribute_code'][$key]);
                    }
                }
            }
        }
        return $this->_requestVarAliases;
    }

    public function isSeoUrlEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_SEO_URL_ENABLED);
    }

    public function getFilter($key, $values){
        $alias = $this->getFilterAlias($key);
        if (!$alias){
            return null;
        }
        if (!empty($alias['attribute_code'])){
            if (!is_array($values)){
                $values = explode(',',$values);
            }
            $optionIds = array();
            foreach ($values as $value){
                if ($optionId = $this->getAttributeOptionId($alias['attribute_code'],$value)){
                    $optionIds[] = $optionId;
                }
            }
            return $optionIds
                ? array($alias['alias'] => $optionIds)
                : null;
        }
        elseif(($alias['alias'] == 'cat' || $alias['alias'] == 'collection') && !is_array($values)){
            return array($alias['alias'] => $this->getCategoryIdByName($values));
        }
        return null;
    }

    public function removeFilterFromPath($pathInfo, $filterRequestVar, $value = null)
    {
        if ($alias = $this->getFilterAliasByRequestVar($filterRequestVar)) {
            $parts = explode('/',$pathInfo);
            $escapedAlias = htmlentities($alias);
            foreach ($parts as $key => $part) {
                if (preg_match("/^{$escapedAlias}([^_].*)/",$part,$matches)) {
                    if ($this->_isMultipleSelectFilter($filterRequestVar)
                            && !is_null($value)){
                        $currentValues = $matches[1];
                        $currentValues = explode(',', $currentValues);
                        if (count($currentValues) > 1
                                && ($searchKey = array_search(mb_strtolower($value, 'UTF-8'),array_map('mb_strtolower', $currentValues))) !== false){
                            unset($currentValues[$searchKey]);
                            $parts[$key] = $escapedAlias.implode(',', $currentValues);
                        }else{
                            unset($parts[$key]);
                        }
                    }else{
                        unset($parts[$key]);
                    }
                }
            }
            $pathInfo = implode('/',$parts);
        }
        return $pathInfo;
    }

    public function appendFilterToPath($pathInfo, $filterRequestVar=null, $filterValue=null)
    {
        if ($filterRequestVar
                && $this->_isMultipleSelectFilter($filterRequestVar)) {
            if ($alias = $this->getFilterAliasByRequestVar($filterRequestVar)) {
                $parts = explode('/',$pathInfo);
                $escapedAlias = htmlentities($alias);
                $found = false;
                foreach ($parts as $key => $part) {
                    if (preg_match("/^{$escapedAlias}([^_].*)/",$part,$matches)) {
                        //unset($parts[$key]);
                        $currentValue = explode(',',$matches[1]);
                        if (array_search($filterValue, $currentValue) === false){
                            $parts[$key] .= ','.$filterValue;
                        }
                        $found = true;
                        break;
                    }
                }
                if (!$found){
                    $pathInfo = $this->_appendFilterToPath($pathInfo, $filterRequestVar, $filterValue);
                }else{
                    $pathInfo = implode('/',$parts);
                }
            }
        }else {
            return $this->_appendFilterToPath($pathInfo, $filterRequestVar, $filterValue);
        }
        return $pathInfo;
    }

    public function _appendFilterToPath($pathInfo, $filterRequestVar=null, $filterValue=null){
        if ($filterRequestVar){
            if ($alias = $this->getFilterAliasByRequestVar($filterRequestVar)){
                if (substr($pathInfo, -1) != '/'){
                    $pathInfo .= '/';
                }
                $pathInfo .= urlencode($alias.mb_strtolower($filterValue, 'UTF-8'));
            }
        }else{
            $pathFilters = Mage::registry('orig_path_info_filters');
            if ($pathFilters){
                if (substr($pathInfo, -1) != '/'){
                    $pathInfo .= '/';
                }
                $pathInfo .= $pathFilters;
            }
        }
        $suffix = Mage::helper('catalog/category')->getCategoryUrlSuffix();
        if ($suffix){
            $pathInfo .= $suffix;
        }
        
        return htmlentities($pathInfo);
    }

    public function getFilterAlias($key=null){
        if (is_null($this->_filterAliases)){
            $this->_initFilterAliases();
        }
        if (is_null($key)) return $this->_filterAliases;
        return isset($this->_filterAliases[$key]) ? $this->_filterAliases[$key] : null;
    }

    protected function _initFilterAliases(){
        $this->_filterAliases = array();
        $map = $this->getRequestVarAliases();
        foreach ($map['alias'] as $key => $alias){
            $this->_filterAliases[$alias] = array(
                'alias'     => $map['request_var'][$key],
            );
            if (!empty($map['attribute_code'][$key])){
                $this->_filterAliases[$alias]['attribute_code'] = $map['attribute_code'][$key];
            }
        }
    }

    public function getFilterAliasByRequestVar($key=null){
        if (is_null($this->_filterAliasByRequestVar)){
            $this->_initFilterAliasByRequestVar();
        }
        if (is_null($key)){
            return $this->_filterAliasByRequestVar;
        }
        return isset($this->_filterAliasByRequestVar[$key]) ? $this->_filterAliasByRequestVar[$key] : null;
    }

    public function _initFilterAliasByRequestVar(){
        $this->_filterAliasByRequestVar = array();
        foreach ($this->getFilterAlias() as $key => $alias){
            $this->_filterAliasByRequestVar[$alias['alias']] = $key;
        }
    }

    public function getAttributeOptionId($attributeCode, $value){
        if (is_null($this->_attributeOptionIds)){
            $this->_initAttributeOptionIds();
        }

        if (!isset($this->_attributeOptionIds[$attributeCode][$value])){
            $attribute = Mage::getResourceSingleton('catalog/product')->getAttribute($attributeCode);
            if (!$attribute){
                return null;
            }
            $optionId = $attribute->getSource()->getOptionId($value);
            $this->_attributeOptionIds[$attributeCode][$value] = $optionId;
            $this->_attributeOptionIdsChanged = true;
        }
        return $this->_attributeOptionIds[$attributeCode][$value];
    }

    protected function _initAttributeOptionIds(){
        $this->_attributeOptionIds = $this->_loadCache($this->_getAttributeOptionIdsCacheKey());
        return $this;
    }

    protected function _getAttributeOptionIdsCacheKey(){
        return 'layerednav_attribute_option_ids_' . Mage::app()->getStore()->getId();
    }

    public function getCategoryIdByName($name,$parentId=null)
    {
        if (!$name) {
            return null;
        }

        if (is_null($parentId)) {
            $parentId = Mage::app()->getStore()->getRootCategoryId();
        }

        if (is_null($this->_categoryIds)){
            $this->_initCategoryIds();
        }

        if (!isset($this->_categoryIds[$parentId])) {
            $this->_categoryIds[$parentId] = array();
        }
        if (!isset($this->_categoryIds[$parentId][$name])) {
            $category = Mage::getModel('catalog/category');
            $additionalAttributes = 'entity_id';
            $collection = $category->getResourceCollection();
            $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $collection->addAttributeToSelect($additionalAttributes)
                    ->addAttributeToFilter('name',$name);
            if ($parentId!== false) {
                //$collection->addAttributeToFilter('parent_id',$parentId);
            }
            foreach ($collection as $object) {
                $category = $object;
                break;
            }

            $this->_categoryIds[$parentId][$name] = $category->getId();
            $this->_categoryIdsChanged=true;
        }
        return $this->_categoryIds[$parentId][$name];
    }

    protected function _initCategoryIds(){
        $this->_categoryIds = $this->_loadCache($this->_getCategoryIdsCacheKey());
        return $this;
    }

    protected function _getCategoryIdsCacheKey(){
        return 'layerednav_category_ids_' . Mage::app()->getStore()->getId();
    }
    
    protected function _loadCache($cacheKey)
	{
		$responseData = array();

		/*
		 * Mage_Core_Block_Abstract::CACHE_GROUP only exists since 1.4
		 * As long as Magento 1.3 still is supported duplicate the constant here
		 */
		if (Mage::app()->useCache(self::CACHE_GROUP))
		{
			if ($cacheData = Mage::app()->loadCache($cacheKey))
			{
				$responseData = unserialize($cacheData);
			}
		}
		return $responseData;
	}

    protected function _saveCache($data, $id, $tags=array(), $lifeTime=false)
	{
		if (Mage::app()->useCache(self::CACHE_GROUP) && !empty($data))
		{
			Mage::app()->saveCache(serialize($data), $id, $this->_getCacheTags(), $this->_getCacheLifetime());
		}
		return $this;
	}

    protected function _getCacheLifetime()
	{
        return false;
    }

    protected function _getCacheTags(){
        return array(self::CACHE_GROUP, Mage_Eav_Model_Entity_Attribute::CACHE_TAG, Mage_Catalog_Model_Category::CACHE_TAG);
    }
}