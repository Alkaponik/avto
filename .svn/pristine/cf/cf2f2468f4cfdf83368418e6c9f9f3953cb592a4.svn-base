<?php

class Testimonial_Intime_Model_Source_Tracking_Status extends Testimonial_Intime_Model_Source_Tracking_Abstract
{
    function __construct()
    {
        $this->_pathToXml = 'default/carriers/intime/tracking/status';
    }

    const STATUS_NOT_FAUND           = 0;
    const STATUS_NO_REQUEST          = 1;
    const STATUS_DELIVERY_ACCEPTED   = 2;
    const STATUS_SHIPMENT_IN_TRANSIT = 3;
    const STATUS_SHIPMENT_RECEIVED   = 4;
    const STATUS_LOADS_OF_STOCK      = 5;
    const STATUS_RETURN_OF_CARGO     = 6;

    public function getStatus()
    {
        return $this->getAllOptions();
    }
}