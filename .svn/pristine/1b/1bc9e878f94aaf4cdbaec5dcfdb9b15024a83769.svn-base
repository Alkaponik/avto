<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract import adapter
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Testimonial_System_Model_Import_Source_Adapter_Abstract extends Varien_Object
    implements SeekableIterator
{
    const IGNORE_COLUMN_NAME = 'IGNORE';

    /**
     * Column names array.
     *
     * @var array
     */
    protected $_colNames;

    /**
     * Quantity of columns in first (column names) row.
     *
     * @var int
     */
    protected $_colQuantity;


    /**
     * Current row.
     *
     * @var array
     */
    protected $_currentRow = null;

    /**
     * Current row number.
     *
     * @var int
     */
    protected $_currentKey = null;

    /**
     * Source file path.
     *
     * @var string
     */
    protected $_source;

    /**
     * Adapter object constructor.
     *
     * @param string $source Source file path.
     * @throws Mage_Core_Exception
     * @return void
     */

    protected $_headerMap = array();

    protected $_ignoreColumns = array();

    protected $_options = array();

    protected $_checkHeader = false;

    protected $_addHeader = false;

    protected $_mapColumns = false;

    protected $_addMissingColumns = false;

    protected $_entityAdapter = null;

    protected $_defaultColValues = array();

    protected $_lastColumnIndex = 0;

    final public function __construct($source)
    {
        if (!is_string($source)) {
            Mage::throwException(Mage::helper('testimonial_system')->__('Source file path must be a string'));
        }
        if (!is_readable($source)) {
            Mage::throwException(Mage::helper('testimonial_system')->__("%s file does not exists or is not readable", $source));
        }

        $this->_source = $source;

        $this->_init();

        // validate column names consistency
        if (is_array($this->_colNames) && !empty($this->_colNames)) {
            $this->_colQuantity = count($this->_colNames);

            if (count(array_unique($this->_colNames)) != $this->_colQuantity) {
                Mage::throwException(Mage::helper('testimonial_system')->__('Column names have duplicates'));
            }
        } else {
            Mage::throwException(Mage::helper('testimonial_system')->__('Column names is empty or is not an array'));
        }
    }

    /**
     * Method called as last step of object instance creation. Can be overridden in child classes.
     *
     * @return Mage_ImportExport_Model_Import_Adapter_Abstract
     */
    protected function _init()
    {
        return $this;
    }

    public function current()
    {
        $this->_prepareRow($this->_currentRow);
        $row = $this->_beforeFetchRow(array_merge($this->_current(), $this->_defaultColValues));

        return $row;
    }

    /**
     * Return the current element.
     *
     * @return mixed
     */
    public function _current()
    {
        return array_combine(
            $this->_colNames,
            count($this->_currentRow) != $this->_colQuantity
                    ? array_pad($this->_currentRow, $this->_colQuantity, '')
                    : $this->_currentRow
        );
    }

    protected function _beforeFetchRow($row)
    {
        return $row;
    }

    /**
     * Column names getter.
     *
     * @return array
     */
    public function getColNames()
    {
        return $this->_colNames;
    }

    public function setHeaderMap(array $headerMap)
    {
        $this->_headerMap = $headerMap;

        $this->__construct($this->_source);
        $this->rewind();

        return $this;
    }

    public function getHeaderMap()
    {
        return $this->_headerMap;
    }
    /**
     * Return the key of the current element.
     *
     * @return int More than 0 integer on success, integer 0 on failure.
     */
    public function key()
    {
        return $this->_currentKey;
    }

    /**
     * Seeks to a position.
     *
     * @param int $position The position to seek to.
     * @return void
     */
    public function seek($position)
    {
        Mage::throwException(Mage::helper('testimonial_system')->__('Not implemented yet'));
    }

    /**
     * Checks if current position is valid.
     *
     * @return boolean Returns true on success or false on failure.
     */
    public function valid()
    {
        return !empty($this->_currentRow);
    }

    /**
     * Check source file for validity.
     *
     * @return Mage_ImportExport_Model_Import_Adapter_Abstract
     */
    public function validateSource()
    {
        return $this;
    }

    /**
     *
     * @return Testimonial_MageDoc_Model_ConfigAdapter
     */
    public function getConfigAdapter()
    {
        if (!$this->_configAdapter) {
            Mage::throwException(Mage::helper('testimonial_system')->__('Config adapter is not set'));
        }
        return $this->_configAdapter;
    }

    /**
     *
     * @param Testimonial_MageDoc_Model_ConfigAdapter $adapter
     */
    public function setConfigAdapter($adapter)
    {
        $this->_configAdapter = $adapter;
    }

    public function getRetailerId()
    {
        if (isset($this->_retailerId)){
            return $this->_retailerId;
        }
        else {
            return false;
        }
    }

    public function beforeImport()
    {
        return $this;
    }

    public function afterImport()
    {
        return $this;
    }

    public function setConfig($config)
    {
        foreach ($config as $option){
            $this->setDataUsingMethod(
                $option['option'],
                str_replace(array('\t', '\n', '\r'), array("\t", "\n", "\r"), $option['value']));
        }
        return $this;
    }

    protected function _initColNames()
    {
        if (!empty($this->_headerMap)) {

            if ($this->_checkHeader && $this->_addHeader) {
                $this->_colNames = array();
            } else {
                $this->_colNames = $this->_currentRow;
            }

            if ($this->_mapColumns){
                foreach ($this->_colNames as $key => $colName){
                    if (!isset($this->_headerMap[$colName])
                        || $this->_headerMap[$colName] == self::IGNORE_COLUMN_NAME){
                        unset($this->_colNames[$key]);
                        $this->_ignoreColumns[] = $key;
                    }else{
                        $this->_colNames[$key] = $this->_headerMap[$colName];
                    }
                }
            } else {
                $columnsCount = max(count($this->_currentRow), count($this->_headerMap));
                for ($key = 0; $key < $columnsCount; $key++) {
                    $headerMapKey = $key + 1;
                    if (!isset($this->_headerMap[$headerMapKey])
                        || $this->_headerMap[$headerMapKey]
                        == self::IGNORE_COLUMN_NAME
                    ) {
                        $this->_ignoreColumns[] = $key;
                        unset($this->_colNames[$key]);
                    } else {
                        $this->_colNames[$key] = $this->_headerMap[$headerMapKey];
                    }
                }
            }

            $this->_colQuantity = count($this->_colNames);
            $this->_lastColumnIndex = max(array_keys($this->_colNames));
        }
        return $this;
    }

    protected function _removeIgnoredColumns(&$row)
    {
        foreach ($this->_ignoreColumns as $key){
            unset($row[$key]);
        }
        return $row;
    }

    protected function _prepareRow(&$row)
    {
        $this->_removeTail($row);
        $this->_removeIgnoredColumns($row);
        if ($this->_addMissingColumns){
            $this->_addMissingColumns($row);
        }

        return $row;
    }

    protected function _removeTail(&$row)
    {
        end($row);
        $lastKey = key($row);
        for ($i = $this->_lastColumnIndex+1; $i<= $lastKey; $i++){
            unset($row[$i]);
        }
        return $row;
    }

    protected function _addMissingColumns(&$row)
    {
        foreach ($this->_colNames as $index => $colName)
        {
            if (!isset($row[$index])){
                $row[$index] = null;
            }
        }
        return $row;
    }

    public function setDefaultColValues($values)
    {
        $this->_defaultColValues = $values;
    }

    public function addHeader($flag = true)
    {
        $this->_addHeader = $flag;
        return $this;
    }

    public function mapColumns($flag = true)
    {
        $this->_mapColumns = $flag;
        return $this;
    }
}
