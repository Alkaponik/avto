<?php
class Testimonial_MageDoc_Model_Mysql4_Retailer_Data_Import_Parser_Base
    extends Testimonial_MageDoc_Model_Mysql4_Retailer_Data_Import_Parser_Abstract
{
    const DEFAULT_QTY = 10;
    protected $_codeNormalizedReplaceExpression
        = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
            {{attribute}}
            ,' ', ''), '+', ''), '.', ''), '-', ''), '=', ''), '\\\\', ''), '/', ''), '\\'', ''), '\"', ''), ')', ''), '(', ''), ']', ''), '[', '')";
    protected $_qtyNormalizeExpression = "FLOOR(REPLACE(REPLACE( REPLACE({{qty}}, '<',''), '>', '' ),' ', ''))";
    protected $_fieldExpressions = null;

    public function insertImportRetailerDataPreview(  $supMapIds = null )
    {
        if(is_array($supMapIds) && empty($supMapIds)) {
            return 0;
        }

        $tableName = $this->getTable('magedoc/import_retailer_data_preview');

        $this->_insertParsedDataFromBase(
            $tableName,
            false,
            $supMapIds
        );

        return $this;
    }

    protected function _insertParsedDataFromBase($tableName, $inStockOnly = false, $supMapIds = null)
    {
        $fieldsForSelect = $this->_getFieldsExpressions();
        $this->_filterFieldsForSelect($fieldsForSelect);
        $this->_applyUserExpressionsToFields($fieldsForSelect);

        $adapter = $this->_getWriteAdapter();

        $select = $this->_getBaseTableSelect($fieldsForSelect);
        $this->_applyBaseTableSelectExtraConditions($select,$inStockOnly, $supMapIds);


        $insert = Mage::getResourceHelper('magedoc_system')->insertFromSelectOnDuplicate(
            $select,
            $tableName,
            array_keys($fieldsForSelect),
            array(
                 'name',
                 'description',
                 'code_normalized',
                 'code_raw',
                 'code',
                 'cost',
                 'price',
                 'supplier_id',
                 'qty',
                 'domestic_stock_qty',
                 'general_stock_qty',
                 'distant_stock_qty',
                 'other_stock_qty',
                 'updated_at'
            )
        );

        return $adapter->query($insert)->rowCount();
    }

    protected function _getFieldsExpressions( $tableAlias = 'main_table' )
    {
        $currentDate = date('Y-m-d H:i:s');

        $fieldsForSelect = array(
            'name'               => $tableAlias.'.name',
            'description'        => $tableAlias.'.description',
            'card'               => $tableAlias.'.card',
            'code'               => $this->_getCodeExpression( $tableAlias ),
            'code_normalized'    => $this->_getCodeNormalizedExpression( ),
            'code_raw'           => $tableAlias.'.code_raw',
            'cost'               => $this->_priceNormalize($tableAlias.'.cost'),
            'price'              => $this->_priceNormalize($tableAlias.'.price'),
            'msrp'               => $this->_priceNormalize($tableAlias.'.msrp'),
            'final_price'        => $this->_priceNormalize($tableAlias.'.final_price'),
            'delivery_days'      => $tableAlias.'.delivery_days',
            'currency'           => $tableAlias.'.currency',
            'supplier_id'        => 'supplier_map.supplier_id',
            'retailer_id'        => $tableAlias.'.retailer_id',
            'manufacturer'       => $tableAlias.'.manufacturer',
            'manufacturer_id'    => $tableAlias.'.manufacturer_id',
            'domestic_stock_qty' => $this->_qtySqlNormalize($tableAlias.'.domestic_stock_qty'),
            'general_stock_qty'  => $this->_qtySqlNormalize( $tableAlias.'.general_stock_qty'),
            'other_stock_qty'    => $this->_qtySqlNormalize( $tableAlias.'.other_stock_qty'),
            'distant_stock_qty'    => $this->_qtySqlNormalize( $tableAlias.'.distant_stock_qty'),
            'qty'                => $this->_getQtyExpression( $tableAlias ),
            'created_at'         => new Zend_Db_Expr("'$currentDate'"),
            'updated_at'         => new Zend_Db_Expr("'$currentDate'"),
            'source_id'          => $tableAlias.'.source_id',
        );

        return $fieldsForSelect;
    }

    protected function _filterFieldsForSelect( &$fieldsForSelect, $tableAlias = 'main_table' )
    {
        $importAdapterConfig = $this->getImportAdapterConfig();
        $priceTableFields = array();
        $sourceFieldsMap = $importAdapterConfig->getSourceFieldsMap();
        foreach($sourceFieldsMap as $field) {
            $priceTableFields[] = $field['base_table_field'];
        }

        $replacingMap = array(
            'cost'  => new Zend_Db_Expr( "TRIM(REPLACE($tableAlias.price, ',', '.')) * (100 - IFNULL(supplier_map.discount_percent, {$importAdapterConfig->getDiscountPercent()})) / 100" ),
            'price' => new Zend_Db_Expr( "TRIM(REPLACE($tableAlias.cost, ',', '.')) / (100 - IFNULL(supplier_map.discount_percent, {$importAdapterConfig->getDiscountPercent()})) * 100" ),
        );

        foreach($replacingMap as $key => $expression) {
            if(!in_array($key, $priceTableFields)) {
                $fieldsForSelect[$key] = $expression;
            }
        }

        if( $importAdapterConfig->getVatPercent() != 0 ) {
            $fieldsForSelect['cost'] = (string)$fieldsForSelect['cost'] . " * (100 + {$importAdapterConfig->getVatPercent()})/100";
            $fieldsForSelect['price'] = (string)$fieldsForSelect['price'] . " * (100 + {$importAdapterConfig->getVatPercent()})/100";
        }
    }

    protected function _applyUserExpressionsToFields( &$fieldsForSelect )
    {
        $this->_setFieldExpressions($fieldsForSelect);
        $templateProcessor = Mage::helper('magedoc_system');
        $importAdapterConfig = $this->getImportAdapterConfig();
        $sourceFieldsFilters = $importAdapterConfig->getSourceFieldsFilters();
        foreach($sourceFieldsFilters as $sourceFieldFilter) {
            $fieldsForSelect[$sourceFieldFilter['base_table_field']] = new Zend_Db_Expr(
                $templateProcessor->processTemplate(
                    $sourceFieldFilter['path'],
                    $this->getRetailer(),
                    $this
                )
            );
        }
    }

    public function getFieldExpressions()
    {
        return $this->_fieldExpressions;
    }

    protected function _setFieldExpressions( $data )
    {
        if(!is_array($data)){
            $data = array($data);
        }
        if(is_null($this->_fieldExpressions)) {
            $this->_fieldExpressions = new Varien_Object($data);
        } else {
            $this->_fieldExpressions->addData($data);
        }

        return $this->_fieldExpressions;
    }

    protected function _getBaseTableSelect($fieldsForSelect)
    {
        $baseTable = $this->getTable('magedoc/import_retailer_data_base');
        $adapter = $this->_getWriteAdapter();
        $select = $adapter->
            select()
            ->from(
                array('main_table' => $baseTable),
                array_values($fieldsForSelect))
            ->where('main_table.retailer_id = ?', $this->getRetailer()->getId())
            ->where('main_table.source_id = ?', $this->getSource()->getId())
            ->joinLeft(
                array('supplier_map' => $this->getTable('magedoc/supplier_map')),
                'main_table.retailer_id = supplier_map.retailer_id AND main_table.manufacturer = supplier_map.manufacturer',
                false
            );

        return $select;
    }

    protected function _applyBaseTableSelectExtraConditions($select, $inStockOnly, $supMapIds)
    {
        if($inStockOnly) {
            $select->where( $this->_getQtyExpression( ) . ' > 0');
        }

        if(!empty($supMapIds)) {
            $supMapIds = implode(',', $supMapIds);
            $select->where( "map_id IN ($supMapIds)");
        }
    }

    protected function _getCodeExpression( $tableAlias = 'main_table'  )
    {
        $importAdapterConfig = $this->getImportAdapterConfig();
        $expressionParts = array(
            'code_after'  => 0,
            'code_before' => 0,
        );

        if( $importAdapterConfig->getCodeAfter() !== '') {
            $expressionParts['code_after'] = "LOCATE('{$importAdapterConfig->getCodeAfter()}', {$tableAlias}.code_raw)";
        }

        if( $importAdapterConfig->getCodeBefore() !== '' ) {
            $expressionParts['code_before'] = "LOCATE('{$importAdapterConfig->getCodeBefore()}', {$tableAlias}.code_raw)";
        }

        $normalizeExpression =  "
                    SUBSTR(
                        {$tableAlias}.code_raw,
                        1 + {{code_after}}
                            + IFNULL(supplier_map.prefix_length, 0)
                            + IF (supplier_map.prefix IS NOT NULL,
                                IF (SUBSTRING({$tableAlias}.code_raw, 1, CHAR_LENGTH(supplier_map.prefix)) = supplier_map.prefix, CHAR_LENGTH(supplier_map.prefix), 0),
                                0),
                        IF ({{code_before}}, {{code_before}}, CHAR_LENGTH({$tableAlias}.code_raw))
                            - IF (supplier_map.suffix IS NOT NULL,
                                IF (SUBSTRING({$tableAlias}.code_raw, -1 * CHAR_LENGTH(supplier_map.suffix)) = supplier_map.suffix, CHAR_LENGTH(supplier_map.suffix), 0),
                                0)
                            - IFNULL(supplier_map.suffix_length, 0)
                            - {{code_after}}
                            - IFNULL(supplier_map.prefix_length, 0)
                            - IF (supplier_map.prefix IS NOT NULL,
                                IF (SUBSTRING({$tableAlias}.code_raw, 1, CHAR_LENGTH(supplier_map.prefix)) = supplier_map.prefix, CHAR_LENGTH(supplier_map.prefix), 0),
                                0)
                            )";

        $normalizeExpression = str_replace(
            array('{{code_after}}', '{{code_before}}'),
            array(
                 $expressionParts['code_after'],
                 $expressionParts['code_before']
            ),
            $normalizeExpression
        );

        $normalizeExpression = "
            IF(
                CHAR_LENGTH('{$importAdapterConfig->getCodeDelimiter()}') <> 0 OR CHAR_LENGTH(supplier_map.code_delimiter) <> 0,
                SUBSTRING_INDEX(
                    $normalizeExpression,
                    IF(
                        CHAR_LENGTH(supplier_map.code_delimiter) <> 0,
                        supplier_map.code_delimiter,
                        '{$importAdapterConfig->getCodeDelimiter()}'
                    ),
                    IF(
                        supplier_map.code_part_count <> 0,
                        supplier_map.code_part_count,
                        {$importAdapterConfig->getCodePartCount()}
                    )
                ),
                $normalizeExpression
            )
        ";

        $normalizeExpression = str_replace(array("\\t", "\\n", "\\r"), array( "\t",  "\n",  "\r"), $normalizeExpression);
       
        return new Zend_Db_Expr( "$normalizeExpression" );
    }

    protected function _getCodeNormalizedExpression( )
    {
        return new Zend_Db_Expr(
            str_replace('{{attribute}}',
                (string)$this->_getCodeExpression(),
                $this->_codeNormalizedReplaceExpression
            )
        );
    }

    protected function _qtySqlNormalize( $qty)
    {
        $importAdapterConfig = $this->getImportAdapterConfig();
        $defaultQty = (int)($importAdapterConfig->getDefaultQty()) ? $importAdapterConfig->getDefaultQty() : static::DEFAULT_QTY;

        return new Zend_Db_Expr(str_replace('{{qty}}', $qty, "IF ({{qty}} REGEXP '^[x*+]+$', CHAR_LENGTH({{qty}}) * {$defaultQty} ,$this->_qtyNormalizeExpression )"));
    }

    protected function _priceNormalize( $field )
    {
        return new Zend_Db_Expr("TRIM(REPLACE($field, ',', '.'))");
    }

    protected function _getQtyExpression( $tableAlias = 'main_table' )
    {
        $expression = " IF(" . (string)$this->_qtySqlNormalize($tableAlias.'.qty') . " IN ('',0)," .
            (string)$this->_qtySqlNormalize($tableAlias.'.domestic_stock_qty') . ' + ' .
            (string)$this->_qtySqlNormalize($tableAlias.'.general_stock_qty') . ' + ' .
            (string)$this->_qtySqlNormalize($tableAlias.'.other_stock_qty') . ' + ' .
            (string)$this->_qtySqlNormalize($tableAlias.'.distant_stock_qty') .
            ", " . (string)$this->_qtySqlNormalize($tableAlias.'.qty') . " )";

        return new Zend_Db_Expr($expression);
    }

    public function updateTdArtId( $supplierMapIds = null )
    {
        if(is_array($supplierMapIds) && empty($supplierMapIds)) {
            return 0;
        }

        $tecdocResource = Mage::getResourceSingleton('magedoc/tecdoc_article');

        $joinClosure = '';
        $whereClosure = '';
        if(!is_null($supplierMapIds)) {
            $supplierMapIds = implode(',', $supplierMapIds);
            $joinClosure = "INNER JOIN {$this->getTable('magedoc/supplier_map')} as supplier_map
                ON supplier_map.manufacturer = prices.manufacturer AND supplier_map.retailer_id = prices.retailer_id";

            $whereClosure = " AND supplier_map.map_id IN ({$supplierMapIds})";
        }
        $tableName = $this->getTable('magedoc/import_retailer_data_preview');
        $query = <<<SQL
            UPDATE $tableName AS prices
                INNER JOIN {$tecdocResource->getTable('magedoc/tecdoc_articleNormalized')} as articles_normalized
                    ON articles_normalized.ARN_ARTICLE_NR_NORMALIZED = prices.code_normalized
                        AND prices.supplier_id = articles_normalized.ARN_SUP_ID
                {$joinClosure}
            LEFT JOIN {$this->getTable('catalog/product')} as product ON articles_normalized.ARN_ART_ID = product.td_art_id
            SET prices.td_art_id = articles_normalized.ARN_ART_ID, prices.product_id = product.entity_id
            WHERE prices.retailer_id = {$this->getRetailer()->getId()} {$whereClosure}
SQL;

        $adapter = $this->_getWriteAdapter();

        $affectedRows = $adapter->query($query)->rowCount();

        //$affectedRows += $this->_updateTdArtIdFromArtLookup($tableName, $whereClosure, $joinClosure);

        return $affectedRows;
    }

    public function _updateTdArtIdFromArtLookup( $tableName, $whereClosure, $joinClosure)
    {
        $tecdocResource = Mage::getResourceSingleton('magedoc/tecdoc_article');

        $query = <<<SQL
            UPDATE $tableName AS prices
                {$joinClosure}
                INNER JOIN {$tecdocResource->getTable('magedoc/tecdoc_artLookup')} as art_lookup
                    ON  art_lookup.ARL_SEARCH_NUMBER = prices.code_normalized AND art_lookup.ARL_KIND IN (1, 2, 3)
                INNER JOIN {$tecdocResource->getTable('magedoc/tecdoc_article')} as articles
                    ON  art_lookup.ARL_ART_ID = articles.ART_ID AND prices.supplier_id = articles.ART_SUP_ID
                SET prices.td_art_id = articles.ART_ID
            WHERE prices.retailer_id = {$this->getRetailer()->getId()} AND prices.td_art_id IS NULL {$whereClosure};
SQL;
        $adapter = $this->_getWriteAdapter();
        $affectedRows = $adapter->query($query)->rowCount();

        $query = <<<SQL
           UPDATE $tableName AS prices
              INNER JOIN {$tecdocResource->getTable('magedoc/supplier_map')} as supplier_map
                ON supplier_map.supplier_id = prices.supplier_id AND supplier_map.retailer_id = prices.retailer_id
              INNER JOIN {$tecdocResource->getTable('magedoc/tecdoc_artLookup')} as art_lookup
                ON art_lookup.ARL_SEARCH_NUMBER = prices.code_normalized AND art_lookup.ARL_KIND = 4
              INNER JOIN {$tecdocResource->getTable('magedoc/tecdoc_article')} as articles
                ON  art_lookup.ARL_ART_ID = articles.ART_ID AND prices.supplier_id = articles.ART_SUP_ID
              SET prices.td_art_id = articles.ART_ID
          WHERE prices.retailer_id = {$this->getRetailer()->getId()}
            AND supplier_map.use_crosses = 1 AND prices.td_art_id IS NULL {$whereClosure};
SQL;

        $affectedRows += $adapter->query($query)->rowCount();

        return $affectedRows;
    }

    public function updateByKey($keyFields)
    {
        $query = "UPDATE {$this->getTable('magedoc/import_retailer_data_preview')} as preview
            INNER JOIN {$this->getTable('magedoc/import_retailer_data_base')} as main_table USING(retailer_id, $keyFields) SET ";

        $sourceFieldsMap = $this->getImportAdapterConfig()->getSourceFieldsMap();
        $sourceFieldsFilters = $this->getImportAdapterConfig()->getSourceFieldsFilters();
        $sourceFieldsMap = array_merge( $sourceFieldsMap, $sourceFieldsFilters );

        $fieldExpressions = $this->_getFieldsExpressions('main_table');
        $this->_applyUserExpressionsToFields($fieldExpressions);

        $keyFields = explode(',', $keyFields);
        $setFields = array();
        foreach($sourceFieldsMap as $element) {
            if( !in_array($element['base_table_field'], $keyFields) ) {
                $setFields[] = "preview.{$element['base_table_field']} = {$fieldExpressions[$element['base_table_field']]}";
            }
        }
        $query .= implode(' , ', $setFields);

        $adapter = $this->_getWriteAdapter();
        $query .= " WHERE"
            .$adapter->quoteInto(' preview.retailer_id = ?', $this->getRetailer()->getId())
            .$adapter->quoteInto(' AND main_table.source_id = ?', $this->getSource()->getId());
        $adapter->query($query);
    }
}
