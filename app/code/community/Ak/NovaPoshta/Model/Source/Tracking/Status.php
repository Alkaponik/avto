<?php

class Ak_NovaPoshta_Model_Source_Tracking_Status extends Ak_NovaPoshta_Model_Source_Tracking_Abstract
{
    function __construct()
    {
        $this->_pathToXml = 'default/carriers/novaposhta/tracking/status';
    }

    const STATUS_INVALID_NUMBER                  = 0;
    const STATUS_NUMBER_NOT_FOUND                = 1;
    const STATUS_DOCUMENT_CREATED_AND_PROCESSING = 2;
    const STATUS_SHIPMENT_NOT_RECEIVED           = 3;
    const STATUS_SHIPMENT_RECEIVED               = 4;
    const STATUS_SERVICE_TEMPORARILY_UNAVAILABLE = 505;

    public function getStatus()
    {
        return $this->getAllOptions();
    }
}