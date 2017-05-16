<?php

abstract class MageDoc_OrderExport_Model_Export_Adapter_Abstract
    implements MageDoc_OrderExport_Model_Export_Adapter_Interface
{
    public function __construct($fileName)
    {
        $this->_init($fileName);
        return $this;
    }

    abstract protected function _init($fileName);

    public function writeRaw($rawData)
    {
        return $this;
    }

    public function startDocument($version, $encoding)
    {
        return $this;
    }

    public function endDocument()
    {
        return $this;
    }

    public function text($value)
    {
        return $this;
    }
    
}