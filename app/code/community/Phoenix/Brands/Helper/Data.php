<?php
class Phoenix_Brands_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CACHE_GROUP = 'eav';

    protected $_storeCategories = null;

    protected $_storesByCategoryId = array();

    protected $_filterAliases;

    protected $_filterAliasByRequestVar;

    protected $_attributeOptionIds;

    protected $_attributeOptionIdsChanged = false;

    protected $_categoryIds;

    protected $_categoryIdsChanged = false;

    protected $_swatchAttributes = array('color', 'size');
    
    protected $_avaiableBrandCollections = null;
    
    protected $_brandCategoriesImagesCache = null;


    public function  __destruct()
    {
        if ($this->_attributeOptionIdsChanged){            
            $this->_saveCache($this->_attributeOptionIds,$this->_getAttributeOptionIdsCacheKey());
        }
        if ($this->_categoryIdsChanged){            
            $this->_saveCache($this->_categoryIds,$this->_getCategoryIdsCacheKey());
        }
    }
    
    public function getConfig($key, $storeId = null)
    {
        return Mage::getStoreConfig('catalog/phoenixbrands/' . $key, $storeId);
    }
    
    /**
     * Formats price
     *
     * @param float $price
     * @param bool $includeContainer
     * @return string
     */
    public function formatPrice($price, $includeContainer = true)
    {
        return Mage::app()->getStore()->formatPrice($price, $includeContainer);
    }

    public function getBrandsRootCategoryId($storeId = null)
    {
        return Mage::getStoreConfig('catalog/phoenixbrands/brands_root_category_id', $storeId);
    }

    public function isProductInCategory($productId, $categoryId, $storeId){
        
    }

    public function getStoreIdByCategory($category)
    {
        if (is_int($category)){
            $category = Mage::getModel('phoenixbrands/catalog_category')->load($category);
        }
        if (!isset($this->_storesByCategoryId[$category->getId()])) {
            $path = $category->getPath();
            $categoryIds = explode('/',$path);
            $storeId = null;
            if (isset($categoryIds[1])){
            foreach (Mage::app()->getStores() as $store) {
                    if ($store->getRootCategoryId() == $categoryIds[1]) {
                        $storeId = $store->getId();
                        break;
                    }
                }
            }
            $this->_storesByCategoryId[$category->getId()] = $storeId;
        }
        return $this->_storesByCategoryId[$category->getId()];
    }

    /**
     * Retrieve current store categories
     *
     * @param   boolean|string $sorted
     * @param   boolean $asCollection
     * @return  Varien_Data_Tree_Node_Collection|Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection|array
     */
    public function getStoreCategories($recursionLevel = 0, $sorted=false, $asCollection=false, $toLoad=true )
    {
        $parent     = Mage::app()->getStore()->getRootCategoryId();
        $cacheKey   = sprintf('%d-%d-%d-%d', $parent, $sorted, $asCollection, $toLoad);
        if (isset($this->_storeCategories[$cacheKey])) {
            return $this->_storeCategories[$cacheKey];
        }

        /**
         * Check if parent node of the store still exists
         */
        $category = Mage::getModel('catalog/category');
        /* @var $category Mage_Catalog_Model_Category */
        if (!$category->checkId($parent)) {
            if ($asCollection) {
                return new Varien_Data_Collection();
            }
            return array();
        }

        $storeCategories = $category->getCategories($parent, $recursionLevel, $sorted, $asCollection, $toLoad);

        $this->_storeCategories[$cacheKey] = $storeCategories;
        return $storeCategories;
    }

    public function productAttribute($callObject, $attributeHtml, $params)
    {
        $attributeName  = $params['attribute'];
        $product        = $params['product'];        
        if ($attributeName == 'collection_category_id'){
            if ($collectionCategory = Mage::registry('current_collection_category')){
                return "<a href='{$collectionCategory->getUrl()}'>".$this->htmlEscape($collectionCategory->getName())."</a>";
            }            
        }
        return $attributeHtml;
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
        //print_r($this->_attributeOptionIds);die;
        return $this;
    }

    protected function _getAttributeOptionIdsCacheKey(){
        return 'phoenixbrands_attribute_option_ids_' . Mage::app()->getStore()->getId();
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
            //$loaded = $category->loadByAttribute('name',$name);
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
        return 'phoenixbrands_category_ids_' . Mage::app()->getStore()->getId();
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

    public function getAvaiableBrandCollections($currentCategoryId, $storeId)
    {
        if (!isset($this->_avaiableBrandCollections)){
            $collection = Mage::getResourceModel('catalog/category_collection')
                    ->removeAttributeToSelect()
                    ->addAttributeToSelect('entity_id');
            $select = $collection->getSelect();
            $subquery = clone $select;
            $subquery->reset()
                    ->from(array('cpi1'=>Mage::getSingleton('core/resource')->getTableName('catalog/category_product_index')), array('category_id' => new Zend_Db_Expr('distinct(cpi1.category_id)')))
                    ->join(array('cpi2'=>Mage::getSingleton('core/resource')->getTableName('catalog/category_product_index')), "cpi1.product_id = cpi2.product_id
                        AND cpi2.store_id = $storeId
                        AND cpi2.category_id = $currentCategoryId
                        AND NOT cpi2.visibility = 1",null)
                    ->where('cpi1.store_id = ?
                        AND NOT cpi1.visibility = 1', $storeId);

            $select
                    ->join(array('ac'=>$subquery), "e.entity_id = ac.category_id" , null);
            $collection->load();
            $this->_avaiableBrandCollections = $collection;
        }
        return $this->_avaiableBrandCollections;
    }
    
    public function getBrandLogoHtml(Mage_Catalog_Model_Category $brand)
    {
        $html = '';
        
        if ($brand->getLogoImage()) {
            $brandName = $this->htmlEscape($brand->getName());
            $html = '<img src="' . Mage::getBaseUrl('media') . 'catalog/category/' . $brand->getLogoImage() . '" alt="' . $brandName . '" title="' . $brandName . '"/>';
            $html = Mage::helper('catalog/output')->categoryAttribute($brand, $html, 'logo_image');
        }
        
        return $html;
    }
    
    public function getProductBrandImage(Mage_Catalog_Model_Product $product)
    {
        if (is_null($this->_brandCategoriesImagesCache)) {
            $category = Mage::getSingleton('catalog/category');
            $brandCategories = $this->getBrandCategoryCollection();

            $logoImageAttr = Mage::getModel('eav/entity_attribute');
            $logoImageAttr->loadByCode('catalog_category', 'logo_image');
            
            $brandCategories->getSelect()
                ->joinInner(
                    array('logo_image' => 'catalog_category_entity_' . $logoImageAttr->getBackendType()), 
                    ($category->getResource() instanceof Mage_Catalog_Model_Resource_Category_Flat ? 'main_table' : 'e') . '.entity_id=logo_image.entity_id AND logo_image.attribute_id=' . $logoImageAttr->getId() . ' AND logo_image.store_id=0 AND logo_image.value!=""', 
                    array('logo_image' => 'logo_image.value')
                );
                
            $this->_brandCategoriesImagesCache = array();
            foreach ($brandCategories->getData() as $category) {
                $this->_brandCategoriesImagesCache[$category['entity_id']] = $category['logo_image'];
            }
        }
        
        if (isset($this->_brandCategoriesImagesCache[$product->getBrandCategoryId()])) {
            return Mage::getBaseUrl('media') . 'catalog/category/' . $this->_brandCategoriesImagesCache[$product->getBrandCategoryId()];
        }
        else {
            return null;
        }
    }
    
    /*
     * get brand categories collection. this method is used to select also categories with include_in_menu = 0
     */ 
    public function getBrandCategoryCollection($brandsRootCategoryId = null)
    {
        if (!$brandsRootCategoryId) {
            $brandsRootCategoryId = $this->getBrandsRootCategoryId();
        }
        
        $collection = null;
        
        $category = Mage::getSingleton('catalog/category');

        if ($category->getResource() instanceof Mage_Catalog_Model_Resource_Category_Flat) {
            $read = Mage::getSingleton('core/resource')->getConnection('core_write');
            $select = $read->select()
                ->from(array('mt' => $category->getResource()->getMainStoreTable($category->getResource()->getStoreId())), array('path'))
                ->where('mt.entity_id = ?', $brandsRootCategoryId);
            $parentPath = $read->fetchOne($select);

            $collection = $category->getCollection()
                ->addNameToResult()
                ->addAttributeToSelect('logo_image')
                ->addUrlRewriteToResult()
                ->addParentPathFilter($parentPath)
                ->addStoreFilter()
                ->addIsActiveFilter()
                ->addSortedField(true);
        }
        else {
            $tree = Mage::getResourceModel('catalog/category_tree');
            $nodes = $tree->loadNode($brandsRootCategoryId)
                ->loadChildren(1)
                ->getChildren();
    
            $tree->addCollectionData(null, true, $brandsRootCategoryId, false, false);
    
            $collection = $tree->getCollection();
            $collection
                ->addAttributeToSelect('logo_image')
                ->addAttributeToFilter('is_active', '1')
                ->addFieldToFilter('block_type', Phoenix_Brands_Model_Catalog_Category::BLOCK_TYPE_BRAND)
                ;
        }
        
        /*if ($this->getConfig('brands_show_brands_withproducts_only')) {
            $collection->setLoadProductCount(true);
            foreach ($collection as $key => $category) {
                if ($category->getProductCount() <= 0) {
                    $collection->removeItemByKey($key);
                }
            }
        }*/
        
        return $collection;
    }

    public function getBrandAttributeCode($storeId = null)
    {
        return $this->getConfig('brands_attribute', $storeId);
    }

    public function getBrandAttribute($storeId = null)
    {
        if ($attributeCode = $this->getBrandAttributeCode($storeId)){
            return Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attributeCode);
        }
        return null;
    }

    public function getCategoryBrandAttributeCode($storeId = null)
    {
        return 'brand';
    }

    public function getCategoryBrandAttribute($storeId = null)
    {
        if ($attributeCode = $this->getCategoryBrandAttributeCode($storeId)){
            return Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attributeCode);
        }
        return null;
    }

    public function isEnabled($storeId = null)
    {
        return true;
    }
}