<?php

class Testimonial_ImportExport_Model_Import_Entity_ExtendedProduct extends Mage_ImportExport_Model_Import_Entity_Product
{
    protected $_import;
    protected $_category;
    protected $_categoryPaths = array();
    protected $_categoryPathsById = array();

    protected $_indexValueAttributes = array(
        'status',
        'tax_class_id',
        'visibility',
        'enable_googlecheckout',
        'gift_message_available',
        'custom_design',
        'is_in_stock'
    );

    protected $_missingCategoryNames = array();

    protected $_staticCategoryAttributes = array(
        'level', 'path', 'parent_id', 'url_path', 'url_key'
    );

    public function __construct()
    {
        parent::__construct();
        if ($errorsLimit = Mage::helper('magedoc')->getImportErrorLimit()){
            $this->_errorsLimit = $errorsLimit;
        }
    }

    /**
     * Initialize categories text-path to ID hash.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Product
     */
    protected function _initCategories()
    {
        $collection = Mage::getResourceModel('catalog/category_collection')->addNameToResult();
        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */
        foreach ($collection as $category) {
            $structure = explode('/', $category->getPath());
            $pathSize  = count($structure);
            if ($pathSize > 2) {
                $path = array();
                for ($i = 2; $i < $pathSize; $i++) {
                    $path[] = $collection->getItemById($structure[$i])->getName();
                }
                $this->_categories[implode('/', $path)] = $category->getId();
                $this->_categories[$category->getName()] = $category->getId();
                $this->_categories[$category->getId()] = $category->getId();
                //$this->_categoryPathsById[$category->getId()] = $category->getPath();
            }
        }
        return $this;
    }
    
    /**
     * Check product category validity.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    protected function _isProductCategoryValid(array $rowData, $rowNum)
    {
        if (!empty($rowData[self::COL_CATEGORY])) {
            if (!is_array($rowData[self::COL_CATEGORY])){
                $rowData[self::COL_CATEGORY] = array($rowData[self::COL_CATEGORY]);
            }
            foreach ($rowData[self::COL_CATEGORY] as $key => $category){
                if (!isset($this->_categories[$category])){
                    //$this->addRowError(self::ERROR_INVALID_CATEGORY, $rowNum);
                    if (!isset($this->_missingCategoryNames[$category])){
                        if ($categoryData = $this->getCategoryData(
                                $category,
                                $rowData[self::COL_STORE])){
                            $category = $this->importCategory($categoryData);
                            $this->_categories[$category->getName()] = $category->getId();
                            $this->_categoryPathsById[$category->getId()] = $category->getPath();
                            if ($category->getUtbId()){
                                $this->_categories[$category->getUtbId()] = $category->getId();
                                $this->_categoryPaths[$category->getUtbId()] = $category->getPath();
                            }
                        }else{
                            $this->_missingCategoryNames[$category] = $category;
                            self::log(self::ERROR_INVALID_CATEGORY. ' '. $category . ' (SKU '.$rowData[self::COL_SKU].')');
                        }
                    }
                    return false;
                }else{
                    //Mage::log($this->_categories[$category]);
                }
            }
        }
        return true;
    }

    protected function &_validateProductCategories(array &$rowData, $rowNum)
    {
        if (!empty($rowData[self::COL_CATEGORY])) {
            if (!is_array($rowData[self::COL_CATEGORY])){
                $rowData[self::COL_CATEGORY] = array($rowData[self::COL_CATEGORY]);
            }
            foreach ($rowData[self::COL_CATEGORY] as $key => $category){
                if (!isset($this->_categories[$category])){
                    unset($rowData[self::COL_CATEGORY][$key]);
                    Mage::log("ProductImport: Category $category was not found");
                }
            }
        }
        return $rowData;
    }

    public function isAttributeValid($attrCode, array $attrParams, array $rowData, $rowNum)
    {
        if (($attrParams['type'] == 'select' || $attrParams['type'] == 'multiselect')
                && !$attrParams['is_static']){
            if ($rowData[$attrCode] && !isset($attrParams['options'][mb_strtolower($rowData[$attrCode], 'UTF-8')]))
            {
                $attribute = Mage::getSingleton('importexport/import_proxy_product_resource')
                    ->getAttribute($attrCode);
                if ($option = $this->_importAttributeOption($attribute, $rowData[$attrCode])){
                    $attrParams['options'][mb_strtolower($rowData[$attrCode], 'UTF-8')] = $option->getId();
                    $this->_productTypeModels['simple']->addAttributeParams($rowData[self::COL_ATTR_SET], $attrParams);
                    $this->_productTypeModels['grouped']->addAttributeParams($rowData[self::COL_ATTR_SET], $attrParams);
                    $this->_productTypeModels['downloadable']->addAttributeParams($rowData[self::COL_ATTR_SET], $attrParams);
                }
            }
        }
        return parent::isAttributeValid($attrCode, $attrParams, $rowData, $rowNum);
    }

    protected function _importAttributeOption($attribute, $value, $storeId = 0)
    {
        $attribute->setOption(
                array(
                'value' => array(
                        "store_$storeId" => array(
                                0 => $value,
                                $storeId => $value
                        )
                )
                )
        );
        try {
            $attribute->save();
        } catch (Exception $e) {
            Mage::log($e->getMessage());
            return null;
        }
        $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter($attribute->getId())
                ->setStoreFilter($storeId)
                //->addFieldToFilter('value',$value)
                //->setCurPage(1)
                //->setPageSize(1)
                ->load();
        $option = $this->getItemByColumnValue($optionCollection,'value', $value);
        //return current($optionCollection);
        return $option;
    }

    public function getItemByColumnValue($collection, $column, $value){
        foreach ($collection as $item){
            if ($item->getData($column) === $value){
                return $item;
            }
        }
        return null;
    }

    public function setSource(Mage_ImportExport_Model_Import_Adapter_Abstract $source)
    {
        if (method_exists($source, 'setEntityAdapter')){
            $source->setEntityAdapter($this);
            $source->setCategoryFilename($this->getImport()->getEntitySourceAlias('catalog_category'));
            $source->setCategoryProductFilename($this->getImport()->getEntitySourceAlias('catalog_category_product'));
            $source->initAdditionalSources();
        }
        return parent::setSource($source);
    }
    
    protected function _getCategory()
    {
        if (!isset($this->_category)) {
            $this->_category = Mage::getModel('catalog/category');
        }
        return $this->_category;
    }

    protected function _initCategory()
    {
        $category = $this->_getCategory();
        $category->setData(array());
        $category->setOrigData();
        return $category;
    }

    public function getCategoryIdByUtbId($utbId)
    {
        return isset($this->_categories[$utbId])
                ? $this->_categories[$utbId]
                : null;
    }

    public function getCategoryPathByUtbId($utbId)
    {
        return isset($this->_categoryPaths[$utbId])
                ? $this->_categoryPaths[$utbId]
                : null;
    }

    public function getCategoryPathById($categoryId)
    {
        return isset($this->_categoryPathsById[$categoryId])
                ? $this->_categoryPathsById[$categoryId]
                : null;
    }

    public function importCategory($categoryData, $category = null)
    {
        if (is_null($category)){
            $category = $this->_initCategory();
        }
        $this->addCategoryData($category, $categoryData);
        $storeId = $category->getStoreId();
        if ($storeId) {
            $category->setStoreId(0);
            $category->save();
        }
        $category->setStoreId($storeId);
        $category->save();
        $category->setUpdated(true);
        self::log("Category: {$category->getName()} #{$category->getId()} was imported");
        return $category;
    }

    public function addCategoryData($category, $categoryData)
    {
        if ($category->getId())
        {
            foreach ($this->_staticCategoryAttributes as $attributeCode)
            {
                unset($categoryData[$attributeCode]);
            }
        }
        $category->addData($categoryData);
        return $category;
    }

    protected static function log($message)
    {
        Mage::log($message,null,'product_import_system.log');
    }

    public function getImport()
    {
        return $this->_import;
    }

    public function setImport($import)
    {
        $this->_import = $import;
    }

    public function getCategoryData($webId, $storeId = null)
    {
        return false;
        //$parentId = Mage::helper('utb')->getTopCategoryId($storeId);
        $parentId = 167;
        //$path = $this->getEntityAdapter()->getCategoryPathById($parentId);
        $path = Mage::getResourceSingleton('catalog/category')->getCategoryPathById($parentId);
        $name = trim($webId);

        $replacements = array(
            '{{name}}' => $name
        );
        $metaTitleTemplate = Mage::getStoreConfig('category_import/meta_title_template', $storeId);
        $metaDescriptionTemplate = Mage::getStoreConfig('category_import/meta_description_template', $storeId);
        $categoryData = array(
            'store_id'              => $storeId,
            'parent_id'             => $parentId,
            'name'                  => $name,
            'path'                  => $path,
            'meta_title'            => $name,
            'url_key'               => '',
            // TODO move to config
            'display_mode'          => 'PRODUCT',
            'url_path'              => '',
            'description'           => '',
            'meta_keywords'         => $name.(!empty($parentCategoryName) ? ', '.$parentCategoryName : ''),
            'is_active'             => 1,
            'is_anchor'             => 1,
            'include_in_menu'       => 1,
            'custom_use_parent_settings'    => 0,
            'custom_apply_to_products'      => 0,
        );
        if ($metaTitleTemplate){
            $categoryData['meta_title'] = str_replace(array_keys($replacements), $replacements, $metaTitleTemplate);
        }
        if ($metaDescriptionTemplate){
            $categoryData['meta_description'] = str_replace(array_keys($replacements), $replacements, $metaDescriptionTemplate);
        }
        return $categoryData;
    }
}
