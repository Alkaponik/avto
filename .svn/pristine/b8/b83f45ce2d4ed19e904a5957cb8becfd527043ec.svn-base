<?php
class Testimonial_MageDoc_Model_Mysql4_Directory_Abstract extends Mage_Core_Model_Resource_Db_Abstract
{
    const DIRECTORY_CODE = 'tecdoc';
    protected $_directoryConfigBase;
    protected $_productSuggestionTableAlias = 'product_index';

    protected function _construct()
    {
        $this->_init('magedoc/directory', 'directory_id');
        $this->_directoryConfigBase =  Testimonial_MageDoc_Model_Directory::DIRECTORIES_CONFIG_XML_PATH . '/'
            . static::DIRECTORY_CODE ;
    }

    public function getDirectoryTable( $tableType )
    {
        $tableNameConfigPath =  $this->_directoryConfigBase . '/tables/' . $tableType;
        $tableEntityName = (string)Mage::getConfig()->getNode( $tableNameConfigPath );

        return  $this->_getTablePrefix() . $this->getTable($tableEntityName) . $this->_getTableSuffix() ;
    }

    protected function _getTablePrefix()
    {
        $tablePrefixConfigPath = $this->_directoryConfigBase . '/table_prefix';
        return (string)Mage::getConfig()->getNode( $tablePrefixConfigPath );
    }

    protected function _getTableSuffix()
    {
        $tablePrefixConfigPath = $this->_directoryConfigBase . '/table_suffix';
        return (string)Mage::getConfig()->getNode( $tablePrefixConfigPath );
    }

    public function getKeyField( $table, $fieldToLink )
    {
        $keyFieldConfigPath = $this->_directoryConfigBase . '/tables/keys/' . $table . '/' . $fieldToLink;

        return Mage::getConfig()->getNode( $keyFieldConfigPath )->asArray();
    }

    public function linkOffersToDirectory( $offersTable, $linkTable, $sourceId )
    {
        $offersTable = $this->getTable($offersTable);
        $linkTable = $this->getTable($linkTable);

        $adapter = $this->_getReadAdapter();

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

        $insertQuery = $adapter
            ->insertFromSelect($select, $linkTable, array('link_id', 'data_id', 'directory_code', 'supplier_id', 'directory_entity_id'), Varien_Db_Adapter_Pdo_Mysql::INSERT_ON_DUPLICATE);
        $adapter->query($insertQuery);

        return $this;
    }

    protected function _joinDirectoryIndex( $select )
    {
        $select->joinLeft(
            array('product_index' => $this->getDirectoryTable('product_index')),
            $this->_getDirectoryIndexJoinCondition(),
            array('product_index.' . $this->getKeyField('product_index', 'primary'))
        );
    }

    protected function _getDirectoryIndexJoinCondition()
    {
        return 'product_index.' . $this->getKeyField('product_index', 'supplier_id') . ' = supplier_map.supplier_id
            AND product_index.' . $this->getKeyField('product_index', 'code_normalized') . ' = offers_table.code_normalized';
    }

    public function joinDirectorySuppliersSuggestions( $collection )
    {
           $collection->getSelect()
                ->joinInner(array('supplier2' => $this->getDirectoryTable('vendor')),
                    'CONCAT(\'%\', supplier2.' . $this->getKeyField('vendor', 'name') .
                    ', \'%\') LIKE CONCAT(\'%\', main_table.manufacturer, \'%\')',
                    array(
                         'supplier_name_suggested' => 'GROUP_CONCAT(  `supplier2`.`' . $this->getKeyField('vendor', 'name') .'` )',
                         'supplier_id_suggested' => 'GROUP_CONCAT(  `supplier2`.`' . $this->getKeyField('vendor', 'primary') . '` )',
                         'supplier_id' => 'IF( COUNT( * ) = 1, GROUP_CONCAT(`supplier2`.`' . $this->getKeyField('vendor', 'primary') . '` ) , 0 )',
                    )
                )
           ->where('main_table.supplier_id IS NULL AND CHAR_LENGTH(main_table.manufacturer) > 2');
        return $this;
    }

    public function joinDirectoryProductsSuggestions( $collection )
    {
        $collection->getSelect()
            ->joinInner(array('product_index' => $this->getDirectoryTable('product_index')),
                'directory_offer_link.supplier_id = product_index.' . $this->getKeyField('product_index', 'supplier_id') .
                   ' AND CONCAT(\'%\', product_index.' . $this->getKeyField('product_index', 'code_normalized') .
                ', \'%\') LIKE CONCAT(\'%\', main_table.code_normalized, \'%\')',
                array(
                    'supplier_name_suggested' => 'GROUP_CONCAT(  `product_index`.`' . $this->getKeyField('product_index', 'code_normalized') .'` )',
                    'product_id_suggested' => 'GROUP_CONCAT(  `product_index`.`' . $this->getKeyField('product_index', 'primary') . '` )',
                    'product_id' => 'IF( COUNT( * ) = 1, GROUP_CONCAT(`product_index`.`' . $this->getKeyField('product_index', 'primary') . '` ) , 0 )',
                )
            )
            ->where('directory_offer_link.directory_entity_id IS NULL /*AND main_table.model_normalized != \'\'*/');
        //$productNameSuggestedExpression = 'GROUP_CONCAT(  `product_index`.`' . $this->getKeyField('product_index', 'code_normalized') .'` )';
        $productNameSuggestedExpression = 'GROUP_CONCAT({{field_expression}})';
        $this->joinProductName($collection, 'product_name_suggested', $this->_productSuggestionTableAlias, $productNameSuggestedExpression);
        $productNameExpression = 'IF(COUNT( * ) = 1, {{field_expression}}, \'\')';
        $this->joinProductName($collection, 'product_name', $this->_productSuggestionTableAlias, $productNameExpression);

        //$collection->addFilterToMap('product_name_suggested', $productNameSuggestedExpression);
        //$collection->addFilterToMap('product_name', $productNameExpression);
        //die((string)$collection->getSelect());

        return $this;
    }

    protected function _joinSuggestionProducts( $collection )
    {
        return $this;
    }

    public function joinSuppliers ( $select, $fields = '', $joinFieldName = 'main_table.manufacturer' )
    {
        if(!empty($fields)) {
            if(!is_array($fields)) {
                $fields = array($fields);
            }

            $fieldsMap = array(
                'vendor_id' => 'supplier.' . $this->getKeyField('vendor', 'primary'),
                'vendor_name' => 'supplier.' . $this->getKeyField('vendor', 'name'),
            );

            $fields = array_flip($fields);
            $fieldsMerged = array_merge($fields, $fieldsMap);

            $fields = array_intersect_key($fieldsMerged, $fields);
        }

        $select->joinLeft(
                array('supplier' => $this->getDirectoryTable('vendor')),
                "{$joinFieldName} = supplier." . $this->getKeyField('vendor', 'name'),
                $fields
            );
        return $this;
    }


    public function getSupplierOptions( $conditions = null )
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from(
                $this->getDirectoryTable('vendor'),
                array(
                    'name' => $this->getKeyField('vendor', 'name'),
                    'id' => $this->getKeyField('vendor', 'primary'),
                )
            )
            ->order('name');

        if(!is_null($conditions)) {

            foreach($conditions as $condition => $value) {
                $select->where( $condition, $value );
            }
        }

        $result = $adapter->query($select);

        $vendorOptions = array();
        while($vendor = $result->fetch()) {
            $vendorOptions[ $vendor['id'] ] = $vendor['name'];
        }

        return $vendorOptions;
    }

    public function updateSupplierIdInSupplierMap( $directoryCode, $retailerId = null, $manufacturerList = null)
    {
        if(is_array($manufacturerList) && empty($manufacturerList)) {
            return $this;
        }
        $sql = "UPDATE {$this->getTable('magedoc/supplier_map')} as supplier_map
            INNER JOIN {$this->getDirectoryTable('vendor')} as supplier
                ON supplier.{$this->getKeyField('vendor', 'name')} = supplier_map.manufacturer
                    AND supplier_map.directory_code = '$directoryCode'
            SET supplier_map.supplier_id = supplier.{$this->getKeyField('vendor', 'primary')}
            WHERE supplier_map.supplier_id IS NULL";

        if(!is_null($retailerId)) {
            $sql .= " AND supplier_map.retailer_id = $retailerId ";
        }

        if(!is_null($manufacturerList)) {
            if(!is_array($manufacturerList)) {
                $manufacturerList = array($manufacturerList);
            }
            $sql .= " AND supplier_map.manufacturer IN (" . implode(',', $manufacturerList) . ") ";
        }

        $this->_getReadAdapter()->query($sql);

        return $this;
    }

    public function joinLinks ($collection, $tableAlias = 'ird')
    {
        $collection->getSelect()
            ->joinInner(
                array('dol' => $this->getTable('magedoc/directory_offer_link')),
                $this->getReadConnection()->quoteInto("dol.data_id = {$tableAlias}.data_id AND dol.directory_code = ?", static::DIRECTORY_CODE),
                ''
        );
    }

    public function joinProducts ( $select, $fields = '', $joinFieldName = 'main_table.product_id' )
    {
        return $this;
    }

    public function getProductCollection($vendorId = null)
    {
        return array();
    }

    public function joinProductName($collection, $fieldName = 'name', $tableAlias = 'main_table', $fieldExpression = null)
    {
        return $this;
    }
}