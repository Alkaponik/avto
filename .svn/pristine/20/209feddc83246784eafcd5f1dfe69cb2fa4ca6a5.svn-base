<?php

interface MageDoc_OrderExport_Model_Export_Adapter_Interface
{
    public function writeRaw($rawData);

    public function startDocument($version, $encoding);

    public function endDocument();

    public function startElement($key);

    public function endElement();

    public function writeAttribute($key, $value);

    public function writeElement($key, $value);
}