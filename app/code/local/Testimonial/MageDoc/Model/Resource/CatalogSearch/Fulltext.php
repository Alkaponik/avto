<?php

class Testimonial_MageDoc_Model_Resource_CatalogSearch_Fulltext extends Mage_CatalogSearch_Model_Resource_Fulltext
{
    /**
     * Prepare results for query
     *
     * @param Mage_CatalogSearch_Model_Fulltext $object
     * @param string $queryText
     * @param Mage_CatalogSearch_Model_Query $query
     * @return Mage_CatalogSearch_Model_Resource_Fulltext
     */
    public function prepareResult($object, $queryText, $query)
    {
        if (Mage::helper('core')->isModuleEnabled('MageDoc_DirectoryTecdocWebservice')) {
            Mage::getSingleton('magedoc/directory')->getDirectory('tecdoc_webservice')->prepareLookupResult($object, $queryText, $query);
        }
        if (Mage::helper('core')->isModuleEnabled('MageDoc_DirectoryTecdoc')) {
            $this->prepareLookupResult($object, $queryText, $query);
        }
        if ($this->_isNewSearch()
            && !empty($this->_foundData)){
            return $this;
        }
        return parent::prepareResult($object, $queryText, $query);
    }

    public function prepareLookupResult($object, $queryText, $query)
    {
        $adapter = $this->_getWriteAdapter();
        $newSearchModel = $this->_isNewSearch();

        if (!$query->getIsProcessed()) {
            $searchType = $object->getSearchType($query->getStoreId());

            $preparedTerms = Mage::helper('magedoc')
                ->normalizeCode($queryText);

            $tecdocResource = Mage::getResourceSingleton('magedoc/tecdoc_article');
            $mainTable = $this->getTable('catalog/product');
            $mainTableAlias = 'p';
            $fields = array(
                'query_id' => new Zend_Db_Expr($query->getId()),
                'product_id' => 'p.entity_id',
                'relevance'  => new Zend_Db_Expr(0)
            );
            if ($newSearchModel){
                unset($fields['query_id']);
            }
            $select = $adapter->select()
                ->from(array($mainTableAlias => $mainTable), $fields)
                ->joinInner(array('arl' => $tecdocResource->getTable('magedoc/tecdoc_artLookup')),
                'arl.ARL_ART_ID = p.td_art_id',
                array())
            ->where('arl.ARL_SEARCH_NUMBER = ?', $preparedTerms);
                //->where($mainTableAlias.'.store_id = ?', (int)$query->getStoreId());
            if ($newSearchModel){
                $this->_foundData = $adapter->fetchPairs($select);
            } else {
                $sql = $adapter->insertFromSelect($select,
                    $this->getTable('catalogsearch/result'),
                    array(),
                    Varien_Db_Adapter_Interface::INSERT_ON_DUPLICATE);
                $stmt = $adapter->query($sql);
                if ($stmt->rowCount()){
                    $query->setIsProcessed(1);
                }
            }
        }

        return $this;
    }

    protected function _isNewSearch()
    {
        return version_compare(Mage::getVersion(), '1.9.3', '>=');
    }
}