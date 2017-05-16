<?php
class MageDoc_DirectoryCatalog_Model_Indexer_Product extends Mage_Index_Model_Indexer_Abstract
{
    protected $_manufacturerIndexSettings = array(
        array(
            'manufacturer_id'   => '*',
            'key_fields'        => 'name',
            'code_normalized_regexp' => '\(([a-z0-9\.,_\-\s\p{Cyrillic}]{4,}?)\)',
            'index'   => 1
        ),
        array(
            'manufacturer_id'   => '*',
            'key_fields'        => 'name',
            'code_normalized_regexp' => '\s\(?([a-zA-Z\.\-]+\d[a-zA-Z0-9\.\-]+)',
            'index'   => 1
            )
        );

    public function getName()
    {
        return Mage::helper('magedoc')->__('Catalog Directory Product Index');
    }

    public function getDescription()
    {
        return Mage::helper('magedoc')->__('Builds product indexes for price import');
    }

    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        return $this;
    }

    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        return $this;
    }

    public function reindexAll()
    {
        $adminStore = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
        $additionalAttributes = array(/*'code', */'model', 'generic_article');
        $columns = array('name', 'manufacturer');
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->joinAttribute('name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $adminStore)
            ->joinAttribute('manufacturer',
                'catalog_product/manufacturer',
                'entity_id',
                null,
                'left',
                $adminStore);

        foreach ($additionalAttributes as $attributeCode){
            if (Mage::getResourceSingleton('catalog/product')->getAttribute($attributeCode)){
                $collection->joinAttribute($attributeCode,
                    "catalog_product/{$attributeCode}",
                    'entity_id',
                    null,
                    'left',
                    $adminStore);
                $columns []= $attributeCode;
            }
        };
        $collection->addAttributeToSelect($columns);
        $connection = $collection->getConnection();

        $limit = 1000;
        $data = array();
        $indexData = array();
        $indexSettings = $this->_manufacturerIndexSettings[0];
        $index = isset($indexSettings['index']) ? $indexSettings['index'] : 0;
        $i=0;
        while ($product = $collection->fetchItem()){
            $i++;
            $key = '';
            $keyFields = explode(',', $indexSettings['key_fields']);
            foreach ($keyFields as $field){
                $key .= $product->getData($field);
            }
            $codeNormalized = Mage::helper('magedoc')->normalizeCode($product->getCode());
            $modelNormalized = Mage::helper('magedoc')->normalizeCode($product->getModel());

            $indexData[$product->getId()] = array(
                'product_id'        => $product->getId(),
                'manufacturer_id'   => $product->getManufacturer(),
                'generic_article_id'=> $product->getGenericArticle(),
                'code_normalized'   => $codeNormalized ? $codeNormalized : null,
                'model_normalized'  => $modelNormalized ? $modelNormalized : null,
                /*'store_id'          => $adminStore*/
            );

            if (preg_match('/'.$indexSettings['code_normalized_regexp'].'/iu', $key, $matches)){
                $code = $matches[$index];
                $codeNormalized = Mage::helper('magedoc')->normalizeCode($code);

                $model = str_replace($matches[0], '', $product->getModel());
                $modelNormalized = Mage::helper('magedoc')->normalizeCode($model);
                if (!$product->getData('code')
                    && $code != $product->getData('code')){
                    $data[]= array(
                        'entity_id' => $product->getId(),
                        'code'       => $code,
                    );
                    $indexData[$product->getId()]['code_normalized'] = $codeNormalized;
                }
                if ($modelNormalized){
                    $indexData[$product->getId()]['model_normalized'] = $modelNormalized;
                }
            } elseif (preg_match('/'.$this->_manufacturerIndexSettings[1]['code_normalized_regexp'].'/iu', $key, $matches)){
                //Mage::log($key, null, 'code.log');
                //Mage::log($matches[1], null, 'code.log');
                $code = $matches[$index];
                $codeNormalized = Mage::helper('magedoc')->normalizeCode($code);

                if (!$product->getData('code')
                    && $code != $product->getData('code')){
                    $data[]= array(
                        'entity_id' => $product->getId(),
                        'code'       => $code,
                    );
                    $indexData[$product->getId()]['code_normalized'] = $codeNormalized;
                }
            }
            if ($i == $limit){
                $i=0;
                if ($data){
                    $connection->insertOnDuplicate(
                        $collection->getTable('catalog/product'), $data, array('code'));
                    $data = array();
                }
                if ($indexData){
                    $connection->insertOnDuplicate(
                        $collection->getTable('directory_catalog/product_index'),
                        $indexData,
                        array('manufacturer_id', 'generic_article_id', 'code_normalized', 'model_normalized'));
                    $indexData = array();
                }
            }
        }
        if ($data){
            $connection->insertOnDuplicate($collection->getTable('catalog/product'), $data, array('code'));
        }
        if ($indexData){
            $connection->insertOnDuplicate(
                $collection->getTable('directory_catalog/product_index'),
                $indexData,
                array('manufacturer_id', 'generic_article_id', 'code_normalized', 'model_normalized'));
        }

        return $this;
    }
}