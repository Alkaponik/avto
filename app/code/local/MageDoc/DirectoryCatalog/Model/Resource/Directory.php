<?php
class MageDoc_DirectoryCatalog_Model_Resource_Directory extends Testimonial_MageDoc_Model_Mysql4_Directory_Abstract
{
    const DIRECTORY_CODE = 'catalog';

    public function joinSuppliers ( $select, $fields = array(), $joinFieldName = 'main_table.supplier_id' )
    {
        if (!is_array($fields)){
            if (!empty($fields)){
                $fields = explode(',', $fields);
            } else {
                $fields = array();
            }
        }
        $select->joinLeft(
            array('supplier' =>  $this->getTable('eav/attribute_option_value')),
                "{$joinFieldName} = supplier.option_id",
                array_merge($fields,
                    array('vendor_name' => 'value', 'vendor_id' => 'option_id'))
            );

        return $this;
    }

    public function linkOffersToDirectory( $offersTable, $linkTable, $sourceId )
    {
        $offersTable = $this->getTable($offersTable);
        $linkTable = $this->getTable($linkTable);

        $adapter = $this->_getReadAdapter();

        /**
         * @todo: optimize join condition
         */

        $select = $adapter->select()
            ->from(
                array('offers_table' => $offersTable),
                array(new Zend_Db_Expr( 'NULL' ), 'data_id', new Zend_Db_Expr( '"' . static::DIRECTORY_CODE . '"' ))
            )
            ->joinLeft(
                array('supplier_map' => $this->getTable('magedoc/supplier_map')),
                'supplier_map.manufacturer = offers_table.manufacturer
                    AND supplier_map.retailer_id = offers_table.retailer_id
                    AND supplier_map.directory_code = "' . static::DIRECTORY_CODE . '"',
                array('supplier_map.supplier_id')
            )
            ->where('offers_table.source_id = ?', $sourceId);

        $this->_joinDirectoryIndex($select);
        //Mage::log((string)$select);

        $insertQuery = $adapter
            ->insertFromSelect($select, $linkTable, array('link_id', 'data_id', 'directory_code', 'supplier_id', 'directory_entity_id'), Varien_Db_Adapter_Pdo_Mysql::INSERT_ON_DUPLICATE);
        $adapter->query($insertQuery);

        return $this;
    }

    protected function _getDirectoryIndexJoinCondition()
    {
        $condition = parent::_getDirectoryIndexJoinCondition();
        return "($condition) OR " . '(product_index.' . $this->getKeyField('product_index', 'supplier_id') . ' = supplier_map.supplier_id
                                AND product_index.' . $this->getKeyField('product_index', 'model_normalized') . ' = offers_table.code_normalized)'
                ."OR " . '(product_index.' . $this->getKeyField('product_index', 'supplier_id') . ' = supplier_map.supplier_id
                    AND product_index.' . $this->getKeyField('product_index', 'model_normalized') . ' = offers_table.model_normalized)'
                ." OR " . '(product_index.' . $this->getKeyField('product_index', 'supplier_id') . ' = supplier_map.supplier_id
                    AND CONCAT( product_index.' . $this->getKeyField('product_index', 'model_normalized') . ",
                        product_index.{$this->getKeyField('product_index', 'code_normalized')} ) = offers_table.code_normalized)"
                ." OR " . '(product_index.' . $this->getKeyField('product_index', 'supplier_id') . ' = supplier_map.supplier_id
                    AND CONCAT( product_index.' . $this->getKeyField('product_index', 'model_normalized') . ",
                        product_index.{$this->getKeyField('product_index', 'code_normalized')} ) =
            CONCAT(offers_table.model_normalized, offers_table.code_normalized))";
    }

    /**
     * Deprecated
     * @param $offersTable
     * @param $linkTable
     * @param $sourceId
     * @return $this
     */
    public function __linkOffersToDirectory( $offersTable, $linkTable, $sourceId )
    {
        $offersTable = $this->getTable($offersTable);
        $linkTable = $this->getTable($linkTable);

        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from(
                array('offers_table' => $offersTable),
                array(
                     new Zend_Db_Expr( 'NULL' ),
                     'data_id',
                     new Zend_Db_Expr( '"' . static::DIRECTORY_CODE . '"' ),
                     'supplier_map.supplier_id',
                     new Zend_Db_Expr('NULL'),
                )
            )
            ->joinLeft(
                array('supplier_map' => $this->getTable('magedoc/supplier_map')),
                'supplier_map.manufacturer = offers_table.manufacturer
                    AND supplier_map.retailer_id = offers_table.retailer_id
                    AND supplier_map.directory_code = "' . static::DIRECTORY_CODE . '"',
                ''
            )->joinLeft(
                array('supplier_map' => $this->getTable('magedoc/supplier_map')),
                'supplier_map.manufacturer = offers_table.manufacturer
                    AND supplier_map.retailer_id = offers_table.retailer_id
                    AND supplier_map.directory_code = "' . static::DIRECTORY_CODE . '"',
                ''
            )
            ->where('offers_table.source_id = ?', $sourceId);

        $insertQuery = $adapter
            ->insertFromSelect($select, $linkTable, array('link_id', 'data_id', 'directory_code', 'supplier_id', 'directory_entity_id'), Varien_Db_Adapter_Pdo_Mysql::INSERT_ON_DUPLICATE);

        $adapter->query($insertQuery);

        return $this;
    }

    public function joinDirectorySuppliersSuggestions( $collection )
    {
        $attributeId = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'manufacturer')->getId();
        $collection->getSelect()
            ->joinInner(
                array('supplier2' => $this->getTable('eav/attribute_option_value')),
                'CONCAT(\'%\', supplier2.' . $this->getKeyField('vendor', 'name') . ', \'%\') LIKE CONCAT(\'%\', main_table.manufacturer, \'%\')'
            )
            ->joinInner(
                array('attribute_option' => $this->getTable('eav/attribute_option')),
                "attribute_option.option_id = supplier2.option_id
                    AND attribute_option.attribute_id = $attributeId",
                array(
                     'supplier_name_suggested' => 'GROUP_CONCAT(  `supplier2`.`' . $this->getKeyField('vendor', 'name') . '` )',
                     'supplier_id_suggested' => 'GROUP_CONCAT(  `supplier2`.`' . $this->getKeyField('vendor', 'primary') . '` )',
                     'supplier_id' => 'IF( COUNT( * ) = 1, GROUP_CONCAT(`supplier2`.`' . $this->getKeyField('vendor', 'primary') . '` ) , 0 )',
                     'vendor_name' => 'IF( COUNT( * ) = 1, GROUP_CONCAT(`supplier2`.`' . $this->getKeyField('vendor', 'name') . '` ) , \'\' )',
                )
            )->where('main_table.supplier_id IS NULL AND CHAR_LENGTH(main_table.manufacturer) > 2');
        return $this;
    }

    public function updateSupplierIdInSupplierMap( $directoryCode, $retailerId=null, $manufacturerList = null)
    {

        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'manufacturer');

        $sql = "UPDATE {$this->getTable('magedoc/supplier_map')} as supplier_map
            INNER JOIN {$this->getTable('eav/attribute_option_value')} as supplier
              ON supplier.value = supplier_map.manufacturer
                AND supplier_map.directory_code = '$directoryCode'
            INNER JOIN {$this->getTable('eav/attribute_option')} as options
              ON options.option_id = supplier.option_id AND options.attribute_id = {$attribute->getId()}
            SET supplier_map.supplier_id = supplier.option_id
            WHERE supplier_map.supplier_id IS NULL AND supplier.store_id = 0";

        if(!is_null($retailerId)) {
            $sql .= " AND supplier_map.retailer_id = $retailerId ";
        }

        if(!is_null($manufacturerList) ) {
            if(!is_array($manufacturerList)) {
                $manufacturerList = array($manufacturerList);
            }
            $sql .= " AND supplier.value IN ('" . implode('\',\'', $manufacturerList) . "') ";
        }

        $this->_getReadAdapter()->query($sql);

        return $this;
    }

    public function updateDirectoryOfferLink()
    {
        $select = $this->_getReadAdapter()
            ->select()
            ->from(
                array('ird' => $this->getTable('magedoc/import_retailer_data')),
                array( new Zend_Db_Expr('null'), 'data_id', new Zend_Db_Expr("'catalog'"), 'rsm.supplier_id', new Zend_Db_Expr('null'),new Zend_Db_Expr('null'))
            )->joinLeft(
                array('rsm' => $this->getTable('magedoc/supplier_map')),
                'ird.retailer_id = rsm.retailer_id AND ird.manufacturer = rsm.manufacturer AND rsm.directory_code = "'.MageDoc_DirectoryCatalog_Model_Directory::CODE.'"',
                ''
            );

        $adapter = $this->_getWriteAdapter();

        $insert = Mage::getResourceHelper('magedoc_system')->insertFromSelectOnDuplicate(
            $select,
            $this->getTable('magedoc/directory_offer_link'),
            array (),
            array( 'supplier_id' )
        );

        $adapter->query($insert);

        $query = "UPDATE `{$this->getTable('magedoc/directory_offer_link')}` AS l
                    INNER JOIN `{$this->getTable('magedoc/import_retailer_data')}` AS ird USING (data_id)
                    INNER JOIN `{$this->getTable('magedoc/generic_article_map')}` AS gam
                     ON ird.name = gam.name AND gam.status= 1
                    SET l.generic_article_id = gam.generic_article_id;";

        $adapter->query($query);

        if (Mage::helper('core')->isModuleEnabled('MageDoc_DirectoryTecdoc')) {
            $tdResource = Mage::getResourceSingleton('magedoc/tecdoc_article');
            $query = "UPDATE `{$this->getTable('magedoc/directory_offer_link')}` AS l1
                    INNER JOIN `{$this->getTable('magedoc/directory_offer_link')}` AS l2
                      ON l1.data_id = l2.data_id
                        AND l1.directory_code =  '" . MageDoc_DirectoryCatalog_Model_Directory::CODE . "'
                        AND l2.directory_code =  '" . Testimonial_MageDoc_Model_Directory_Tecdoc::CODE . "'
                    INNER JOIN {$tdResource->getTable('magedoc/tecdoc_linkArtGA')}
                      ON l2.directory_entity_id = LAG_ART_ID
                    SET l1.generic_article_id = LAG_GA_ID;";

            $adapter->query($query);
        }

        return $this;
    }

    public function getProductCollection($vendorId = null)
    {
        $collection  = Mage::getResourceModel('catalog/product_collection')
            //->addAttributeToFilter('manufacturer', $vendorId)
            ->addAttributeToSelect('name', 'inner')
            ->addAttributeToSelect('code', 'left');
        $collection->setStoreId(1);
        $manufacturerAttribute = $collection->getResource()->getAttribute('manufacturer');
        $filterResource = Mage::getResourceModel('catalog/layer_filter_attribute');
        $filter = new Varien_Object(array(
            'layer' => new Varien_Object(array('product_collection' => $collection)),
            'attribute_model' => $manufacturerAttribute
            ));
        $filterResource->applyFilterToCollection($filter, $vendorId);
        return $collection;
    }

    public function joinProductName($collection, $fieldName = 'name', $tableAlias = 'main_table', $fieldExpression = null)
    {
        Mage::helper('magedoc')->joinProductAttribute(
            'name', $collection, $tableAlias, 'product_id', 0, $fieldExpression, $fieldName
        );
        return $this;
    }
}