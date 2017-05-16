<?php
class Testimonial_MageDoc_Model_Mysql4_Directory_Tecdoc extends Testimonial_MageDoc_Model_Mysql4_Directory_Abstract
{
    const DIRECTORY_CODE = 'tecdoc';
    protected $_productSuggestionTableAlias = 'directory_product';

    public function linkOffersToDirectory($offersTable, $linkTable, $sourceId)
    {

        parent::linkOffersToDirectory($offersTable, $linkTable, $sourceId);
        /*
        $tecdocResource = Mage::getResourceSingleton('magedoc/tecdoc_article');

        $directory = static::DIRECTORY_CODE;
        $offersTable = $this->getTable($offersTable);
        $linkTable = $this->getTable($linkTable);
        $query = <<<SQL
          UPDATE $offersTable AS offers
            INNER JOIN $linkTable as directory_offer_link
              ON directory_offer_link.data_id = directory_offer_link.data_id
                AND directory_offer_link.directory = '{$directory}'
                AND directory_offer_link.directory_entity_id IS NULL
            INNER JOIN {$tecdocResource->getTable('magedoc/tecdoc_artLookup')} as art_lookup
              ON  art_lookup.ARL_SEARCH_NUMBER = offers.code_normalized AND art_lookup.ARL_KIND IN (1, 2, 3)
            INNER JOIN {$tecdocResource->getTable('magedoc/tecdoc_article')} as articles
              ON  art_lookup.ARL_ART_ID = articles.ART_ID AND directory_offer_link.supplier_id = articles.ART_SUP_ID
            SET directory_offer_link.directory_entity_id = articles.ART_ID
          WHERE offers.source_id = {$sourceId} ;
SQL;
        $adapter = $this->_getWriteAdapter();
        $adapter->query($query);

        $query = <<<SQL
           UPDATE $offersTable AS offers
           INNER JOIN $linkTable as directory_offer_link
              ON directory_offer_link.data_id = directory_offer_link.data_id
                AND directory_offer_link.directory = '{$directory}'
                AND directory_offer_link.directory_entity_id IS NULL
             INNER JOIN {$tecdocResource->getTable('magedoc/supplier_map')} as supplier_map
               ON supplier_map.manufacturer = offers.manufacturer AND supplier_map.retailer_id = offers.retailer_id
                AND supplier_map.directory = '{$directory}'
             INNER JOIN {$tecdocResource->getTable('magedoc/tecdoc_artLookup')} as art_lookup
              ON  art_lookup.ARL_SEARCH_NUMBER = offers.code_normalized AND art_lookup.ARL_KIND = 4
             INNER JOIN {$tecdocResource->getTable('magedoc/tecdoc_article')} as articles
              ON  art_lookup.ARL_ART_ID = articles.ART_ID AND directory_offer_link.supplier_id = articles.ART_SUP_ID
            SET directory_offer_link.directory_entity_id = articles.ART_ID
          WHERE offers.source_id = {$sourceId} AND supplier_map.use_crosses = 1;
SQL;

        $adapter->query($query); */

        return $this;
    }

    public function getProductCollection($vendorId = null)
    {
        /* @var $collection Testimonial_MageDoc_Model_Mysql4_Tecdoc_Article_Collection */
        $collection = Mage::getResourceModel('magedoc/tecdoc_article_collection');
        $collection->addSupplierFilter($vendorId);
        $this->joinProductName($collection, 'name', 'main_table');

        return $collection;
    }

    public function joinProductName($collection, $fieldName = 'name', $tableAlias = 'directory_product', $fieldExpression = null)
    {
        /* @var $resource Testimonial_MageDoc_Model_Mysql4_Tecdoc_Article */
        $resource = Mage::getResourceSingleton('magedoc/tecdoc_article');
        $resource->joinName($collection->getSelect(), $tableAlias, $fieldName);
        $collection->addFilterToMap($fieldName, $resource->getNameFieldExpression($tableAlias));
        return $this;
    }

    public function joinProducts ( $select, $fields = '', $joinFieldName = 'main_table.product_id' )
    {
        if(!empty($fields)) {
            if(!is_array($fields)) {
                $fields = array($fields);
            }

            $fieldsMap = array(
                'product_id' => 'directory_product.' . $this->getKeyField('product', 'primary'),
            );

            $fields = array_flip($fields);
            $fieldsMerged = array_merge($fields, $fieldsMap);

            $fields = array_intersect_key($fieldsMerged, $fields);
        }

        $select->joinLeft(
            array('directory_product' => $this->getDirectoryTable('product')),
            "{$joinFieldName} = directory_product." . $this->getKeyField('product', 'primary'),
            $fields
        );
        return $this;
    }

    protected function _joinSuggestionProducts( $collection )
    {
        $this->joinProducts( $collection->getSelect(), '', 'product_index.product_id');

        return $this;
    }
}