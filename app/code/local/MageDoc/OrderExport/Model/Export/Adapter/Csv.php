<?php

class MageDoc_OrderExport_Model_Export_Adapter_Csv extends MageDoc_OrderExport_Model_Export_Adapter_Abstract
{
    const FIELD_DELIMITER = '|';

    protected $_adapter;

    protected $_rowStart = true;

    protected function _init($fileName)
    {
        if (is_array($fileName)){
            $fileName = current($fileName);
        }
        $handle = fopen($fileName, 'w+');
        if ($handle) {
            $this->_adapter = $handle;
        } else {
            Mage::throwException("Unable to open file $fileName");
        }
        return $this;
    }

    public function __destruct()
    {
        if (isset($this->_adapter)){
            fclose($this->_adapter);
        }
    }

    public function writeRaw($rawData)
    {
        return $this;
    }

    public function writeElement($key, $value)
    {
        fwrite($this->_adapter, "$value\n");
        return $this;
    }

    public function startElement($key)
    {
        return $this;
    }

    public function endElement()
    {
        fwrite($this->_adapter, "\n");
        $this->_rowStart = true;
        return $this;
    }

    public function writeAttribute($key, $value)
    {
        if ($this->_rowStart){
            fwrite($this->_adapter, $value);
            $this->_rowStart = false;
        }else{
            fwrite($this->_adapter, self::FIELD_DELIMITER.$value);
        }
        return $this;
    }
}