<?php

class Testimonial_System_Model_Import_Source_Adapter_Xlsx extends Testimonial_System_Model_Import_Source_Adapter_Abstract
{
    protected $_checkHeader = true;

    protected $_addHeader = true;

    /** @var PHPExcel */
    protected $_phpExcel = null;
    /** @var PHPExcel_Worksheet_RowIterator $_rowIterator */
    protected $_rowIterator;
    /** @var PHPExcel_Worksheet $_ecelWorksheet */
    protected $_excelWorksheet;

    protected $_sheetName = null;

    protected $_sheetIndex = 0;

    protected $_sheetIndexes = array(0);

    protected $_colNames = array(1);

    public function setSheetName($sheetName = null)
    {
        if (!is_null($sheetName)){
            $this->_sheetName = $sheetName;
        }
        return $this;
    }

    protected function _setSheetName($sheetName = null)
    {
        $this->_excelWorksheet = $this->_getPhpExcel()->getSheetByName($this->_sheetName);
        if( (!$this->_excelWorksheet instanceof PHPExcel_Worksheet) ) {
            Mage::throwException(__('Wrong sheet name: %s', $this->_sheetName));
        }
        $this->_rowIterator   = $this->_excelWorksheet->getRowIterator();
        return $this;
    }

    public function getSheetName()
    {
        return $this->_sheetName;
    }

    public function setSheetIndex($sheetIndex = null)
    {
        if (!is_null($sheetIndex)){
            if (!is_array($sheetIndex) && strlen($sheetIndex)){
                $sheetIndex = explode(',', $sheetIndex);
            }
            if (!empty($sheetIndex)){
                $this->_sheetIndexes = $sheetIndex;
                $this->_sheetIndex = current($this->_sheetIndexes);
            }
        }
        return $this;
    }

    protected function _setSheetIndex($sheetIndex = null)
    {
        if (!is_null($sheetIndex)){
            $this->_sheetIndex = $sheetIndex;
        }

        if ($this->_sheetIndex < 0
            || $this->_sheetIndex >= $this->_getPhpExcel()->getSheetCount()){
            Mage::throwException(Mage::helper('testimonial_system')->__('Sheet #%s doesn\'t exist', $this->_sheetIndex));
        }
        $this->_excelWorksheet = $this->_getPhpExcel()->getSheet($this->_sheetIndex);
        $this->_rowIterator   = $this->_excelWorksheet->getRowIterator();
        return $this;
    }

    /**
     *
     * @return PHPExcel_Worksheet_RowIterator
     */
    public function getSheetRowIterator()
    {
        if (empty($this->_rowIterator)){
            try {
                if ($this->getSheetName()){
                    $this->_setSheetName();
                }else {
                    $this->_setSheetIndex();
                }

            } catch (PHPExcel_Exception $exc) {
                Mage::throwException($exc->getTraceAsString());
            }
        }
        return $this->_rowIterator;
    }

    protected function _getPhpExcel()
    {
        if( is_null($this->_phpExcel) ) {
            /** @var $reader PHPExcel_Reader_Abstract */
            /*PHPExcel_CachedObjectStorageFactory::initialize('ReadonlyMemory');
            PHPExcel_Settings::setLibXmlLoaderOptions(PHPExcel_Settings::getLibXmlLoaderOptions()
                | LIBXML_COMPACT | LIBXML_NOBLANKS);*/
            if ($this->hasExtensionType()){
                $reader = PHPExcel_IOFactory::createReader($this->getExtensionType());
                if (!$reader->canRead($this->_source)) {
                    Mage::throwException(Mage::helper('testimonial_system')->__('Unable to read file %s', $this->_source));
                }
            } else{
                $reader = PHPExcel_IOFactory::createReaderForFile($this->_source);
            }
            $reader->setReadDataOnly(true);
            if ($this->getRowLimit()){
                $chunkFilter = new Testimonial_System_Model_Import_Source_Adapter_Xlsx_ChunkReadFilter();
                $chunkFilter->setRows(0, $this->getRowLimit());
                $reader->setReadFilter($chunkFilter);
            }
            $this->_phpExcel = $reader->load($this->_source);
        }

        return $this->_phpExcel;
    }


    public function next()
    {
        $this->getSheetRowIterator()->next();
        if(!$this->getSheetRowIterator()->valid()) {
            if ($sheetIndex = next($this->_sheetIndexes)){
                $this->_setSheetIndex($sheetIndex);
            }
        }
        $this->_setCurrentRow();
        $this->_currentKey = $this->_currentRow ? $this->_currentKey + 1 : null;
    }

    public function rewind()
    {
        $sheetIndex = reset($this->_sheetIndexes);
        if ($this->_sheetIndex != $sheetIndex){
            $this->_setSheetIndex($sheetIndex);
        }
        $this->getSheetRowIterator()->rewind();
        $this->_setCurrentRow();

        $this->_initColNames();

        if ($this->_currentRow) {
            $this->_prepareRow($this->_currentRow);
            $this->_currentKey = 0;
        }
    }

    public function seek($position)
    {
        if ($position != $this->_currentKey) {

            if (0 == $position) {
                return $this->rewind();
            } elseif ($position > 0) {
                $this->getSheetRowIterator()->seek($position+1);
                $this->_setCurrentRow();
                $this->_currentKey = $position;
                return;
            }
            throw new OutOfBoundsException(Mage::helper('importexport')->__('Invalid seek position'));
        }
    }

    protected function _setCurrentRow()
    {
        $this->_currentRow = array();

        if(!$this->getSheetRowIterator()->valid()) {
            return $this;
        }
        $iterator = $this->getSheetRowIterator()->current()->getCellIterator();
        $iterator->setIterateOnlyExistingCells(false);
        foreach ($iterator as $key => $value) {
            $this->_currentRow[$key] = $value->getValue();
        }
        return $this;
    }
}

class Testimonial_System_Model_Import_Source_Adapter_Xlsx_ChunkReadFilter implements PHPExcel_Reader_IReadFilter
{
    private $_startRow = 0;
    private $_endRow   = 0;

    /**  Set the list of rows that we want to read  */
    public function setRows($startRow, $chunkSize) {
        $this->_startRow = $startRow;
        $this->_endRow   = $startRow + $chunkSize;
    }

    public function readCell($column, $row, $worksheetName = '') {
        //  Only read the heading row, and the configured rows
        if (($row == 1) || ($row >= $this->_startRow && $row < $this->_endRow)) {
            return true;
        }
        return false;
    }
}
