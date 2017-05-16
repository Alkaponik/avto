<?php

class Testimonial_FlatCatalog_Model_Resource_Product extends Mage_Core_Model_Resource_Db_Abstract
{
    protected $_attributesByCode;

    /**
     * Initialize resource
     */
    /*
    public function __construct()
    {
        parent::__construct();
        $this->setType(Mage_RxCatalog_Model_Product::ENTITY)
             ->setConnection('catalog_read', 'catalog_write');
    }
     * 
     */

    protected function _construct()
    {
        $this->_init('flatcatalog/product', 'data_id');
    }

    /**
     * Default product attributes
     *
     * @return array
     */
    protected function _getDefaultAttributes()
    {
        return array('entity_id', 'entity_type_id', 'attribute_set_id', 'type_id', 'created_at', 'updated_at');
    }

    /**
     * Retrieve product website identifiers
     *
     * @param Mage_Catalog_Model_Product|int $product
     * @return array
     */
    public function getWebsiteIds($product)
    {
        return array();
    }

    /**
     * Retrieve product website identifiers by product identifiers
     *
     * @param   array $productIds
     * @return  array
     */
    public function getWebsiteIdsByProductIds($productIds)
    {
        $productsWebsites = array();

        return $productsWebsites;
    }

    /**
     * Retrieve product category identifiers
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getCategoryIds($product)
    {
        return array();
    }

    /**
     * Get product identifier by sku
     *
     * @param string $sku
     * @return int|false
     */
    public function getIdBySku($sku)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from($this->getEntityTable(), 'entity_id')
            ->where('sku = :sku');

        $bind = array(':sku' => (string)$sku);

        return $adapter->fetchOne($select, $bind);
    }

    /**
     * Process product data before save
     *
     * @param Varien_Object $object
     * @return Mage_Catalog_Model_Resource_Product
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        /**
         * Try detect product id by sku if id is not declared
         */
        if (!$object->getId() && $object->getSku()) {
            $object->setId($this->getIdBySku($object->getSku()));
        }

        /**
         * Check if declared category ids in object data.
         */
        if ($object->hasCategoryIds()) {
            $categoryIds = Mage::getResourceSingleton('catalog/category')->verifyIds(
                $object->getCategoryIds()
            );
            $object->setCategoryIds($categoryIds);
        }

        return parent::_beforeSave($object);
    }

    /**
     * Save data related with product
     *
     * @param Varien_Object $product
     * @return Mage_Catalog_Model_Resource_Product
     */
    protected function _afterSave(Mage_Core_Model_Abstract $product)
    {
        return parent::_afterSave($product);
    }

    /**
     * Refresh Product Enabled Index
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Resource_Product
     */
    public function refreshIndex($product)
    {
        return $this;
    }

    /**
     * Get collection of product categories
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getCategoryCollection($product)
    {
        return null;
    }

    /**
     * Retrieve category ids where product is available
     *
     * @param Mage_Catalog_Model_Product $object
     * @return array
     */
    public function getAvailableInCategories($object)
    {
        return array();
    }

    /**
     * Get default attribute source model
     *
     * @return string
     */
    public function getDefaultAttributeSourceModel()
    {
        return 'eav/entity_attribute_source_table';
    }

    /**
     * Check availability display product in category
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int $categoryId
     * @return string
     */
    public function canBeShowInCategory($product, $categoryId)
    {
        return array();
    }

    /**
     * Get SKU through product identifiers
     *
     * @param  array $productIds
     * @return array
     */
    public function getProductsSku(array $productIds)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('flatcatalog/product'), array('entity_id', 'sku'))
            ->where('entity_id IN (?)', $productIds);
        return $this->_getReadAdapter()->fetchAll($select);
    }

    /**
     * Retrieve product entities info
     *
     * @param  array|string|null $columns
     * @return array
     */
    public function getProductEntitiesInfo($columns = null)
    {
        if (!empty($columns) && is_string($columns)) {
            $columns = array($columns);
        }
        if (empty($columns) || !is_array($columns)) {
            $columns = $this->_getDefaultAttributes();
        }

        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getTable('flatcatalog/product'), $columns);

        return $adapter->query($select);

        return $adapter->fetchAll($select);
    }

    public function getAttribute()
    {
        return null;
    }

    public function createMainTable($installer = null, $tableName = null)
    {
        if (is_null($installer)){
            $installer = new Mage_Catalog_Model_Resource_Setup('flatcatalog_setup');
        }
        $connection = $installer->getConnection();
        if (is_null($connection)){
            $connection = $this->_getWriteAdapter();
        }
        if (is_null($tableName)){
            $tableName = $this->getTable('flatcatalog/product');
        }
        $table = $connection
            ->newTable($tableName)
            ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                ), 'Entity ID')
            ->addColumn('type_id', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
                'nullable'  => false,
                'default'   => Mage_Catalog_Model_Product_Type::DEFAULT_TYPE,
            ), 'Type ID')
            ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(
                ), 'SKU')
            ->addColumn('has_options', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                'nullable'  => false,
                'default'   => '0',
                ), 'Has Options')
            ->addColumn('required_options', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                ), 'Required Options')
            ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
                ), 'Creation Time')
            ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
                ), 'Update Time')
            ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
                ), 'Name')
            ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
                ), 'Description')
            ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                'nullable'  => false,
                'default'   => '0',
                ), 'Status')
            ->addColumn('pzn', Varien_Db_Ddl_Table::TYPE_TEXT, 8, array(
                ), 'PZN')
            ->addColumn('ean', Varien_Db_Ddl_Table::TYPE_TEXT, 13, array(
                ), 'EAN')
            ->addColumn('release_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
                ), 'Release Date')
            ->addColumn('contents_value', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(
                ), 'Contents Value')
            ->addColumn('contents_entity', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(
                ), 'Contents Entity')
            ->addColumn('limit_entity', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(
                ), 'Limit Entity')
            ->addColumn('manufacturer_code', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                ), 'Manufacturer')
            ->addColumn('is_cooled', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                'nullable'  => false,
                'default'   => '0',
                ), 'Is Cooled')
            ->addColumn('content_package_type', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                ), 'Content Package Type')
            ->addColumn('A01KAEP', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
                ), 'A01KAEP')
            ->addColumn('A01APU', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
                ), 'A01APU')
            ->addColumn('A01AMPVSGB', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
                ), 'A01AMPVSGB')
            ->addColumn('A01AMPVAMG', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
                ), 'A01AMPVAMG')
            ->addColumn('A01AEP', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
                ), 'A01AEP')
            ->addColumn('A01AVP', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
                ), 'A01AVP')
            ->addColumn('A01ZBV', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
                ), 'A01ZBV')
            ->addColumn('A01MWST', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
                ), 'A01MWST')
            ->addColumn('A01RB130B', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
                ), 'A01RB130B')
            ->addColumn('A01AB130A2', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
                ), 'A01AB130A2')
            ->addIndex($installer->getIdxName('flatcatalog/product', array('sku')),
                array('sku'))
            ->addIndex($installer->getIdxName('flatcatalog/product', array('pzn')),
                array('pzn'))
            ->addIndex($installer->getIdxName('flatcatalog/product', array('ean')),
                array('ean'))
            ->setComment('RxCatalog Product Table');

        $connection->createTable($table);

        return $table;
    }

    public function updateAttributes($productIds, $attributes, $storeId = null)
    {
        $write = $this->_getWriteAdapter();
        $query = "UPDATE {$this->getMainTable()} SET ";
        $setStatement = array();
        foreach ($attributes as $attributeCode => $value){
            $setStatement[] = $write->quoteInto("{$attributeCode} =  ?", $value);
        }
        $query .= implode(', ', $setStatement).
                ' WHERE '.
                $write->quoteInto("{$this->getIdFieldName()} IN (?)", $productIds);
           
        $write->query($query);
        return $this;
    }

    public function getAttributesByCode()
    {
        if (!isset($this->_attributesByCode)){

        }
        return $this->_attributesByCode;
    }

    public function updateFinalPrice($productId, $finalPrice)
    {
        $adapter = $this->_getReadAdapter();
        $sql = "UPDATE `{$this->getTable('flatcatalog/product')}`
                    SET `final_price` = {$finalPrice} WHERE data_id = {$productId}";
        $adapter->query($sql);
        return $this;
    }

    public function updateFinalPriceFromBunch( $bunch )
    {
        $helper = Mage::getResourceHelper('magedoc_system');
        $helper->insertMultipleOnDuplicate( $this->getTable('flatcatalog/product'), $bunch, array('final_price') );

        return $this;
    }

    public function loadAllAttributes()
    {
        return $this;
    }

    public function getSortedAttributes()
    {
        return array();
    }
}