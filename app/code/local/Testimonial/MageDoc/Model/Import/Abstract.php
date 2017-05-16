<?php

abstract class Testimonial_MageDoc_Model_Import_Abstract extends Mage_Core_Model_Abstract
{
    const IMPORT_STATUS_IMPORTED = true;
    
    protected $_collection;
    protected $_supplierId;
    protected $_categoryId;
    protected $_isUpdateMode = false;
    protected $_articleIdsForImport = array();
    protected $_importStatus = null;
    protected $_articleCriteriaToImport;
        
    public function getCollection()
    {
        if(!isset($this->_collection)){
            /*
             * @var $collection Testimonial_MageDoc_Model_Mysql4_Import_Retailer_Data_Collection
             */
            $collection = Mage::getResourceModel('magedoc/import_retailer_data_collection');
            $collection->setIdFieldName('art_id');
            $collection->getResource()->setIdFieldName('td_art_id');
            $collection->joinRetailer();
            Mage::getResourceSingleton('magedoc/tecdoc_article_collection')                    
                    ->joinArticles($collection, 'main_table', 
                            array('art_id'=> 'main_table.td_art_id',
                                'art_article_nr'=> 'td_article.ART_ARTICLE_NR',
                                'supplier_id' => "td_article.ART_SUP_ID"), false)
                    ->joinDesignation($collection, 'td_article', 'ART_COMPLETE_DES_ID', '', null, true)
                    ->joinDesignation($collection, 'td_article', 'ART_DES_ID');
            Mage::getResourceSingleton('magedoc/supplier_collection')
                    ->joinSuppliers($collection, 'main_table', array(
                        'supplier'      => 'md_supplier.td_sup_id',
                        'manufacturer'  => 'md_supplier.title',
                        'sku'           => new Zend_Db_Expr("CONCAT(md_supplier.title, 
                                    '-', REPLACE(td_article.ART_ARTICLE_NR, ' ', '') )"),
                        'name'          => new Zend_Db_Expr("
                        IF(art_id IS NULL,
                            main_table.name,
                            CONCAT(
                                IF(des_text_template.text IS NULL,
                                    CONCAT(td_desText.TEX_TEXT, ' ', md_supplier.title,' ', td_article.ART_ARTICLE_NR),
                                    IF(LOCATE('%s', des_text_template.text),
                                        REPLACE(des_text_template.text,
                                            '%s',
                                            CONCAT(md_supplier.title,' ',td_article.ART_ARTICLE_NR)
                                        ),
                                        CONCAT(des_text_template.text, ' ', md_supplier.title,' ',td_article.ART_ARTICLE_NR)
                                    )
                                ),
                                IF(td_desText1.TEX_TEXT IS NOT NULL,
                                    CONCAT(' ',td_desText1.TEX_TEXT),
                                    ''
                                )
                            )
                        )"),
                    ));
            if (!empty($this->_categoryId) || !$this->getIsUpdateMode()) {
                Mage::getResourceSingleton('magedoc/tecdoc_searchTree_collection')
                    ->joinSearchTree($collection, 'td_article', array(
                    'category_id' => new Zend_Db_Expr("GROUP_CONCAT(
                                    catalog_category_entity.entity_id)"),
                    'td_str_id' => "catalog_category_entity.td_str_id",
                ));
            }

            $collection->joinProducts($collection, 'td_article', array(
                    'td_art_id'     => 'catalog_product_entity.td_art_id',
                    'catalog_product_id'    => 'catalog_product_entity.entity_id',
                    'product_price' => 'catalog_product_price.value',
                ));
                        
            $collection->addFilterToMap('name', "CONCAT(td_desText.TEX_TEXT, ' ',
                            md_supplier.title,' ', 
                            td_article.ART_ARTICLE_NR,
                            IF(td_desText1.TEX_TEXT IS NOT NULL,
                                CONCAT(' ',td_desText1.TEX_TEXT),
                                ''))");
            $collection->addFilterToMap('sku', "CONCAT(md_supplier.title, '-',
                            REPLACE(td_article.ART_ARTICLE_NR, ' ', ''))");

            $collection->addFilterToMap('supplier_id', 'main_table.supplier_id');


            $this->_collection = $collection;
            $this->_prepareCollection();   
        }
        return $this->_collection;
    }

    protected function _joinCriteria($collection)
    {
        $criteria = $this->getArticleCriteria();
        $criteriaIds = array_keys($criteria);
        $joinExpression = 'td_article.ART_ID = td_artCriteria.ACR_ART_ID';
        if ($collection->hasJoin('td_linkArtGA')){
            $joinExpression .= ' AND td_artCriteria.ACR_GA_ID = td_linkArtGA.LAG_GA_ID';
        }

        if ($criteriaIds){
            Mage::getResourceSingleton('magedoc/tecdoc_artCriteria_collection')
                ->joinArtCriteria(
                    $collection,
                    $criteriaIds,
                    'td_article',
                    'ART_ID',
                    '',
                    'td_artCriteria',
                    $joinExpression);
            Mage::getResourceSingleton('magedoc/tecdoc_artCriteria_collection')->joinCriteriaDesignation(
                $collection,
                'td_artCriteria',
                array(
                    'criteria_ids' => 'GROUP_CONCAT(td_artCriteria.ACR_CRI_ID)',
                ),
                array(
                    'criteria_value_text' => 'GROUP_CONCAT(IFNULL({{des_text}}.TEX_TEXT, td_artCriteria.ACR_VALUE))',
                ));
        }

        return $this;
    }

    public function getArticleCriteria()
    {
        if (!isset($this->_articleCriteriaToImport)){
            $criteria = Mage::getResourceModel('magedoc/criteria_collection')
                ->addFieldToFilter('is_import_enabled', 1)
                ->addFieldToFilter('attribute_code', array('notnull' => true));
            $select = $criteria->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns(array('td_cri_id', 'attribute_code'));
            $this->_articleCriteriaToImport = $criteria->getConnection()->fetchPairs($select);
            if (!is_array($this->_articleCriteriaToImport)){
                $this->_articleCriteriaToImport = array();
            }
        }
        return $this->_articleCriteriaToImport;
    }

    protected function _prepareCollection()
    {
        $this->_collection->setImportModel($this);
        $this->_collection->getSelect()->reset(Zend_Db_Select::GROUP);        
        return $this;
    }
    
    /*
     *    Prepare data for grid view
     */

    public function getAdditionalData($item)
    {
        $helper = Mage::helper('magedoc/price');
        $retailer = $helper->getRetailerById($item->getRetailerId());
        $discountTable = $retailer->getDiscountTable();
        $marginTable = $retailer->getMarginTable();
        $data = array(
            'cost'          => $item->getCost(),
            'price'         => $item->getPrice(),
            'base_cost'     => $helper->getCost($item),
            'base_price'    => $helper->getPrice($item),
            'price_with_margin' => $retailer->getPriceWithMargin($helper->getCost($item) + $retailer->getFixedFee()),
            'price_with_discount' => $retailer->getPriceWithDiscount($helper->getPrice($item)),
            'discount'      => $helper->getDiscount($helper->getCost($item), $discountTable) / $retailer->getMarginRatio(),
            'margin'        => $helper->getMargin($helper->getPrice($item), $marginTable) *  $retailer->getMarginRatio(),
            'retailer_id'   => $item->getRetailerId(),
            'is_imported'   => $item->getData('catalog_product_id') !== null
                ? 1 : 0,
        );

        $data['final_price'] = $helper->getFinalPrice($data, $discountTable, $marginTable);

        return $data;
    }

    /*
     * Getting/setting params for action(import/update)
     */
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
        $this->_articleIdsForImport = $importIds;
        return $this;
    }
    
    public function setCategoryId($categoryId)
    {
        $this->_categoryId = $categoryId;
        return $this;
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

    public function setIsUpdateMode($isUpdateMode)
    {
        $this->_isUpdateMode = $isUpdateMode;
    }

    public function getIsUpdateMode()
    {
        return $this->_isUpdateMode;
    }
    /*
     *  Prepare data for action(import/update) block
     */    
    public function getBranchCategories()
    {
        $categoryModel = Mage::getModel('catalog/category')->load($this->_categoryId);
        $categoryModel->getPathIds();
        $categoryModel->getAllChildren(true);
        return array_merge($categoryModel->getPathIds(), $categoryModel->getAllChildren(true));
        
    }

    protected function _prepareCollectionForAction($isUpdateMode = null)
    {
        $collection = $this->getCollection();

        if ($isUpdateMode) {
            $collection->getSelect()->where('catalog_product_entity.entity_id IS NOT NULL');
        } else {
            Mage::getResourceSingleton('magedoc/tecdoc_article_collection')
                ->joinGraphics($collection, 'td_article');

            if ($this->getImportStatus() !== null) {
                if ($this->getImportStatus() == self::IMPORT_STATUS_IMPORTED) {
                    $collection->getSelect()->where('catalog_product_entity.entity_id IS NOT NULL');
                } else {
                    $collection->getSelect()->where('catalog_product_entity.entity_id IS NULL');
                }
            }

            $this->_joinCriteria($collection);
        }

        $collection->addEnabledRetailerImportFilter();

        if (!empty($this->_categoryId)){
            $collection->addCategoryFilter(array('in' => $this->getBranchCategories()));
        }

        if (!empty($this->_categoryId) || !$isUpdateMode){
            $collection->addFieldToFilter('catalog_category_entity.is_import_enabled', 1);
        }
        
        if ($supplierId = $this->getSupplierId()) {
            $collection->addSupplierFilter($supplierId);
        }
        
        if (!empty($this->_articleIdsForImport)) {
            $collection->addFieldToFilter('art_id', array('in' => $this->_articleIdsForImport));
        }
        return $this;
    }
    
    public function getUrlKey($string)
    {
        return Mage::helper('magedoc')->transliterationString($string);
    }
    
    public function getProductsArray($isUpdateMode = null)
    {
        $hlp = Mage::helper('magedoc');
        if (is_null($isUpdateMode)){
            $isUpdateMode = $this->getIsUpdateMode();
        }
        $mediaGalleryAttributeId = Mage::getResourceSingleton('catalog/product')
                        ->getAttribute('media_gallery')->getId();

        if(!$isUpdateMode){
            $layoutModel = Mage::getModel('core/layout');
            $criteria = $layoutModel->createBlock('magedoc/criteria')
                    ->setTemplate('magedoc/product/criteria.phtml')
                    ->setArea('frontend');
            $linkArt = $layoutModel->createBlock('magedoc/linkArt')
                    ->setTemplate('magedoc/product/usedincars.phtml')
                    ->setArea('frontend');
            /** @var $linkArtShort Testimonial_Magedoc_Block_LinkArt */
            $linkArtShort = $layoutModel->createBlock('magedoc/linkArt')
                    ->setTemplate('magedoc/product/usedincars_short.phtml')
                    ->setArea('frontend');
            $criteriaToImport = $this->getArticleCriteria();
        }
        $qty = array();
        $item = new Varien_Object();
        $productDummy = new Varien_Object();
        $productsArray = array();
        $this->_prepareCollectionForAction($isUpdateMode);
        $collection = $this->getCollection();
        $adapter = $collection->getConnection();
        Mage::log((string)$collection->getSelect());
        $stmt = $adapter->query($collection->getSelect());
        //foreach ($adapter->fetchAll($collection->getSelect()) as $data) {
        while ($data = $stmt->fetch(Zend_Db::FETCH_ASSOC)){
            $item->setData($data);  
            $collection->processItemAfterLoad($item);
            if($item->getRetailerDisableAutopricing()){
                if($item->getCatalogProductId() !== null){
                    $item->setQty(0);
                }else{
                    continue;   
                }
            }

            if($isUpdateMode){
                $productData = array(
                    'data_id'           => $item->getData('data_id'),
                    "sku"               => $item->getData('sku'),
                    "price"             => $item->getData('final_price'),
                    "cost"              => $item->getData('base_cost'),
                    "qty"               => $item->getData('qty'),
                    "is_in_stock"       => $item->getData('qty') > 0 ? 1 : 0,
                    "retailer_id"       => $item->getData('retailer_id'),
                    /* Temporarily name and manufacturer update*/
                    //'name'              => $item->getData('name'),
                    //'url_key'           => $this->getUrlKey($item->getData('name')),
                    //'manufacturer'      => $item->getData('manufacturer'),
                    //'td_art_id'         => $item->getData('art_id'),
                    //'supplier'          => $item->getData('supplier')
                );
            }else{
                $fullName = $item->getData('name');
                $keyWords = $item->getData('name') . "," . $item->getData('sku');
                $productDummy->setName($fullName)
                    ->setTdArtId($item->getArtId());
                
                $productData = array(
                    'data_id'           => $item->getData('data_id'),
                    "sku"               => $item->getData('sku'),
                    "cost"              => $item->getData('cost'),
                    "created_at"        => now(),
                    "_category"         => $item->getData('category_id'),
                    "description"       => $fullName,
                    "meta_description"  => $fullName,
                    "meta_keywords"     => $keyWords,
                    "name"              => $fullName,
                    "price"             => $item->getData('final_price'),
                    "short_description" => $linkArtShort->setProduct($productDummy)->toHtml(),
                    "qty"               => $item->getData('qty'),
                    "is_in_stock"       => $item->getData('qty') > 0 ? 1 : 0,
                    "url_key"           => $this->getUrlKey($fullName),
                    'supplier'          => $item->getData('supplier'),
                    'manufacturer'      => $item->getData('manufacturer'),
                    'td_art_id'         => $item->getData('art_id'),
                    'type_ids'          => $linkArtShort->getTypeIds(),
                    'retailer_id'       => $item->getData('retailer_id'),
                    'code'              => $item->getData('art_article_nr'),
                    'code_normalized'   => $hlp->normalizeCode($item->getData('art_article_nr')),
                );
                $hlp->getEntityDefaultValues('product', $productData, $this->getStoreId());
                if ($item->getData('criteria_ids')){
                    $criteriaIds = explode(',', $item->getData('criteria_ids'));
                    $criteriaValues = explode(',', $item->getData('criteria_value_text'));
                } else {
                    $criteriaIds = array();
                }

                foreach ($criteriaIds as $criteriaKey => $criterionId){
                    if (isset ($criteriaToImport[$criterionId]) &&
                        isset($criteriaValues[$criteriaKey])){
                        $productData[$criteriaToImport[$criterionId]] = $criteriaValues[$criteriaKey];
                    }
                }
                if (($images = $item->getData('image_path')) && is_array($images)) {
                    $productData = array_merge($productData, array(
                        '_media_attribute_id'   => $mediaGalleryAttributeId,
                        '_media_image'          => $images,
                        'image'                 => current($images),
                        'small_image'           => current($images),
                        'thumbnail'             => current($images),
                        '_media_lable'          => null,
                        '_media_position'       => $item->getImageSort(),
                        '_media_is_disabled'    => 0,
                        ));
                }
            }
            $productsArray[] = $productData;
        }
//        print_r($productsArray); die;    
        return $productsArray;
    }

    public function getStoreId()
    {
        return 0;
    }
}

