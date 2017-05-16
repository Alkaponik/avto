<?php

class MageDoc_DirectoryCatalog_Model_Import_Aggregator extends Testimonial_MageDoc_Model_Import_Aggregator
{
    public function getCollection()
    {
        if(!isset($this->_collection)){
            /* @var $collection Testimonial_MageDoc_Model_Mysql4_Import_Retailer_Data_Collection */
            $collection = Mage::getResourceModel('magedoc/import_retailer_data_collection');
            $skuExpr = $this->_getSkuExpr();
            $collection->setIdFieldName('sku');
            //$collection->getResource()->setIdFieldName('sku');
            $collection->joinRetailer();
            $collection->getSelect()->columns(array('art_article_nr' => 'main_table.code'));
            $collection->addFilterToMap('art_article_nr', 'main_table.code_normalized');


            $this->getDirectory()->getResource()->joinLinks($collection, 'main_table');
            $this->getDirectory()->getResource()->joinSuppliers(
                $collection->getSelect(),
                array('sku' => $skuExpr,
                'supplier_id' => 'supplier.option_id'),
                'dol.supplier_id' );

            $collection->joinProducts($collection, 'main_table', array(
                'catalog_product_id'    => 'catalog_product_entity.entity_id',
                'product_price' => 'catalog_product_price.value',
                ),
                'catalog_product_entity.entity_id = main_table.product_id');

            $collection->joinProducts(null, 'main_table', '',
                'linked_product.entity_id = dol.directory_entity_id',
                'linked_product', false);

            if (!empty($this->_categoryId) || !$this->getIsUpdateMode()) {
                $resource = Mage::getResourceSingleton('catalog/category');
                $collection->getSelect()->joinLeft(array('catalog_category_product' => $resource->getTable('catalog/category_product')),
                    'catalog_category_product.product_id = main_table.product_id',
                    '');
                $collection->getSelect()->joinLeft(array('catalog_category_entity' => $resource->getEntityTable()),
                    'catalog_category_entity.entity_id = catalog_category_product.category_id',
                    '');
                /*
                Mage::getResourceSingleton('magedoc/tecdoc_searchTree_collection')
                    ->joinSearchTree($collection, 'td_article', array(
                    'category_id' => new Zend_Db_Expr("GROUP_CONCAT(
                                    catalog_category_entity.entity_id)"),
                    'td_str_id' => "catalog_category_entity.td_str_id",
                ));
                */
            }

            /*$collection->addFilterToMap('name', "CONCAT(td_desText.TEX_TEXT, ' ',
                            md_supplier.title,' ',
                            td_article.ART_ARTICLE_NR,
                            IF(td_desText1.TEX_TEXT IS NOT NULL,
                                CONCAT(' ',td_desText1.TEX_TEXT),
                                ''))");*/
            $collection->addFilterToMap('sku', $skuExpr);
            $collection->addFilterToMap('name', 'main_table.name');
            $collection->addFilterToMap('supplier_id', 'dol.supplier_id');


            $this->_collection = $collection;
            $this->_prepareCollection();
        }
        return $this->_collection;
    }

    /**
     * @todo remove duplicate refactor method extrude
     * @param null $isUpdateMode
     * @return MageDoc_DirectoryCatalog_Model_Import_Aggregator|Testimonial_MageDoc_Model_Import_Abstract
     */

    protected function _prepareCollectionForAction($isUpdateMode = null)
    {
        $collection = $this->getCollection();

        $collection->addEnabledRetailerImportFilter();

        if (!empty($this->_categoryId)){
            //$collection->addCategoryFilter(array('in' => $this->getBranchCategories()));
        }

        if (!empty($this->_categoryId) || !$isUpdateMode){
            //$collection->addFieldToFilter('catalog_category_entity.is_import_enabled', 1);
        }

        if ($supplierId = $this->getSupplierId()) {
            $collection->addSupplierFilter($supplierId);
        }

        if (!empty($this->_articleIdsForImport)) {
            $collection->addFieldToFilter('art_id', array('in' => $this->_articleIdsForImport));
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        Testimonial_MageDoc_Model_Import_Abstract::_prepareCollection();

        $this->addAggregatedColumnsToCollection($this->_collection);
        $this->_collection->getSelect()
            ->group($this->_getSkuExpr());

        return $this;
    }

    protected function _getSkuExpr()
    {
        return new Zend_Db_Expr("IFNULL(linked_product.sku, CONCAT(IFNULL(supplier.value, main_table.manufacturer), '-', main_table.code_normalized ))");
    }
}