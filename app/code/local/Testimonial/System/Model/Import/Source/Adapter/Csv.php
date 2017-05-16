<?php

class Testimonial_System_Model_Import_Source_Adapter_Csv extends Testimonial_System_Model_Import_Source_Adapter_Abstract
{
    /**
     * Field delimiter.
     *
     * @var string
     */
    protected $_delimiter = ';';

    /**
     * Field enclosure character.
     *
     * @var string
     */
    protected $_enclosure = '"';

    /**
     * Source file handler.
     *
     * @var resource
     */
    protected $_fileHandler;

    protected $_checkHeader = true;

    protected $_addHeader = true;

    protected $_colNames = array(1);

    /**
     * Object destructor.
     *
     * @return void
     */
    public function __destruct()
    {
        if (is_resource($this->_fileHandler)) {
            fclose($this->_fileHandler);
        }
    }

    /**
     * Method called as last step of object instance creation. Can be overrided in child classes.
     *
     * @return Mage_ImportExport_Model_Import_Adapter_Abstract
     */
    protected function _init()
    {
        $this->_fileHandler = fopen($this->_source, 'r');
        $this->rewind();
        return $this;
    }

    /**
     * Move forward to next element
     *
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->_currentRow = fgetcsv($this->_fileHandler, null, $this->_delimiter, $this->_enclosure);
        $this->_currentKey = $this->_currentRow ? $this->_currentKey + 1 : null;
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        // rewind resource, reset column names, read first row as current
        rewind($this->_fileHandler);
        $this->_currentRow = fgetcsv($this->_fileHandler, null, $this->_delimiter, $this->_enclosure);

        $this->_initColNames();

        if ($this->_currentRow) {
            $this->_currentKey = 0;
        }

    }

    /**
     * Seeks to a position.
     *
     * @param int $position The position to seek to.
     *
     * @throws OutOfBoundsException
     * @return void
     */
    public function seek($position)
    {

        if ($position != $this->_currentKey) {

            if (0 == $position) {
                return $this->rewind();
            } elseif ($position > 0) {
                if ($position < $this->_currentKey) {
                    $this->rewind();
                }
                while ($this->_currentRow = fgetcsv($this->_fileHandler, null, $this->_delimiter, $this->_enclosure)) {
                    if (++$this->_currentKey == $position) {
                        return;
                    }
                }
            }
            throw new OutOfBoundsException(Mage::helper('importexport')->__('Invalid seek position'));
        }
    }

    public function setEntityAdapter(Mage_ImportExport_Model_Import_Entity_Abstract $adapter)
    {
        $this->_entityAdapter = $adapter;
        return $this;
    }

    public function getEntityAdapter()
    {
        if (!$this->_entityAdapter) {
            Mage::throwException(Mage::helper('productimport')->__('Entity adapter is not set'));
        }
        return $this->_entityAdapter;
    }

    public function setDelimiter($delimiter)
    {
        $this->_delimiter = $delimiter;
        return $this;
    }

    public function setEnclosure($enclosure)
    {
        $this->_enclosure = $enclosure;
        return $this;
    }
}