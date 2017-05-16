<?php
abstract class Testimonial_MageDoc_Model_Retailer_Data_Import_Parser_Advanced
    extends Testimonial_MageDoc_Model_Retailer_Data_Import_Parser_Abstract
{
    protected $_modelIndexSettings = array(
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

    protected $_baseTableFields = array();
    protected $_directoryExtraFields = array();
    protected $_lastOrigRow = array();
    protected $_lastRow = array();
    protected $_defaultFieldFilters = array(
        array(
            'base_table_field'  => 'code',
            'path'              => '{{::getPureCode(@)}}'
        ),
        array(
            'base_table_field'  => 'code_normalized',
            'path'              => '{{::getPureCode(@)/::normalizeCode(@)}}'
        ),
        array(
            'base_table_field'  => 'model',
            'path'              => '{{::getPureModel(@)}}'
        ),
        array(
            'base_table_field'  => 'model_normalized',
            'path'              => '{{::getPureModel(@)/::normalizeCode(@)}}'
        )
    );


    public function _construct()
    {
        $this->_init('magedoc/retailer_data_import_parser_advanced');
    }

    public function insertImportRetailerDataPreview( $supMapIds = null )
    {
        /** @var Testimonial_MageDoc_Model_Mysql4_Retailer_Data_Import_Parser_Advanced $resource */
        $resource = $this->getResource();
        $this->prepareSourceAdapter();

        $insertedCountIntoBase = 0;
        $insertedCountIntoPreview = 0;
        while ( ( $bunchSize = count($bunch = $this->getNextBunch(self::MAX_BUNCH_SIZE)) ) > 0 ) {
            $insertedCountIntoBase += $this->getResource()->saveBunchToBase($bunch);
            $insertedCountIntoPreview += $resource->saveBunchToPreview( $bunch );
        }

        if( !$insertedCountIntoBase ) {
            $retailerName = $this->getRetailer()->getName();
            $fileName = $this->getSource()->getSourcePath();

            Mage::throwException(
                Mage::helper('magedoc')->__('%s price (%s) contains no valid records', $retailerName, $fileName )
            );
        }

        $this->getSession()->setLastPriceSheetValidRecords($insertedCountIntoBase);

        $this->getSession()->setLastPriceSheetRecordsWithOldBrands(
            $this->getResource()->updateBaseTableSupplierId()
        );

        return  $this;
    }

    protected function _addRowToBunch( $row, &$bunch )
    {
        if(empty($this->_baseTableFields)) {
            $this->_baseTableFields = Mage::getConfig()
                ->getNode( Testimonial_MageDoc_Block_Adminhtml_Retailer_Edit_Tab_Config_Source_Map::FIELD_LIST_CONFIG_PATH )->asArray();
            $this->_baseTableFields = array_merge($this->_baseTableFields, array('source_id' => 'source_id'));
        }

        if(empty($this->_directoryExtraFields)) {
            $config = Mage::getConfig()
                ->getNode( Testimonial_MageDoc_Model_Directory::DIRECTORIES_CONFIG_XML_PATH . '/' . static::DIRECTORY . '/extra_fields' );
            if($config){
                $this->_directoryExtraFields = array_flip(explode(',', $config->asArray()));
            }
        }

        $baseRowFields = array_intersect_key($row, $this->_baseTableFields);

        if (!$this->_validateRow($baseRowFields)){
            return false;
        }

        $bunch['base'][] = $baseRowFields;

        $this->_processPriceCostFields($baseRowFields);
        $this->_processQtyFields($baseRowFields);
        $bunch['preview'][] = $baseRowFields;
        $bunch['extended'][] = array_intersect_key($row, $this->_directoryExtraFields);
    }

    protected function _validateRow(&$row)
    {
        $requiredFields = array ('code_raw', 'manufacturer');
        foreach ($requiredFields as $field){
            if (empty($row[$field])){
                return false;
            }
        }
        return true;
    }

    protected function _processRow( &$row )
    {
        parent::_processRow($row);
        $origRow = $row;

        $importAdapterConfig = $this->getImportAdapterConfig();
        $sourceFieldsFilters = $this->_getSourceFieldFilters($importAdapterConfig);

        $templateProcessor = Mage::helper('magedoc_system');

        foreach($sourceFieldsFilters as $sourceFieldFilter) {
            $row[$sourceFieldFilter['base_table_field']] =
                $templateProcessor->processTemplate($sourceFieldFilter['path'], $row, $this);
        }
        $this->_lastOrigRow = $origRow;
        $this->_lastRow = $row;

        return $this;
    }

    protected function _getSourceFieldFilters($importAdapterConfig)
    {
        $defaultFilters = $this->_defaultFieldFilters;
        $adapterFilters = $importAdapterConfig->getSourceFieldsFilters();
        $filter = current($adapterFilters);
        while(count($defaultFilters) && $filter)
        {
            if(!isset($filter['base_table_field'])){
                $filter = next($adapterFilters);
                continue;
            }
            foreach ($defaultFilters as $key => $filterValue){
                if ($filterValue['base_table_field'] == $filter['base_table_field']){
                    unset($defaultFilters[$key]);
                }
            }
            $filter = next($adapterFilters);
        }
        return array_merge_recursive(
            $defaultFilters, 
            $adapterFilters);
    }

    protected function _processPriceCostFields( &$row )
    {
        if(isset($row['price'])) {
            $row['price'] = str_replace(',', '.', trim($row['price']));
        }

        if(isset($row['cost'])) {
            $row['cost'] = str_replace(',', '.', trim($row['cost']));
        }

        $importAdapterConfig = $this->getImportAdapterConfig();
        if( !isset($row['price']) || strlen($row['price']) == 0) {
            $row['price'] = $row['cost'] * (1 + $importAdapterConfig->getDiscountPercent()/100);
        } elseif( !isset($row['cost']) || strlen($row['cost']) == 0) {
            $row['cost'] = $row['price'] * (1 - $importAdapterConfig->getDiscountPercent()/100);
        }
    }

    protected function _processQtyFields( &$row )
    {
        $qtyFieldKeys = array('domestic_stock_qty','general_stock_qty','other_stock_qty', 'distant_stock_qty', 'qty'  );

        foreach ($qtyFieldKeys as $key) {
            $row[$key] = isset($row[$key]) ? $row[$key] : '';
            $row[$key] = str_replace(array('<', '>', ' '), '', $row[$key]);
            $row[$key] = preg_replace('/s+/', '', $row[$key]);
            if (strlen($row[$key])) {
                $defaultQty = (int)$this->getImportAdapterConfig()->getDefaultQty();
                switch (mb_strtolower($row[$key], 'UTF-8')) {
                    case 'нет':
                    case 'нет в наличии':
                    case 'no':
                    case 'not available':
                        $row[$key] = 0;
                    case '+++':
                        $row[$key] = $defaultQty * 100;
                        break;
                    case '++':
                        $row[$key] = $defaultQty * 10;
                        break;
                    case '+':
                    default:
                        $row[$key] = floor($row[$key]) > 0
                            ? floor($row[$key])
                            : $defaultQty;
                }
            } else {
                $row[$key] = 0;
            }
        }

        if( !isset($row['qty']) || $row['qty'] == 0 ) {
            $row['qty'] = $row['domestic_stock_qty'] + $row['general_stock_qty'] + $row['other_stock_qty']
                + $row['distant_stock_qty'];
        }

        return $this;
    }

    public function updatePreview($supMapIds = null)
    {
        $this->deleteSourceRecords();
        $this->insertImportRetailerDataPreview($supMapIds);
        $this->linkOffersToDirectory();
        return $this;
    }

    public function deleteSourceRecords()
    {
        $this->getResource()->deleteSourceRecords( $this->getSource()->getId() );
    }

    public function getNextBunch($size)
    {
        $headerMapFlipped = array_flip($this->getImportAdapterConfig()->getHeaderMap());

        $bunchSize = 0;
        $bunch = array();
        $totalRecords = $this->getSession()->getLastPriceSheetTotalRecords();

        while ( $this->getSourceAdapter()->valid() && (++$bunchSize <= $size)) {
            $totalRecords++;
            $row = $this->getSourceAdapter()->current();
            $row = array_intersect_key($row, $headerMapFlipped);

            $this->_processRow( $row );
            $this->_addRowToBunch($row, $bunch);
            $this->getSourceAdapter()->next();
        }

        $this->getSession()->setLastPriceSheetTotalRecords($totalRecords);

        return $bunch;
    }

    public function getLastValue ($fieldName, $data)
    {
        $value = null;
        if ((is_array($data) || $data instanceof Varien_Object) && !empty($data[$fieldName])){
            $value = $data[$fieldName];
        } elseif (!is_object($data) && !is_array($data) && !empty($data)){
            $value = $data;
        } elseif (isset($this->_lastRow[$fieldName])){
            $value = $this->_lastRow[$fieldName];
        }
        return $value;
    }

    public function getCode($row, $field = 'code_raw')
    {
        $codeRaw = preg_replace('/\s+/', ' ', trim($row[$field]));
        if (empty($codeRaw)){
            $codeRaw = preg_replace('/\s+/', ' ', trim($row['name']));
        }
        $manufacturer = trim($row['manufacturer']);
        $pos = isset($row['manufacturer']) && strlen($manufacturer)
            ? mb_stripos($codeRaw, $manufacturer, 0, 'UTF-8')
            : false;
        if ($pos !== false){
            $code = mb_substr($codeRaw, $pos + strlen($manufacturer), null, 'UTF-8');
            if (!mb_strlen($code, 'UTF-8')){
                $code = $pos ? mb_substr($codeRaw, 0, $pos, 'UTF-8') : null;
            }
        } else {
            $code = $codeRaw;
        }
        return $code;
    }

    public function getModel($row, $field = 'code_raw')
    {
        return $this->getCode($row,$field);
    }

    public function getPureCode($row, $field = 'code_raw', $trim = true)
    {
        $indexSettings = $this->_modelIndexSettings[0];
        $index = 1;
        $model = $this->getCode($row, $field);
        $code = $key = $model;
        if (preg_match('/'.$indexSettings['code_normalized_regexp'].'/iu', $key, $matches)){
            $code = $matches[$index];
            if ($trim){
                $code = trim($code, " \t\n\r\0\x0B\(\)");
            }
        } elseif (preg_match('/'.$this->_modelIndexSettings[1]['code_normalized_regexp'].'/iu', $key, $matches)){
            $code = $matches[$index];
        }
        return $code;
    }

    public function getPureModel($row, $field = 'code_raw')
    {
        $model = $this->getCode($row, $field);
        $code = $this->getPureCode($row, $field, false);
        $pos = mb_strrpos($model, $code, 'UTF-8');
        if ($pos +  mb_strlen($code, 'UTF-8') == mb_strlen($model, 'UTF-8')){
            $pureModel = trim(mb_substr($model, 0, $pos, 'UTF-8'));
        }
        return isset($pureModel) && strlen($pureModel)
            ? $pureModel
            : $model;
    }

    public function getCodeNormalized($row)
    {
        return $this->normalizeCode($this->getCode($row));
    }

    public function normalizeCode($code)
    {
        return Mage::helper('magedoc')->normalizeCode($code);
    }

    public function isFieldEmpty($fieldName, $row, $resultFieldName = null)
    {
        return empty($row[$fieldName])
            ? (!is_null($resultFieldName) ? $row[$resultFieldName] : $row)
            : null;
    }
}