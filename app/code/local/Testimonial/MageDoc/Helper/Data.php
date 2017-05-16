<?php
class Testimonial_MageDoc_Helper_Data extends Mage_Core_Helper_Abstract
{
    const PRODUCTION_START_YEAR = 1950;
    const PRICE_IMPORT_ERROR_LIMIT = 100;
    const RETAILER_MODEL_DEFAULT = 'default';
    const DEFAULT_ADMIN_USER_ID = 1;
    const DEFAULT_IMPORT_STORE = 1;
    const DEFAULT_IMPORT_WEBSITE = 1;
    const DEFAULT_COMBOBOX_SELECT_SIZE = 10;
    const DEFAULT_SUPPLIED_RETENTION = 1;
    const DEFAULT_VEHICLE_SAVE_RULE = 'registration_order_placement';
    const DEFAULT_MAX_COUNT_SESSION_TYPE_IDS_DISPLAYED = 5;
    const DEFAULT_DIRECTORY_CODE = 'tecdoc';
    const CONFIG_XML_PATH_LNG_ID = 'magedoc/general/lng_id';
    const CONFIG_XML_PATH_DISPLAYED_TYPES_COUNT = 'magedoc/layered_navigation_rules/dispalyed_types_count';
    const CONFIG_XML_PATH_ASSEMBLE_ORDER_STATUSES = 'magedoc/supply_management/assemble_order_statuses';
    const CONFIG_XML_PATH_ASSEMBLE_ORDER_SUPPLY_STATUSES = 'magedoc/supply_management/assemble_order_supply_statuses';
    const CONFIG_XML_PATH_TECDOC_TABLE_PREFIX = 'global/magedoc/directory/tecdoc/table_prefix';
    const CONFIG_XML_PATH_TECDOC_TABLE_SUFFIX = 'global/magedoc/directory/tecdoc/table_suffix';
    const CONFIG_XML_PATH_STR_ROOT_CATEGORY = 'magedoc/general/search_tree_root_category_id';
    const CONFIG_XML_PATH_TYPE_TOP_CATEGORIES = 'magedoc/general/type_top_categories';
    const CONFIG_XML_PATH_TYPE_TOP_CATEGORY_LEVEL = 'magedoc/general/type_top_category_level';
    const CONFIG_XML_PATH_IMPORT_FLUSH_LIMIT = 'magedoc/import/flush_limit';

    protected $_stripCodeChars = array( "\t", ' ', '+', '.', ',', '-', '=', '\\', '/', '\'', '"', ')', '(', ']', '[', '_');
    protected $_retailerModels = array();
    protected $_adminUser;
    
    public function getYearStart()
    {
        return self::PRODUCTION_START_YEAR;
    }
    
    public function readDir($dir)
    {
        $dh  = opendir($dir); 
        $files = array();
        while(false !== ($filename = readdir($dh))) 
        { 
            if(is_file($dir.$filename)){
                $files[$filename]= $filename;                 
            }    
        }
        
        return $files;
    }
    
    public function getRetailerImportModel($retailerId)
    {
        if(empty($this->_retailerModels)){
            $this->_retailerModels = Mage::getModel('magedoc/source_retailer_data_import_model')->toOptionArray();
        }
        $retailer = Mage::getModel('magedoc/retailer')->load($retailerId);
        if($retailer->getId() !== null){
            $model = $this->_retailerModels[$retailer->getModel()]['model'];
        }else{
            $model = $this->_retailerModels[self::RETAILER_MODEL_DEFAULT]['model'];
        }
        
        return $model;
    }
    
    public function transliterationString($string)
    {
        $trans = array("а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d","е"=>"e", "ё"=>"yo","ж"=>"j","з"=>"z","и"=>"i","й"=>"i","к"=>"k","л"=>"l", "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r","с"=>"s","т"=>"t", "у"=>"y","ф"=>"f","х"=>"h","ц"=>"c","ч"=>"ch", "ш"=>"sh","щ"=>"sh","ы"=>"i","э"=>"e","ю"=>"u","я"=>"ya",
            "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G","Д"=>"D","Е"=>"E", "Ё"=>"Yo","Ж"=>"J","З"=>"Z","И"=>"I","Й"=>"I","К"=>"K", "Л"=>"L","М"=>"M","Н"=>"N","О"=>"O","П"=>"P", "Р"=>"R","С"=>"S","Т"=>"T","У"=>"Y","Ф"=>"F", "Х"=>"H","Ц"=>"C","Ч"=>"Ch","Ш"=>"Sh","Щ"=>"Sh", "Ы"=>"I","Э"=>"E","Ю"=>"U","Я"=>"Ya",
            "ь"=>"","Ь"=>"","ъ"=>"","Ъ"=>"", " "=>"_");
        $string = strtr($string, $trans);
        return $string;
        
    }
    
    public function getProductionPeriod($type, $entity = 'typ')
    {
        $yearStart=substr($type->getData($entity.'_pcon_start'),0,4);
		$yearEnd=substr($type->getData($entity.'_pcon_end'),0,4);
		$monthStart=substr($type->getData($entity.'_pcon_start'),4);
		$monthEnd=substr($type->getData($entity.'_pcon_end'),4);
				
		$period=$yearStart."/".$monthStart."-";
				
		if(!$yearEnd){
            $period .= $this->__('present');
        }else{  
            $period .= $yearEnd."/".$monthEnd." ";
        }
        
        return $period;
    }
    
     public function getModelPeriod($model)
    {
        $myearstart=substr($model->getModPconStart(),0,4);
		$myearend=substr($model->getModPconEnd(),0,4);
		$monthstart=substr($model->getModPconStart(),4);
		$monthend=substr($model->getModPconEnd(),4);
				
		$period=$myearstart."/".$monthstart."-"; 
				
		if($myearend == NULL){
            $period=$period.$this->__('present'); 
        }else{  
            $period=$period.$myearend."/".$monthend." ";
        }
        
        return $period;
    }
    
    public function getSearchTreeRootCategoryId($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_STR_ROOT_CATEGORY, $storeId);
    }

    public function getTypeTopCategories($storeId = null)
    {
        $categories = Mage::getStoreConfig(self::CONFIG_XML_PATH_TYPE_TOP_CATEGORIES, $storeId);
        return $categories
            ? explode(',', $categories)
            : array();
    }

    public function getTypeTopCategoryLevel($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_TYPE_TOP_CATEGORY_LEVEL, $storeId);
    }

    
    public function getProductionStartYear($storeId = null)
    {
        if(!$producttionStartYear = Mage::getStoreConfig('magedoc/general/production_start_year', $storeId)){
            $producttionStartYear = self::PRODUCTION_START_YEAR;
        }
        return $producttionStartYear;
    }

    
    public function getImportErrorLimit($storeId = null)
    {
        if(!$errorLimit = Mage::getStoreConfig('magedoc/import/price_import_error_limit', $storeId)){
            $errorLimit = self::PRICE_IMPORT_ERROR_LIMIT;
        }
        return $errorLimit;
    }

    public function getStoreForImport($storeId = null)
    {
        if(!$importStoreId = Mage::getStoreConfig('magedoc/import/price_import_to_store', $storeId)){
            $importStoreId = self::DEFAULT_IMPORT_STORE;
        }
        return $importStoreId;
    }

    public function getWebsitesToImport($storeId = null)
    {
        if(!$importWebsiteIds = Mage::getStoreConfig('magedoc/import/price_import_to_website', $storeId)){
            $importWebsiteIds = self::DEFAULT_IMPORT_WEBSITE;
        }
        
        return explode(',', $importWebsiteIds);
    }

    public function getCurrentAdminUser($storeId = null)
    {
        if (!isset($this->_adminUser)){
            if (Mage::getSingleton('admin/session')->isLoggedIn())
            {
                $this->_adminUser = Mage::getSingleton('admin/session')->getUser();
            }else{
                $this->_adminUser = Mage::getModel('admin/user')
                    ->load($this->getDefaultAdminUserId($storeId));
            }
        }
        return $this->_adminUser;
    }

    public function getCurrentAdminUserId($storeId = null)
    {
        return $this->getCurrentAdminUser($storeId)->getId();
    }
    
    public function getDefaultAdminUserId($storeId = null)
    {
        if(!$dafaultAdminUserId = Mage::getStoreConfig('magedoc/order_management/default_admin_user_id', $storeId)){
            $dafaultAdminUserId = self::DEFAULT_ADMIN_USER_ID;
        }
        return $dafaultAdminUserId;
    }
    
    public function getAssembleOrderStatuses($storeId = null)
    {
        $statuses = Mage::getStoreConfig(self::CONFIG_XML_PATH_ASSEMBLE_ORDER_STATUSES, $storeId);
        return $statuses ? explode(',', $statuses) : array();
    }

    public function getAssembleOrderSupplyStatuses($storeId = null)
    {
        $statuses = Mage::getStoreConfig(self::CONFIG_XML_PATH_ASSEMBLE_ORDER_SUPPLY_STATUSES, $storeId);
        return $statuses ? explode(',', $statuses) : array();
    }
    
    public function getDefaultComboboxSelectSize()
    {
        return self::DEFAULT_COMBOBOX_SELECT_SIZE;
    }

    public function getImagePathPrefix()
    {
        return 'tecdoc' . DS;
    }
    
    public function getDateWithSuppliedRetention($storeId = null)
    {
        if(!$date = Mage::getStoreConfig('magedoc/supply_management/default_supplied_retention', $storeId)){
            $date = date("Y-m-d", 
                    Mage::getModel('core/date')->timestamp(time() 
                          + 24 * 60 * 60 * self::DEFAULT_SUPPLIED_RETENTION));
        }
        return $date;
    }

    public function getCustomerVehicleSaveRule($storeId = null)
    {
        if(!$rule = Mage::getStoreConfig('magedoc/layered_navigation_rules/vehicle_save_rules', $storeId)){
            $rule = self::DEFAULT_VEHICLE_SAVE_RULE;
        }
        return $rule;
    }

    public function getMaxCountSessionTypeIds($storeId = null)
    {
        if(!$count = Mage::getStoreConfig(self::CONFIG_XML_PATH_DISPLAYED_TYPES_COUNT, $storeId)){
            $count = self::DEFAULT_MAX_COUNT_SESSION_TYPE_IDS_DISPLAYED;
        }
        return $count;
        
    }

    public function getTecDocTablePrefix($storeId = null)
    {
        return (string)Mage::getConfig()->getNode( self::CONFIG_XML_PATH_TECDOC_TABLE_PREFIX );
    }

    public function getTecDocTableSuffix($storeId = null)
    {
        return (string)Mage::getConfig()->getNode( self::CONFIG_XML_PATH_TECDOC_TABLE_SUFFIX );
    }

    public function getItemSupplyDate($item)
    {
        if($item->getData('supply_date') === null){
            //return $this->getDateWithSuppliedRetention();
            $date = $this->getItemDeliveryEstimationDate($item);
        }else{
            $date = Mage::app()->getLocale()->date($item->getData('supply_date'));
        }
        return $date->get(Zend_Date::DATE_MEDIUM);
    }

    public function getItemDeliveryEstimationDate($item)
    {
        $retailer = Mage::helper('magedoc/price')->getRetailerById($item->getRetailerId());
        return $retailer->getSupplyConfig()->getDeliveryEstimationDate();
    }

    public function getLngId($store = null)
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_LNG_ID, $store);
    }

    public function getDefaultRetailerId()
    {
        return 0;
    }

    public function getDefaultDateDisplayFormat($withTime = false)
    {
        $outputFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
        return Varien_Date::convertZendToStrFtime($outputFormat, true, $withTime);
    }

    public function getDefaultDateTimeDisplayFormat()
    {
        $outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        return Varien_Date::convertZendToStrFtime($outputFormat, true, true);
    }

    public function getDefaultDateTime()
    {
        return Mage::app()->getLocale()->date()->toString(
            Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        );
    }

    public function getRetailersSupplyConfigJson()
    {
        return Mage::helper('core')->jsonEncode($this->getRetailersSupplyConfig());
    }

    public function getRetailersSupplyConfig()
    {
        $resource = Mage::getResourceModel('magedoc/retailer_config_supply');
        $adapter = $resource->getReadConnection();
        $select = $adapter->select()->from($resource->getMainTable(), array('retailer_id', 'delivery_type'));
        $config = array();
        $stmt = $adapter->query($select);
        $dummyItem = new Varien_Object();
        while ($row = $stmt->fetch(Zend_Db::FETCH_ASSOC)){
            $config[$row['retailer_id']] = $row;
            $config[$row['retailer_id']]['supply_date'] = $this->getItemSupplyDate($dummyItem->setData($row));
        }
        return $config;
    }

    public function saveStaticAttributes($object, $attributes = array())
    {
        if (!$object->getId()){
            return $this;
        }
        if (!is_array($attributes)){
            $attributes = array($attributes);
        }
        $values = array();
        foreach ($attributes as $attributeCode){
            $values[$attributeCode] = $object->getData($attributeCode);
        }
        $adapter = $object->getResource()->getReadConnection();
        $tableName = $object->getResource() instanceof Mage_Eav_Model_Entity_Abstract
            ? $object->getResource()->getEntityTable()
            : $object->getResource()->getMainTable();
        $adapter->update($tableName, $values, $adapter->quoteInto("{$object->getResource()->getIdFieldName()} = ?", $object->getId()));
        return $this;
    }

    public function normalizeCode( $code )
    {
        return str_replace($this->_stripCodeChars, '', $code);
    }

    public function getPaymentMethodsHash()
    {
        $paymentMethods = array();
        foreach (Mage::helper('payment')->getPaymentMethods() as $code => $data) {
            if (!empty($data['active'])){
                if ((isset($data['title']))) {
                    $paymentMethods[$code] = $data['title'];
                } else {
                    if (Mage::helper('payment')->getMethodInstance($code)) {
                        $paymentMethods[$code] = Mage::helper('payment')->getMethodInstance($code)->getConfigData('title', null);
                    }
                }
            }
        }
        return $paymentMethods;
    }

    public function getImportSourceAdapterModelName($code)
    {
        $valuePath = Testimonial_MageDoc_Model_Source_Import_Model_Adapter::IMPORT_MODEL_CONFIG_PATH . '/' . $code . '/' . 'class';
        return (string) Mage::getConfig()->getNode($valuePath);
    }

    public function getImportParserModelName($code)
    {
        $valuePath = Testimonial_MageDoc_Model_Source_Import_Model_Parser::IMPORT_MODEL_CONFIG_PATH . '/' . $code . '/' . 'class';
        return (string) Mage::getConfig()->getNode($valuePath);
    }

    public function setCurrentManager($object, $storeId = null)
    {
        if (is_null($storeId)){
            $storeId = $object->getStoreId();
        }
        $user = $this->getCurrentAdminUser($storeId);
        $this->setObjectManager($object, $user);
    }

    public function setObjectManager($object, $user)
    {
        $userId = $user->getId();

        if($object->getManagerId() === null){
            $object->setManagerId($userId);
            $object->setManagerName($user->getName());
        }
        $object->setLastManagerId($userId);
        $object->setLastManagerName($user->getName());
    }

    public function getEntityDefaultValues($entity, &$data, $storeId = null)
    {
        foreach ($this->getEntityFieldTemplates($entity, $storeId) as $field => $template){
            $data[$field] = Mage::helper('magedoc_system')->processTemplate($template, $data, $this);
        }
        return $data;
    }

    public function getEntityFieldTemplates($entity, $storeId = null)
    {
        $templates = Mage::app()->getStore($storeId)->getConfig("magedoc/{$entity}_field_templates");
        if (!is_array($templates)){
            return array();
        }
        return $templates;
    }

    public function getEntityFieldTemplate($entity, $field, $storeId = null)
    {
        return Mage::getStoreConfig("magedoc/{$entity}_field_templates/{$field}", $storeId);
    }

    public function getDefaultDirectoryCode()
    {
        $directories = Mage::getConfig()->getNode(Testimonial_MageDoc_Model_Directory::DIRECTORIES_CONFIG_XML_PATH)->asArray();
        return isset($directories[self::DEFAULT_DIRECTORY_CODE])
            ? self::DEFAULT_DIRECTORY_CODE
            : key($directories);
    }

    public function joinProductAttribute($attributeName, $collection = null, $joinTable = 'catalog_product_entity', $joinColumn = null, $storeId = 0, $fieldExpression = null, $columnAlias = null)
    {
        if(is_null($collection)){
            $collection = $this;
        }

        $resource = Mage::getModel('catalog/product')->getResource();
        $attribute = $resource->getAttribute($attributeName);
        $attributeTable = $attribute->getBackendTable();
        if (is_null($columnAlias)){
            $columnAlias = $attributeName;
        }
        $alias = 'at_'.$attributeName.'_'.$columnAlias;

        if ($attribute->isStatic()){
            $columnExpression = new Zend_Db_Expr("(IFNULL({$joinTable}.{$attributeName}, ''))");
        }else{
            $columnExpression = new Zend_Db_Expr("{$alias}.value");
        }

        if (!is_null($fieldExpression)){
            $columnExpression = str_replace('{{field_expression}}', $columnExpression, $fieldExpression);
            $columnExpression = str_replace('{{table_alias}}', $alias, $columnExpression);
        }

        $collection->addFilterToMap($columnAlias, $columnExpression);
        if (is_null($joinColumn)){
            $joinColumn = 'entity_id';
        }

        if($attribute->isStatic()){
            $collection->getSelect()->columns(array($columnAlias => $columnExpression), $joinTable);
        }else{
            $collection->getSelect()->joinLeft(array($alias => $attributeTable),
                "{$alias}.entity_id = {$joinTable}.{$joinColumn}
                         AND {$alias}.attribute_id = {$attribute->getId()}
                         AND {$alias}.store_id = $storeId");
            $collection->getSelect()->columns(array($columnAlias => $columnExpression), $alias);
        }
        return $this;
    }

    public function getOrderStatusChangeReasonLabel($supplyStatus)
    {
        return $this->__((string) Mage::getConfig()
            ->getNode(Testimonial_MageDoc_Model_Source_Order_Reason::STATUS_CHANGE_REASON_PATH . '/'
                . $supplyStatus . "/label"));
    }

    public function getVehicleFormatJs()
    {
        return  Mage::getSingleton('magedoc/customer_vehicle')->getFormatJs();
    }

    public function getShippingDateFormat()
    {
        $outputFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        return $outputFormat;
    }

    public function getImportFlushLimit()
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_IMPORT_FLUSH_LIMIT);
    }
}