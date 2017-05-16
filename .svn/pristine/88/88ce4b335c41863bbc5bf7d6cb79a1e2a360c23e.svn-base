<?php

class Testimonial_MageDoc_Model_Import_Entity_Product extends Mage_ImportExport_Model_Import_Entity_Product
{
    const MAX_PACKET_SIZE_SCALE = 0.2;
    const DEFAULT_RETAILER = 0;

    protected $_entityRowsUpCount = 0;
    protected $_entityRowsInCount = 0;
    protected $_indexValueAttributes = array(
        'status',
        'tax_class_id',
        'visibility',
        'enable_googlecheckout',
        'gift_message_available',
        'custom_design',
        'supplier',
        'retailer_id',
        'is_imported'
    );

    protected $_staticAttributesToUpdate = array(
        'updated_at',
        'retailer_id'
    );
    
    protected $_staticAttributeValues = array(
                "_attribute_set"            => "Default",
                "_type"                     => "spare",
                "enable_googlecheckout"     => "1",
                "is_imported"               => "1",
                "required_options"          => "0",
                "status"                    => "1",
                "updated_at"                => "NULL",
                "visibility"                => "4",
                "weight"                    => "0",
                "min_qty"                   => "1",
                "is_qty_decimal"            => "1",
                "backorders"                => "1",
                "use_config_backorders"     => "1",
                "min_sale_qty"              => "1",
                "use_config_min_sale_qty"   => "1",
                "max_sale_qty"              => "100",
                "use_config_max_sale_qty"   => "1",
                "is_in_stock"               => "1",
                "manage_stock"              => "1",
                "use_config_manage_stock"   => "1",
                "tax_class_id"              => "2",
    );

    protected $_websites = array();
    protected $_processedRowsCount = 0;
    protected $_importModel;
    protected $_importParams = array();
    protected $_importIds = array();
    protected $_updateMode = false;
    protected $_errorsLimit;
    protected $_supplierId;
    protected $_retailerId;
    protected $_categoryId;
    protected $_importStatus = null;
    protected $_helper;
    protected $_entityTypeCode = 'catalog_product';
    protected $_stripCodeChars = array( ' ', '+', '.', '-', '=', '\\', '/', '\'', '"', ')', '(', ']', '[', );
    /**
     * @var Testimonial_MageDoc_Model_Directory_Abstract
     */
    protected $_directory;


    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->_entityTypeCode = 'magedoc_product';

        $this->_errorsLimit = $this->getHelper()->getImportErrorLimit();
    }

    /**
     * remove all type model except spare
     */
    protected function _initTypeModels()
    {
        $config = Mage::getConfig()->getNode(self::CONFIG_KEY_PRODUCT_TYPES)->asCanonicalArray();
        $config = array(
            'simple'    => $config['simple'],
            'spare'     => $config['spare'],
        );
        foreach ($config as $type => $typeModel) {
            if (!($model = Mage::getModel($typeModel, array($this, $type)))) {
                Mage::throwException("Entity type model '{$typeModel}' is not found");
            }
            if (! $model instanceof Mage_ImportExport_Model_Import_Entity_Product_Type_Abstract) {
                Mage::throwException(
                    Mage::helper('importexport')->__('Entity type model must be an instance of Mage_ImportExport_Model_Import_Entity_Product_Type_Abstract')
                );
            }
            if ($model->isSuitable()) {
                $this->_productTypeModels[$type] = $model;
            }
            $this->_particularAttributes = array_merge(
                $this->_particularAttributes,
                $model->getParticularAttributes()
            );
        }
        // remove doubles
        $this->_particularAttributes = array_unique($this->_particularAttributes);

        return $this;
    }

    /**
     * EAV entity type code getter.
     *
     * @abstract
     * @return string
     */
    public function getEntityTypeCode()
    {
        return $this->_entityTypeCode;
    }

    public function getHelper($helperName = null)
    {
        if ($helperName){
            return Mage::helper($helperName);
        }

        if(!isset($this->_helper)){
            $this->_helper = Mage::helper('magedoc');
        }
        return $this->_helper;
    }

    public function setImportStatus($status = false)
    {
        $this->_importStatus = $status;
        return $this;
    }
    
    public function getImportStatus()
    {
        return $this->_importStatus;  
    }

    
    public function setImportIds(array $importIds)
    {
        $this->_importIds = $importIds;
        return $this;
    }
    
    public function setRetailerId($retialerId)
    {
        $this->_retailerId = $retialerId;
        return $this;
    }
    
    public function getRetailerId()
    {
        if(!isset($this->_retailerId)){
            $this->_retailerId = self::DEFAULT_RETAILER;
        }
        return $this->_retailerId;
    }

    public function setCategoryId($categoryId)
    {
        $this->_categoryId = $categoryId;
        return $this;
    }
    
    public function getCategoryId()
    {
        if(!isset($this->_categoryId)){
            $this->_categoryId = null;
        }
        return $this->_categoryId;
    }

    public function setSupplierId($supplierId)
    {
        $this->_supplierId = $supplierId;
        return $this;
    }
    
    public function getSupplierId()
    {
        if(!isset($this->_supplierId)){
            $this->_supplierId = null;
        }
        return $this->_supplierId;
    }

    
    public function getImportModel()
    {
        if(!isset($this->_importModel)){
            $this->_importModel = $this->getDirectory()->getRetailerImportModel($this->getRetailerId());
            $this->_importModel->addData(array('retailer_id' => $this->getRetailerId()));
            $this->_importModel->setSupplierId($this->getSupplierId())
                    ->setCategoryId($this->getCategoryId())
                    ->setImportStatus($this->getImportStatus())
                    ->setImportIds($this->_importIds)
                    ->setIsUpdateMode($this->_updateMode);
        }
        
        return $this->_importModel;
    }

    /**
     * Initialize existent product SKUs.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Product
     */
    protected function _initSkus()
    {
        $columns = array('entity_id', 'type_id', 'attribute_set_id', 'sku'/*, 'code_normalized'*/);
        
        $stmt = $this->getProductEntitiesInfo($columns);
        while ($info = $stmt->fetch(Zend_Db::FETCH_ASSOC)){
        //foreach (Mage::getSingleton('catalog/product')->getProductEntitiesInfo($columns) as $info) {
            $typeId = $info['type_id'];
            $sku = $info['sku'];
            $normalizedSku = $this->normalizeCode($sku);
            $this->_oldSku[$sku] = array(
                'type_id'        => $typeId,
                'attr_set_id'    => $info['attribute_set_id'],
                'entity_id'      => $info['entity_id'],
                'supported_type' => isset($this->_productTypeModels[$typeId])
            );
            if ($normalizedSku != $sku){
                $this->_oldSku[$normalizedSku] = &$this->_oldSku[$sku];
            }
            if (!empty($info['code_normalized'])){
                $normalizedCodeSku = $this->normalizeCode(current(explode('-', $sku)).$info['code_normalized']);
                if ($normalizedCodeSku != $sku && $normalizedCodeSku != $normalizedSku){
                    $this->_oldSku[$normalizedCodeSku] = &$this->_oldSku[$sku];
                }
            }
        }
        return $this;
    }

    /**
     * Initialize website values.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Product
     */
    protected function _initWebsites()
    {
        /** @var $website Mage_Core_Model_Website */
        foreach (Mage::app()->getWebsites() as $website) {
            $this->_websiteCodeToId[$website->getCode()] = $website->getId();
            $this->_websiteCodeToStoreIds[$website->getCode()] = array_flip($website->getStoreCodes());
            $this->_websites[$website->getId()] = $website;
        }
        return $this;
    }

    /**
     * Retrieve product entities info
     *
     * @param  array|string|null $columns
     * @return array
     */
    public function getProductEntitiesInfo($columns = null, $productIds = array())
    {
        if (!empty($columns) && is_string($columns)) {
            $columns = array($columns);
        }

        $adapter = $this->_connection;
        /*$collection = Mage::getResourceModel('catalog/product_collection');
        $select = $collection->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns($columns);
        $collection->joinAttribute('name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $adminStore)*/
        $select = $adapter->select()
            ->from(Mage::getResourceSingleton('catalog/product')->getTable('catalog/product'), $columns);

        if ($productIds){
            $select->where('entity_id IN (?)', $productIds);
        }

        return $adapter->query($select);
    }
    
    protected function _initCategories()
    {
        $collection = Mage::getResourceModel('catalog/category_collection')->addNameToResult();
    
        foreach ($collection as $category) {
                $this->_categories[$category->getId()] = $category->getId();
        }
        return $this;
    }
        
    
    protected function _saveValidatedBunches()
    {  
        $productsArray = $this->getImportModel()
                ->getProductsArray();
        if(empty($productsArray)){
            throw new Exception($this->getHelper()->__('Collection is empty'));
        }
        
        $source          = $productsArray;
        $productDataSize = 0;
        $bunchRows       = array();
        $startNewBunch   = false;
        $nextRowBackup   = array();
        $maxDataSize = Mage::getResourceHelper('importexport')->getMaxDataSize();
        $this->_dataSourceModel->cleanBunches();

        $rowData = current($source);
        while ($rowData !== false || $bunchRows) {
            if ($startNewBunch || $rowData === false) {
                $this->_dataSourceModel->saveBunch($this->getEntityTypeCode(), $this->getBehavior(), $bunchRows);

                $bunchRows       = $nextRowBackup;
                $productDataSize = strlen(serialize($bunchRows));
                $startNewBunch   = false;
                $nextRowBackup   = array();
            }
            if ($rowData !== false) {
                if ($this->_errorsCount >= $this->_errorsLimit) { // errors limit check
                    return;
                }
                
                $rowData = array_merge($this->_staticAttributeValues, $rowData);
                $rowData["_store"] = $this->getHelper()->getStoreForImport();
                $rowData["_product_websites"] = $this->getHelper()->getWebsitesToImport();
                $this->_processedRowsCount++;
                if ($this->validateRow($rowData, key($source))) { // add row to bunch for save
                    $rowData = $this->_prepareRowForDb($rowData);
                    $rowSize = strlen(serialize($rowData));

                    if (($productDataSize + $rowSize) >= $maxDataSize*self::MAX_PACKET_SIZE_SCALE) { // check bunch size
                        $startNewBunch = true;
                        $nextRowBackup = array(key($source) => $rowData);
                    } else {
                        $bunchRows[key($source)] = $rowData;
                        $productDataSize += $rowSize;
                    }
                }
            }
                $rowData = next($source);
        }
        
        
        return $this;
    }


    protected function _importData()
    {
        if (Mage_ImportExport_Model_Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->_deleteProducts();
        } else {
            $this->_saveValidatedBunches();
            $this->_saveProducts();
            $this->_saveStockItem();
            $this->_saveLinks();
            $this->_saveCustomOptions();
            foreach ($this->_productTypeModels as $productType => $productTypeModel) {
                $productTypeModel->saveData();
            }
        }
        return true;
    }
    
    /**
     * Gather and save information about product entities.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Product
     */
    
    protected function _isProductCategoryValid(array $rowData, $rowNum)
    {
        if (!empty($rowData[self::COL_CATEGORY])){
            foreach($rowData[self::COL_CATEGORY] as $category)
                if(!isset($this->_categories[$category])) {
                    $this->addRowError(self::ERROR_INVALID_CATEGORY, $rowNum);
                    return false;
                }
        }
        return true;
    }

    /**
     * Check product website belonging.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    protected function _isProductWebsiteValid(array $rowData, $rowNum)
    {
        if (!empty($rowData['_product_websites'])){ 
            foreach ($rowData['_product_websites'] as $websiteId) {
                if (!isset($this->_websites[$websiteId])){
                    $this->addRowError(self::ERROR_INVALID_WEBSITE, $rowNum);
                    return false;
                }
            }
        }
        return true;
    }
    
    protected function _saveProducts()
    {
        /** @var $resource Mage_ImportExport_Model_Import_Proxy_Product_Resource */
        $resource       = Mage::getModel('importexport/import_proxy_product_resource');
        $priceIsGlobal  = Mage::helper('catalog')->isPriceGlobal();
        $strftimeFormat = Varien_Date::convertZendToStrftime(Varien_Date::DATETIME_INTERNAL_FORMAT, true, true);
        $productLimit   = null;
        $productsQty    = null;
        
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            
            $typeData     = array();
            $entityRowsIn = array();
            $entityRowsUp = array();
            $attributes   = array();
            $websites     = array();
            $categories   = array();
            $tierPrices   = array();
            $mediaGallery = array();
            $uploadedGalleryFiles = array();
            $dataIds = array();
                        
            foreach ($bunch as $rowNum => $rowData) {


                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }
                
                $rowScope = $this->getRowScope($rowData);
                
                if (self::SCOPE_DEFAULT == $rowScope) {
                    $rowSku = $rowData[self::COL_SKU];

                    // 1. Entity phase
                    if (isset($this->_oldSku[$rowSku])) { // existing row
                        $entityRowsUp[] = array(
                            'updated_at' => now(),
                            'entity_id'  => $this->_oldSku[$rowSku]['entity_id'],
                            'retailer_id'      => $rowData['retailer_id']
                        );
                        $this->_entityRowsUpCount++;
                    } else { // new row
                        if (!$productLimit || $productsQty < $productLimit) {
                            $entityRowsIn[$rowSku] = array(
                                'entity_type_id'   => $this->_entityTypeId,
                                'attribute_set_id' => $this->_newSku[$rowSku]['attr_set_id'],
                                'type_id'          => $this->_newSku[$rowSku]['type_id'],
                                'sku'              => $rowSku,
                                'created_at'       => now(),
                                'updated_at'       => now(),
                                'td_art_id'        => $rowData['td_art_id'],
                                'supplier'         => $rowData['supplier'],
                                'retailer_id'      => $rowData['retailer_id'],
                                'code'             => $rowData['code'],
                                /*'code_normalized'  => $rowData['code_normalized'],*/
                            );
                            $productsQty++;
                        } else {
                            $rowSku = null; // sign for child rows to be skipped
                            $this->_rowsToSkip[$rowNum] = true;
                            continue;
                        }
                    }
                } elseif (null === $rowSku) {
                    $this->_rowsToSkip[$rowNum] = true;
                    continue; // skip rows when SKU is NULL
                } elseif (self::SCOPE_STORE == $rowScope) { // set necessary data from SCOPE_DEFAULT row
                    $rowData[self::COL_TYPE]     = $this->_newSku[$rowSku]['type_id'];
                    $rowData['attribute_set_id'] = $this->_newSku[$rowSku]['attr_set_id'];
                    $rowData[self::COL_ATTR_SET] = $this->_newSku[$rowSku]['attr_set_code'];
                }
                
                if (!empty($rowData['_product_websites'])) { // 2. Product-to-Website phase
                    foreach ($rowData['_product_websites'] as $websiteId){
                        $websites[$rowSku][$websiteId] = true;
                    }
                }
                
                if (!empty($rowData[self::COL_CATEGORY])) { // 3. Categories phase
                    if(is_array($rowData[self::COL_CATEGORY])){
                        foreach($rowData[self::COL_CATEGORY] as $category){
                            $categories[$rowSku][$this->_categories[$category]] = true;
                        }
                    }else{
                        $categories[$rowSku][$this->_categories[$rowData[self::COL_CATEGORY]]] = true;
                    }
                }
                
                if (!empty($rowData['_tier_price_website'])) { // 4. Tier prices phase
                    $tierPrices[$rowSku][] = array(
                        'all_groups'        => $rowData['_tier_price_customer_group'] == self::VALUE_ALL,
                        'customer_group_id' => $rowData['_tier_price_customer_group'] == self::VALUE_ALL ?
                                               0 : $rowData['_tier_price_customer_group'],
                        'qty'               => $rowData['_tier_price_qty'],
                        'value'             => $rowData['_tier_price_price'],
                        'website_id'        => self::VALUE_ALL == $rowData['_tier_price_website'] || $priceIsGlobal ?
                                               0 : $this->_websiteCodeToId[$rowData['_tier_price_website']]
                    );
                }
                if(!isset($this->_oldSku[$rowData[self::COL_SKU]])){
                    foreach ($this->_imagesArrayKeys as $imageCol) {
                        if (!empty($rowData[$imageCol])) { // 5. Media gallery phase
                            if (is_array($rowData[$imageCol])){
                                foreach ($rowData[$imageCol] as $key => $image){
                                    if (!array_key_exists($image, $uploadedGalleryFiles)) {
                                        $uploadedGalleryFiles[$image] = $this->_uploadMediaFiles($image);
                                    }
                                    $rowData[$imageCol][$key] = $uploadedGalleryFiles[$image];
                                }
                            }else{
                                if (!array_key_exists($rowData[$imageCol], $uploadedGalleryFiles)) {
                                   $uploadedGalleryFiles[$rowData[$imageCol]] = $this->_uploadMediaFiles($rowData[$imageCol]);
                                }
                                $rowData[$imageCol] = $uploadedGalleryFiles[$rowData[$imageCol]];
                            }
                        }
                    }
                    if (!empty($rowData['_media_image'])) {
                        if(!is_array($rowData['_media_image'])){
                            $rowData['_media_image'] = array($rowData['_media_image']);
                        }
                        foreach($rowData['_media_image'] as $key => $image){
                            $position = is_array($image)
                                ? isset($image[$key])
                                    ? $image[$key]
                                    : 10
                                : $image;
                            $mediaGallery[$rowSku][] = array(
                                'attribute_id'      => $rowData['_media_attribute_id'],
                                'label'             => $rowData['_media_lable'],
                                'position'          => $position,
                                'disabled'          => $rowData['_media_is_disabled'],
                                'value'             => $image
                            );
                        }

                    }
                }else{
                    foreach ($this->_imagesArrayKeys as $imageCol){
                        unset($rowData[$imageCol]);
                    }
                }
                
                if(!empty($rowData['type_ids'])){
                    $typeData[$rowSku] =  $rowData['type_ids'];
                }

                if(!empty($rowData['data_id'])){
                    $dataIds[$rowSku] =  $rowData['data_id'];
                }
                
                
                $rowStore = self::SCOPE_STORE == $rowScope ? $this->_storeCodeToId[$rowData[self::COL_STORE]] : 0;
                $rowData  = $this->_productTypeModels[$rowData[self::COL_TYPE]]->prepareAttributesForSave($rowData);
                $product  = Mage::getModel('importexport/import_proxy_product', $rowData);

                
                foreach ($rowData as $attrCode => $attrValue) {
                    
                    $attribute = $resource->getAttribute($attrCode);
                    $attrId    = $attribute->getId();
                    $backModel = $attribute->getBackendModel();
                    $attrTable = $attribute->getBackend()->getTable();
                    $storeIds  = array(0);

                    if ('datetime' == $attribute->getBackendType()) {
                        $attrValue = gmstrftime($strftimeFormat, strtotime($attrValue));
                    } elseif ($backModel) {
                        $attribute->getBackend()->beforeSave($product);
                        $attrValue = $product->getData($attribute->getAttributeCode());
                    }
                    if (self::SCOPE_STORE == $rowScope) {
                        if (self::SCOPE_WEBSITE == $attribute->getIsGlobal()) {
                            // check website defaults already set
                            if (!isset($attributes[$attrTable][$rowSku][$attrId][$rowStore])) {
                                $storeIds = $this->_storeIdToWebsiteStoreIds[$rowStore];
                            }
                        } elseif (self::SCOPE_STORE == $attribute->getIsGlobal()) {
                            $storeIds = array($rowStore);
                        }
                    }
                    foreach ($storeIds as $storeId) {
                        $attributes[$attrTable][$rowSku][$attrId][$storeId] = $attrValue;
                    }
                    $attribute->setBackendModel($backModel); // restore 'backend_model' to avoid 'default' setting
                }
            }
            
            $this->_saveProductEntity($entityRowsIn, $entityRowsUp)
                ->_saveProductTypes($typeData)
                ->_saveProductDataLinks($dataIds)
                ->_saveProductIndex($entityRowsIn, $entityRowsUp)
                ->_saveProductWebsites($websites)
                ->_saveProductCategories($categories)
                ->_saveProductTierPrices($tierPrices)
                ->_saveMediaGallery($mediaGallery)
                ->_saveProductAttributes($attributes);
        }
        
        $this->_entityRowsInCount = $productsQty === null ? 0 : $productsQty;
        
        return $this;
    }
    
    public function getUpdatedRowsCount()
    {
        return $this->_entityRowsUpCount;
    }
    
    
    public function getImportedRowsCount()
    {
        return $this->_entityRowsInCount;        
    }
    
    /**
     * Update and insert data in entity table.
     *
     * @tutorial added td_art_id attribute handling
     * @param array $entityRowsIn Row for insert
     * @param array $entityRowsUp Row for update
     * @return Mage_ImportExport_Model_Import_Entity_Product
     * 
     */
    protected function _saveProductEntity(array $entityRowsIn, array $entityRowsUp)
    {
        static $entityTable = null;

        if (!$entityTable) {
            $entityTable = Mage::getModel('importexport/import_proxy_product_resource')->getEntityTable();
        }
        if ($entityRowsUp) {
            $this->_connection->insertOnDuplicate(
                $entityTable,
                $entityRowsUp,
                $this->_staticAttributesToUpdate
            );
        }
        if ($entityRowsIn) {
            $this->_connection->insertMultiple($entityTable, $entityRowsIn);

            $newProducts = $this->_connection->fetchPairs($this->_connection->select()
                ->from($entityTable, array('sku', 'entity_id'))
                ->where('sku IN (?)', array_keys($entityRowsIn))
            );
            foreach ($newProducts as $sku => $newId) { // fill up entity_id for new products
                $this->_newSku[$sku]['entity_id'] = $newId;
            }
        }
        return $this;
    }

    protected function _saveProductTypes(array $typeData)
    {
        if(!empty($typeData)){
            $productTypes = array();
            foreach($typeData as $sku => $typeIds){
                $productId = isset($this->_newSku[$sku]) 
                        ? $this->_newSku[$sku]['entity_id']
                        : $this->_oldSku[$sku]['entity_id'];           
                foreach($typeIds as $typeId){
                    $productTypes[] = array('product_id' => $productId, 
                                                'type_id' => $typeId);
                }
            }
            $resource = Mage::getSingleton('core/resource');
            $tableName = $resource->getTableName('magedoc/type_product');
        
            try {
                $this->_connection->insertOnDuplicate($tableName, $productTypes, array());
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        return $this;
    }

    protected function _saveProductDataLinks(array $dataIds)
    {
        if(!empty($dataIds)){
            $productIds = array();
            foreach($dataIds as $sku => $dataId){
                $productId = isset($this->_newSku[$sku])
                    ? $this->_newSku[$sku]['entity_id']
                    : $this->_oldSku[$sku]['entity_id'];
                if (!is_array($dataId)) {
                    $dataId = array();
                }
                foreach ($dataId as $id){
                    $productIds[] = array(
                        'product_id' => $productId,
                        'data_id' => $id);
                }
            }
            $resource = Mage::getSingleton('core/resource');
            $offersTableName = $resource->getTableName('magedoc/import_retailer_data');

            try {
                $this->_connection->insertOnDuplicate($offersTableName, $productIds, array('product_id'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        return $this;
    }

    protected function _saveProductIndex(array $entityRowsIn, array $entityRowsUp)
    {
        /**
         * @todo: Implement DirectoryCatalog Product Index update
         */
        return $this;
    }

    public function isAttributeValid($attrCode, array $attrParams, array $rowData, $rowNum)
    {
        if (($attrParams['type'] == 'select' || $attrParams['type'] == 'multiselect')
            && !$attrParams['is_static']){
            if ($rowData[$attrCode]
                && (is_array($rowData[$attrCode])
                    || !isset($attrParams['options'][mb_strtolower($rowData[$attrCode], 'UTF-8')])))
            {
//                if (!empty($attrParams['options'])){
//                    print_r($attrParams['options']);die;
//                }
                $attribute = Mage::getSingleton('importexport/import_proxy_product_resource')
                    ->getAttribute($attrCode);
                if ($option = $this->_importAttributeOption($attribute, $rowData[$attrCode])){
                    $attrParams['options'][mb_strtolower($rowData[$attrCode], 'UTF-8')] = $option->getId();
                    $this->_productTypeModels['spare']->addAttributeParams($rowData[self::COL_ATTR_SET], $attrParams);
                }
            }
        }
        return $this->_isAttributeValid($attrCode, $attrParams, $rowData, $rowNum);
    }

    public function _isAttributeValid($attrCode, array $attrParams, array $rowData, $rowNum)
    {
        switch ($attrParams['type']) {
            case 'varchar':
                $val   = Mage::helper('core/string')->cleanString($rowData[$attrCode]);
                $valid = Mage::helper('core/string')->strlen($val) < self::DB_MAX_VARCHAR_LENGTH;
                break;
            case 'decimal':
                $val   = trim($rowData[$attrCode]);
                $valid = (float)$val == $val;
                break;
            case 'select':
            case 'multiselect':
                $valid = isset($attrParams['options'][mb_strtolower($rowData[$attrCode], 'UTF-8')]);
                break;
            case 'int':
                $val   = trim($rowData[$attrCode]);
                $valid = (int)$val == $val;
                break;
            case 'datetime':
                $val   = trim($rowData[$attrCode]);
                $valid = strtotime($val) !== false
                    || preg_match('/^\d{2}.\d{2}.\d{2,4}(?:\s+\d{1,2}.\d{1,2}(?:.\d{1,2})?)?$/', $val);
                break;
            case 'text':
                $val   = Mage::helper('core/string')->cleanString($rowData[$attrCode]);
                $valid = Mage::helper('core/string')->strlen($val) < self::DB_MAX_TEXT_LENGTH;
                break;
            default:
                $valid = true;
                break;
        }

        if (!$valid) {
            $this->addRowError(Mage::helper('importexport')->__("Invalid value for '%s'"), $rowNum, $attrCode);
        } elseif (!empty($attrParams['is_unique'])) {
            if (isset($this->_uniqueAttributes[$attrCode][$rowData[$attrCode]])) {
                $this->addRowError(Mage::helper('importexport')->__("Duplicate Unique Attribute for '%s'"), $rowNum, $attrCode);
                return false;
            }
            $this->_uniqueAttributes[$attrCode][$rowData[$attrCode]] = true;
        }
        return (bool) $valid;
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
            Mage::getSingleton('magedoc/import_proxy_product_resource')->saveAttributeOption($attribute);
        } catch (Exception $e) {
            Mage::logException($e);
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

    /**
     * Returns attributes all values in label-value or value-value pairs form. Labels are lower-cased.
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param array $indexValAttrs OPTIONAL Additional attributes' codes with index values.
     * @return array
     */
    public function getAttributeOptions(Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $indexValAttrs = array())
    {
        $options = array();

        if ($attribute->usesSource()) {
            // merge global entity index value attributes
            $indexValAttrs = array_merge($indexValAttrs, $this->_indexValueAttributes);

            // should attribute has index (option value) instead of a label?
            $index = in_array($attribute->getAttributeCode(), $indexValAttrs) ? 'value' : 'label';

            // only default (admin) store values used
            $attribute->setStoreId(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);

            try {
                foreach ($attribute->getSource()->getAllOptions(false) as $option) {
                    $value = is_array($option['value']) ? $option['value'] : array($option);
                    foreach ($value as $innerOption) {
                        if (strlen($innerOption['value'])) { // skip ' -- Please Select -- ' option
                            $options[mb_strtolower($innerOption[$index], 'UTF-8')] = $innerOption['value'];
                        }
                    }
                }
            } catch (Exception $e) {
                // ignore exceptions connected with source models
            }
        }
        return $options;
    }

    /**
     * Save product media gallery.
     *
     * @param array $mediaGalleryData
     * @return Mage_ImportExport_Model_Import_Entity_Product
     */
    protected function _saveMediaGallery(array $mediaGalleryData)
    {
        if (empty($mediaGalleryData)) {
            return $this;
        }

        static $mediaGalleryTableName = null;
        static $mediaValueTableName = null;
        static $productId = null;

        if (!$mediaGalleryTableName) {
            $mediaGalleryTableName = Mage::getModel('importexport/import_proxy_product_resource')
                ->getTable('catalog/product_attribute_media_gallery');
        }

        if (!$mediaValueTableName) {
            $mediaValueTableName = Mage::getModel('importexport/import_proxy_product_resource')
                ->getTable('catalog/product_attribute_media_gallery_value');
        }

        $productIds = array();
        $valuesToInsert = array();
        $insertedGalleryImgs = array();

        foreach ($mediaGalleryData as $productSku => $mediaGalleryRows) {
            $productId = $this->_newSku[$productSku]['entity_id'];
            $productIds[$productId] = $productId;
            $insertedGalleryImgs[$productSku] = array();

            foreach ($mediaGalleryRows as $key => $insertValue) {
                $value = $insertValue['value'];
                if (!isset($insertedGalleryImgs[$productSku][$value])) {
                    $valueArr = array(
                        'attribute_id' => $insertValue['attribute_id'],
                        'entity_id'    => $productId,
                        'value'        => $value
                    );

                    $valuesToInsert []= $valueArr;

                    $insertedGalleryImgs[$productSku][$value] = $key;
                }
            }
        }

        if (Mage_ImportExport_Model_Import::BEHAVIOR_APPEND != $this->getBehavior()) {
            $this->_connection->delete(
                $mediaGalleryTableName,
                $this->_connection->quoteInto('entity_id IN (?)', array_keys($productIds))
            );
        }

        $this->_connection
            ->insertOnDuplicate($mediaGalleryTableName, $valuesToInsert, array('entity_id'));

        $newMediaValues = $this->_connection->fetchPairs($this->_connection->select()
                ->from($mediaGalleryTableName, array(new Zend_Db_Expr('CONCAT(entity_id, "_", value)'), 'value_id'))
                ->where('entity_id IN (?)', array_keys($productIds))
        );

        $valuesToInsert = array();

        foreach ($mediaGalleryData as $productSku => $mediaGalleryRows) {
            $productId = $this->_newSku[$productSku]['entity_id'];

            foreach ($mediaGalleryRows as $insertValue) {

                $valueKey = $productId . '_' . $insertValue['value'];
                if (array_key_exists($valueKey, $newMediaValues)) {
                    $insertValue['value_id'] = $newMediaValues[$valueKey];

                    $valueArr = array(
                        'value_id' => $insertValue['value_id'],
                        'store_id' => Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID,
                        'label'    => $insertValue['label'],
                        'position' => $insertValue['position'],
                        'disabled' => $insertValue['disabled']
                    );
                    $valuesToInsert [] = $valueArr;
                }
            }
        }

        try {
            $this->_connection
                ->insertOnDuplicate($mediaValueTableName, $valuesToInsert, array('value_id'));
        } catch (Exception $e) {
            $this->_connection->delete(
                $mediaGalleryTableName, $this->_connection->quoteInto('value_id IN (?)', $newMediaValues)
            );
            Mage::logException($e);
        }

        return $this;
    }

    /**
     * Validate data row.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return boolean
     */
    public function validateRow(array $rowData, $rowNum)
    {
        static $sku = null; // SKU is remembered through all product rows

        if (isset($this->_validatedRows[$rowNum])) { // check that row is already validated
            return !isset($this->_invalidRows[$rowNum]);
        }
        $this->_validatedRows[$rowNum] = true;

        if (isset($this->_newSku[$rowData[self::COL_SKU]])) {
            $this->addRowError(self::ERROR_DUPLICATE_SKU, $rowNum);
            return false;
        }
        $rowScope = $this->getRowScope($rowData);

        // BEHAVIOR_DELETE use specific validation logic
        if (Mage_ImportExport_Model_Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            if (self::SCOPE_DEFAULT == $rowScope && !isset($this->_oldSku[$rowData[self::COL_SKU]])) {
                $this->addRowError(self::ERROR_SKU_NOT_FOUND_FOR_DELETE, $rowNum);
                return false;
            }
            return true;
        }
        // common validation
        $this->_isProductWebsiteValid($rowData, $rowNum);
        $this->_isProductCategoryValid($rowData, $rowNum);
        $this->_isTierPriceValid($rowData, $rowNum);
        $this->_isGroupPriceValid($rowData, $rowNum);
        $this->_isSuperProductsSkuValid($rowData, $rowNum);

        if (self::SCOPE_DEFAULT == $rowScope) { // SKU is specified, row is SCOPE_DEFAULT, new product block begins
            $this->_processedEntitiesCount ++;

            $sku = $rowData[self::COL_SKU];

            // Adding normalized SKU alias
            $normalizedSku = $this->normalizeCode($sku);
            if ($sku != $normalizedSku
                && !isset($this->_oldSku[$sku])
                && isset($this->_oldSku[$normalizedSku])
            ) {
                $this->_oldSku[$sku] = & $this->_oldSku[$normalizedSku];
            }

            if (isset($this->_oldSku[$sku])) { // can we get all necessary data from existant DB product?
                // check for supported type of existing product
                if (isset($this->_productTypeModels[$this->_oldSku[$sku]['type_id']])) {
                    $this->_newSku[$sku] = array(
                        'entity_id'     => $this->_oldSku[$sku]['entity_id'],
                        'type_id'       => $this->_oldSku[$sku]['type_id'],
                        'attr_set_id'   => $this->_oldSku[$sku]['attr_set_id'],
                        'attr_set_code' => $this->_attrSetIdToName[$this->_oldSku[$sku]['attr_set_id']]
                    );
                } else {
                    $this->addRowError(self::ERROR_TYPE_UNSUPPORTED, $rowNum);
                    $sku = false; // child rows of legacy products with unsupported types are orphans
                }
            } else { // validate new product type and attribute set
                if (!isset($rowData[self::COL_TYPE])
                    || !isset($this->_productTypeModels[$rowData[self::COL_TYPE]])
                ) {
                    $this->addRowError(self::ERROR_INVALID_TYPE, $rowNum);
                } elseif (!isset($rowData[self::COL_ATTR_SET])
                    || !isset($this->_attrSetNameToId[$rowData[self::COL_ATTR_SET]])
                ) {
                    $this->addRowError(self::ERROR_INVALID_ATTR_SET, $rowNum);
                } elseif (!isset($this->_newSku[$sku])) {
                    $this->_newSku[$sku] = array(
                        'entity_id'     => null,
                        'type_id'       => $rowData[self::COL_TYPE],
                        'attr_set_id'   => $this->_attrSetNameToId[$rowData[self::COL_ATTR_SET]],
                        'attr_set_code' => $rowData[self::COL_ATTR_SET]
                    );
                }
                if (isset($this->_invalidRows[$rowNum])) {
                    // mark SCOPE_DEFAULT row as invalid for future child rows if product not in DB already
                    $sku = false;
                }
            }
        } else {
            if (null === $sku) {
                $this->addRowError(self::ERROR_SKU_IS_EMPTY, $rowNum);
            } elseif (false === $sku) {
                $this->addRowError(self::ERROR_ROW_IS_ORPHAN, $rowNum);
            } elseif (self::SCOPE_STORE == $rowScope && !isset($this->_storeCodeToId[$rowData[self::COL_STORE]])) {
                $this->addRowError(self::ERROR_INVALID_STORE, $rowNum);
            }
        }
        if (!isset($this->_invalidRows[$rowNum])
            && !isset($this->_oldSku[$sku])) {
            // set attribute set code into row data for followed attribute validation in type model
            $rowData[self::COL_ATTR_SET] = $this->_newSku[$sku]['attr_set_code'];

            $rowAttributesValid = $this->_productTypeModels[$this->_newSku[$sku]['type_id']]->isRowValid(
                $rowData, $rowNum, !isset($this->_oldSku[$sku])
            );
            if (!$rowAttributesValid && self::SCOPE_DEFAULT == $rowScope && !isset($this->_oldSku[$sku])) {
                $sku = false; // mark SCOPE_DEFAULT row as invalid for future child rows if product not in DB already
            }
        }
        return !isset($this->_invalidRows[$rowNum]);
    }

    public function normalizeCode( $code )
    {
        return mb_strtolower(Mage::helper('magedoc')->normalizeCode($code), 'UTF-8');
    }

    public function setDirectory($directory)
    {
        $this->_directory = $directory;
        return $this;
    }

    /**
     * @return Testimonial_MageDoc_Model_Directory_Abstract
     */

    public function getDirectory()
    {
        return isset($this->_directory)
            ? $this->_directory
            : Mage::registry('magedoc_directory');
    }

    /**
     * Removes empty keys in case value is null or empty string
     *
     * @param array $rowData
     */
    protected function _filterRowData(&$rowData)
    {
        $rowData = array_filter($rowData, array($this, '_validateRowDataValue'));
        // Exceptions - for sku - put them back in
        if (!isset($rowData[self::COL_SKU])) {
            $rowData[self::COL_SKU] = null;
        }
    }

    protected function _validateRowDataValue($value)
    {
        return is_array($value) || strlen($value);
    }
}
