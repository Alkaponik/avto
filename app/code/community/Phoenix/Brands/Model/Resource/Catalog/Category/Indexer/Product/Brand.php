<?php

class Phoenix_Brands_Model_Resource_Catalog_Category_Indexer_Product_Brand extends Mage_Index_Model_Resource_Abstract
{
    /**
     * Category table
     *
     * @var string
     */
    protected $_categoryTable;

    /**
     * Category product table
     *
     * @var string
     */
    protected $_categoryProductTable;

    /**
     * Product website table
     *
     * @var string
     */
    protected $_productWebsiteTable;

    /**
     * Store table
     *
     * @var string
     */
    protected $_storeTable;

    /**
     * Group table
     *
     * @var string
     */
    protected $_groupTable;

    /**
     * Array of info about stores
     *
     * @var array
     */
    protected $_storesInfo;

    protected $_brandAttribute;
    protected $_productBrandAttributeTable;

    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('catalog/category_product', 'category_id');
        $this->_categoryNameAttribute = Mage::getSingleton('eav/config')
                ->getAttribute('catalog_category', 'name');
        $this->_categoryTable        = $this->getTable('catalog/category');
        $this->_categoryNameAttributeTable    = $this->_categoryNameAttribute
                                        ->getBackend()
                                        ->getTable();
        $this->_eavAttributeOptionTable = $this->getTable('eav/attribute_option');
        $this->_eavAttributeOptionValueTable = $this->getTable('eav/attribute_option_value');
        $this->_categoryProductTable = $this->getTable('catalog/category_product');
        $this->_categoryProductIdxTable = $this->getTable('catalog/category_product_idx');
        $this->_productTable  = $this->getTable('catalog/product');
        $this->_productWebsiteTable  = $this->getTable('catalog/product_website');
        $this->_storeTable           = $this->getTable('core/store');
        $this->_groupTable           = $this->getTable('core/store_group');
    }
    
    public function reindexAll()
    {
        $hlp = Mage::helper('phoenixbrands');
        $mainTable = $this->getMainTable();
        $idxAdapter = $this->_getIndexAdapter();

        $brandRootCategoryIds = array();
        $idxAdapter->truncateTable($this->_categoryProductIdxTable);

        foreach (Mage::app()->getWebsites() as $website){
            $storeId = $website->getDefaultStore()->getId();
            $brandRootCategoryId = $hlp->getBrandsRootCategoryId($storeId);
            $categoryBrandAttribute = $hlp->getCategoryBrandAttribute($storeId);
            $this->_initBrandAttribute($storeId);
            if (!$hlp->isEnabled($storeId) 
                    || !$brandRootCategoryId
                    || !$this->_brandAttribute){
                continue;
            }

            $source = $this->_brandAttribute->getSource();
        if ($this->_brandAttribute->getSourceModel() == 'eav/entity_attribute_option_collection'){
            $сategoryBrandsSelect = $idxAdapter->select()
                    ->from(array('eao' => $this->_eavAttributeOptionTable),
                            array('brand' => 'option_id'))
                    ->joinInner(array('eaov' => $this->_eavAttributeOptionValueTable),
                                'eaov.option_id = eao.option_id 
                                        AND eaov.store_id = 0',
                                '')
                    ->joinInner(array('ccev' => $this->_categoryNameAttributeTable),
                                sprintf(
                                        'ccev.entity_type_id = %s
                                            AND ccev.entity_id =    cce.entity_id
                                            AND ccev.attribute_id   = %s
                                            AND ccev.store_id = 0
                                            AND eaov.value = ccev.value',
                                        $this->_categoryNameAttribute->getEntityTypeId(),
                                        $this->_categoryNameAttribute->getAttributeId()
                                        ),
                                '')
                    ->where('eao.attribute_id = ?', $this->_brandAttribute->getAttributeId());
        }elseif($source instanceof Testimonial_MageDoc_Model_Source_Supplier){
            $сategoryBrandsSelect = $idxAdapter->select()
                    ->from(array('eao' => Mage::getResourceSingleton('magedoc/supplier')->getMainTable()),
                            array('brand' => 'td_sup_id'))
                    ->joinInner(array('ccev' => $this->_categoryNameAttributeTable),
                                sprintf(
                                        'ccev.entity_type_id = %s
                                            AND ccev.entity_id =    cce.entity_id
                                            AND ccev.attribute_id   = %s
                                            AND ccev.store_id = 0
                                            AND eao.title = ccev.value',
                                        $this->_categoryNameAttribute->getEntityTypeId(),
                                        $this->_categoryNameAttribute->getAttributeId()
                                        ),
                                '');
        }else{
            continue;
        }

            $сategoryBrandsSelect
                ->where('cce.parent_id = ?', $brandRootCategoryId);
            $brandUpdateQuery = $idxAdapter->updateFromSelect($сategoryBrandsSelect,
                array('cce' => $this->_categoryTable));

            //print_r($brandUpdateQuery);die;
            $idxAdapter->query($brandUpdateQuery);

            $attribute = $this->_brandAttribute;

            if ($this->_productBrandAttributeTable){
                $productBrandTableAlias = 'value_default';
                $productBrandField = 'value';
                $select = $idxAdapter->select()
                ->from(array($productBrandTableAlias => $this->_productBrandAttributeTable))
                ->where("$productBrandTableAlias.attribute_id = ", $attribute->getId())
                ->where("$productBrandTableAlias.store_id = ?", Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);

                /** @todo:
                 *  implement store scope values support
                 */
//                if (!$attribute->isScopeGlobal()){
//                     $select->joinLeft(array('value' => $this->_productBrandAttributeTable),
//                            $idxAdapter->quoteInto(
//                                'value.entity_id = value_default.entity_id
//                                    AND value.attribute_id = ?', $attribute->getId())
//                            . $idxAdapter->quoteInto(
//                                ' AND value.store_id = ?', $storeId).
//                            '');
//                }
            }else{
                $productBrandTableAlias = 'main_table';
                $productBrandField = $attribute->getAttributeCode();
                $select = $idxAdapter->select()
                    ->from(array($productBrandTableAlias => $this->_productTable));
            }

            $brandRootCategoryIds[$brandRootCategoryId] = $brandRootCategoryId;
            $select->reset(Zend_Db_Select::COLUMNS);
            $select->reset(Zend_Db_Select::WHERE);
            $select->joinInner(
                    array('cce' => $this->_categoryTable),
                    "$productBrandTableAlias.$productBrandField = cce.{$categoryBrandAttribute->getAttributeCode()}
                     AND cce.parent_id = $brandRootCategoryId",
                    '');

            $select->columns(array('cce.entity_id', "$productBrandTableAlias.entity_id", new Zend_Db_Expr(0)));
            
            //print_r((string)$select);die;

            $query = $idxAdapter->insertFromSelect($select,
                    $this->_categoryProductIdxTable,
                    array('category_id', 'product_id', 'position'),
                    Varien_Db_Adapter_Interface::INSERT_IGNORE);
            $idxAdapter->query($query);
        }
        
        $cleanupSelect = $idxAdapter->select()->from(
                array('catalog_category_product' => $this->_categoryProductTable),
                array('category_id'))
                ->joinInner(
                        array('catalog_category' => $this->_categoryTable),
                        'catalog_category_product.category_id = catalog_category.entity_id');

        foreach ($brandRootCategoryIds as $brandRootCategoryId){
            $cleanupSelect->reset(Zend_Db_Select::WHERE);
            $cleanupSelect->where('catalog_category.parent_id = ?', $brandRootCategoryId);
            $query = $idxAdapter->deleteFromSelect($cleanupSelect, 'catalog_category_product');
            $idxAdapter->query($query);
        }
        
        $idxSelect = $idxAdapter->select()->from(
                array('category_product_idx' => $this->_categoryProductIdxTable),
                array('product_id'));
        $idxSelect->reset(Zend_Db_Select::WHERE);
        $idxSelect->reset(Zend_Db_Select::COLUMNS);
        $idxSelect->columns('*');

        $query = $idxAdapter->insertFromSelect($idxSelect,
                    $this->_categoryProductTable,
                    array('category_id', 'product_id', 'position'),
                    Varien_Db_Adapter_Interface::INSERT_IGNORE);
        $idxAdapter->query($query);
        
        return $this;
    }

    protected function _initBrandAttribute($storeId = null)
    {
        $this->_brandAttribute = null;
        $this->_productBrandAttributeTable = null;
        if ($brandAttribute = Mage::helper('phoenixbrands')->getBrandAttribute($storeId)) {
            if ($brandAttribute){
                $this->_brandAttribute = $brandAttribute;
                $this->_productBrandAttributeTable  = $brandAttribute->isStatic() ? null : $brandAttribute->getBackend()->getTable();
            }
        }
    }
}