<?php
abstract class Testimonial_MageDoc_Model_Retailer_Data_Import_Parser_Abstract extends Mage_Core_Model_Abstract
{
    const DEFAULT_ENCODING = 'UTF-8';
    const MAX_BUNCH_SIZE = 1000;
    const DIRECTORY = 'tecdoc';
    const ERROR_MESSAGES_LIMIT = 10;

    /**
     * @var Testimonial_MageDoc_Model_Retailer_Data_Import_Session_Source
     */
    protected $_source;
    protected $_sourceAdapter = NULL;
    protected $_headerMap;
    protected $_sourceFileNotValidRowsCount = 0;
    protected $_invalidRows = array();
    protected $_errorsCount = 0;

    abstract public function insertImportRetailerDataPreview( $supMapIds = null );
    abstract public function updatePreview($supMapIds = null);

    public function __construct( $params )
    {
        $this->_source = $params['source'];

        parent::__construct();
    }

    public function getSource(  )
    {
        return $this->_source;
    }

    public function getDirectoryCode()
    {
        return static::DIRECTORY;
    }

    public function getDirectoryModel()
    {
        return Mage::getSingleton('magedoc/directory')->getDirectory( $this->getDirectoryCode() );
    }

    /**
     * @return Testimonial_MageDoc_Model_Mysql4_Retailer_Data_Import_Parser_Abstract
     */
    public function getResource()
    {
        return parent::getResource()->setModel($this);
    }

    public function getRetailer()
    {
        return $this->getSource()->getSession()->getRetailer();
    }

    public function initSourceAdapter( $source )
    {
        $this->_source = $source;
        return $this;
    }

    public function getSourceAdapter( $headerMap = null, $options = array())
    {
        if(is_null($this->_sourceAdapter)) {
            Varien_Profiler::start('MageDoc_Import::INIT_SOURCE_ADAPTER');
            $sourceFile = $this->getSource()->getSourcePath();

            if (!$sourceFile || !is_readable($sourceFile)) {
                throw Mage::exception( 'Testimonial_MageDoc_Model_Retailer_Data_Import_Session', Mage::helper('magedoc')->__("File %s doesn't exists or is not readable.", $sourceFile) );
            }

            if (is_null($headerMap)){
                $headerMap = $this->getImportAdapterConfig()->getHeaderMap();
            }

            $options = array_merge($this->getImportAdapterConfig()->getSourceAdapterConfig(), $options);

            try {
                $this->_sourceAdapter = Mage::getModel($this->getImportAdapterConfig()->getSourceAdapterModelName(), $sourceFile)
                    ->setConfig($options)
                    ->setHeaderMap($headerMap);
            } catch (Exception $e) {
                Mage::logException($e);
                throw Mage::exception( 'Testimonial_MageDoc_Model_Retailer_Data_Import_Session', Mage::helper('magedoc')->__("Unable to init source adapter: %s", $e->getMessage()) );
            }
            Varien_Profiler::stop('MageDoc_Import::INIT_SOURCE_ADAPTER');
        }

        return $this->_sourceAdapter;
    }

    public function getFilePreviewCollection( $rowLimit, $columnLimit = 30 )
    {
        Varien_Profiler::start('MageDoc_Import::getFilePreviewCollection');
        $collection = new Varien_Data_Collection();
        if (!$this->getSource()->getId()){
            return $collection;
        }

        $sourceAdapter = $this->getSourceAdapter(
            range(1, $columnLimit),
            array(
                array(
                    'option' => 'row_limit',
                    'value'  => $rowLimit
                )
            ));
        //$sourceAdapter->rewind();

        $counter = 1;
        while( $sourceAdapter->valid() && $collection->count() <= $rowLimit) {
            $row = $sourceAdapter->current();
            $this->_decodeSourceRow($row);
            $collection->addItem(new Varien_Object(array_merge(array('item_index' => $counter), $row)));

            $sourceAdapter->next();
            $counter++;
        }
        Varien_Profiler::stop('MageDoc_Import::getFilePreviewCollection');

        return $collection;
    }

    public function getImportAdapterConfig()
    {
        return $this->getSource()->getConfig();
    }

    public function getSession()
    {
        return $this->getSource()->getSession();
    }

    protected function _processRow( &$row )
    {
        $row['retailer_id'] = $this->getImportAdapterConfig()->getRetailerId();
        $row['source_id'] = $this->getSource()->getId();
        foreach($this->getSource()->getConfig()->getDefaultValues() as $column => $defaultValue) {
            if(!isset($row[$column]) || $row[$column] === '') {
                $row[$column] = $defaultValue;
            }
        }
        $this->_decodeSourceRow($row);


        return $this;
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
            if($this->_isRowDataValid($row) || $this->getImportAdapterConfig()->isUpdateConfig()) {
                $row = array_intersect_key($row, $headerMapFlipped);
                $this->_processRow( $row );
                $this->_addRowToBunch($row, $bunch);
            } else {
                if( !$this->_isRowEmpty($row) ) {
                    $this->addRowError(Mage::helper('magedoc')->__('Invalid row data #%s (%s)'), $totalRecords, null, $row);
                }

                if ($this->_sourceFileNotValidRowsCount >= Mage::getStoreConfig('magedoc/import/price_source_errors_limit')) {
                    $retailerName = $this->getRetailer()->getName();
                    $fileName = $this->_sourceFile;
                    Mage::throwException(
                        __('Too many not valid records in the %s retailer price source file (%s).', $retailerName,  $fileName)
                    );
                }
            }
            $this->getSourceAdapter()->next();
        }

        $this->getSession()->setLastPriceSheetTotalRecords($totalRecords);

        return $bunch;
    }

    protected function _addRowToBunch( $row, &$bunch )
    {
        $bunch[] = $row;
    }


    protected function _isRowDataValid( $row )
    {
        $sourceFilterMap = $this->getImportAdapterConfig()->getSourceFieldsFilters();

        $sourceFilterFields = array();
        array_walk($sourceFilterMap, function( $item ) use (&$sourceFilterFields) {
                $sourceFilterFields[$item['base_table_field']] = $item['base_table_field'];
            });


        if (!$requiredFields = $this->getImportAdapterConfig()->getUpdateKeyFields()) {
            $requiredFields = array(
                array('code', 'code_raw', 'code_normalized'),
                array('manufacturer', 'manufacturer_id'),
                array('cost', 'price'),
            );
        }

        foreach($requiredFields as $fieldGroup) {
            if(!is_array($fieldGroup)) {
                if( !isset( $sourceFilterFields[$fieldGroup] ) &&
                    (!isset($row[$fieldGroup]) || trim($row[$fieldGroup]) == '') &&
                    $this->getImportAdapterConfig()->getDefaultValues($fieldGroup)
                ) {
                    return false;
                }
            } else {
                $condition = false;
                foreach($fieldGroup as $field) {
                    if( isset($row[$field]) && trim($row[$field]) != ''
                        || isset($sourceFilterFields[$field]) || $this->getImportAdapterConfig()->getDefaultValues($field)) {
                        $condition = true;
                        break;
                    }
                }
                if($condition == false) {
                    return false;
                }
            }
        }
        return true;
    }

    protected function _isRowEmpty( $row ) {

        foreach($row as $value) {
            if( trim($value) != '' ) {
                return false;
            }
        }

        return true;
    }

    public function prepareSourceAdapter()
    {
        $this->getSourceAdapter()->rewind();
        $this->getSourceAdapter()->seek($this->getImportAdapterConfig()->getStartingRecord() - 1);

        $this->getSession()->setLastPriceSheetTotalRecords(0);

        return $this;
    }

    public function prepareTables()
    {
        $this->getResource()
            ->prepareBaseTable()
            ->preparePreviewTable();

        return $this;
    }


    public function updateBaseTableSupplierId( $ids = null )
    {
        $this->getResource()->updateBaseTableSupplierId($ids);
    }

    public function getUpdateKeys()
    {
        $updateByKeyValue = $this->getSource()->getConfig()->getUpdateByKey();
        return  Mage::getSingleton('magedoc/source_import_update_key')->getKeyFieldsByValue($updateByKeyValue);
    }

    public function isSourceParsedIntoBase()
    {
        return $this->getResource()->isSourceParsedIntoBase();
    }

    public function getPriceRecordsLinkedToSupplier()
    {
        return $this->getResource()->getPriceRecordsLinkedToSupplier();
    }

    public function linkOffersToDirectory( )
    {
        Mage::getSingleton('magedoc/directory')->getDirectory( static::DIRECTORY )
            ->linkOffersToDirectory( $this->getSource() );

        return $this;
    }

    /**
     * Add error with corresponding current data source row number.
     *
     * @param string $errorCode Error code or simply column name
     * @param int $errorRowNum Row number.
     * @param string $colName OPTIONAL Column name.
     * @return Mage_ImportExport_Model_Import_Adapter_Abstract
     */
    public function addRowError($errorCode, $errorRowNum, $colName = null, $data = array())
    {
        $this->_invalidRows[$errorRowNum] = true;
        $this->_errorsCount ++;
        if ($this->_errorsCount <= self::ERROR_MESSAGES_LIMIT){
            $this->getSession()->addError($errorCode, $errorRowNum, $colName, $data);
        }

        return $this;
    }

    /**
     * Returns error counter value.
     *
     * @return int
     */
    public function getErrorsCount()
    {
        return $this->_errorsCount;
    }

    protected function _decodeSourceRow(&$row)
    {
        $priceEncoding = $this->getImportAdapterConfig()->getPriceEncoding();
        $defaultEncoding = self::DEFAULT_ENCODING;
        array_walk(
            $row,
            function ( &$item ) use ($priceEncoding, $defaultEncoding)
            {
                $item = trim($item);
                if( $priceEncoding != $defaultEncoding) {
                    $item = iconv($priceEncoding, $defaultEncoding, $item);
                }
            }
        );
        return $row;
    }

    public function importBrands()
    {
        return $this->getResource()->importBrands();
    }
}