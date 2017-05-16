<?php

class MageDoc_OrderExport_Model_Export_Adapter_SimpleXml extends MageDoc_OrderExport_Model_Export_Adapter_Abstract
{
    protected $_adapter;

    protected $_rowStart = true;

    protected function _init($fileName)
    {
        if (is_array($fileName)){
            $fileName = current($fileName);
        }
        $writer = new XMLWriter();
        if ($writer->openURI($fileName)){
            Mage::log("Adapter_Xml: Strating output to file $fileName");
            $this->_adapter = $writer;
        }else{
            Mage::throwException("Unable to open file $fileName");
        }
        return $this;
    }

    public function __destruct()
    {
        if (isset($this->_adapter)){
            unset($this->_adapter);
        }
    }

    public function startDocument($version, $encoding)
    {
        $this->_adapter->startDocument($version, $encoding);
        return $this;
    }

    public function endDocument()
    {
        $this->_adapter->endDocument();
        return $this;
    }

    public function writeRaw($rawData)
    {
        $this->_adapter->writeRaw($rawData);
        return $this;
    }

    public function writeElement($key, $value)
    {
        $this->_adapter->writeElement($key, $value);
        return $this;
    }

    public function startElement($key)
    {
        $this->_adapter->startElement($key);
        return $this;
    }

    public function endElement()
    {
        $this->_adapter->endElement();
        return $this;
    }

    public function writeAttribute($key, $value)
    {
        $this->_adapter->writeAttribute($key, $value);
        return $this;
    }

    public function text($value)
    {
        $this->_adapter->text($value);
        return $this;
    }
}