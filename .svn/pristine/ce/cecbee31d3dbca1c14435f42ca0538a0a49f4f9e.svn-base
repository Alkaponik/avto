<?php

class Testimonial_Intime_Model_Source_Tracking_Backdelivery extends Testimonial_Intime_Model_Source_Tracking_Abstract
{
    function __construct()
    {
        $this->_pathToXml = 'default/carriers/intime/tracking/back_delivery';
    }

    const BACK_DELIVERY_NO  = 0;
    const BACK_DELIVERY_YES = 1;

    public function getBackDelivery()
    {
        return $this->getAllOptions($this->_pathToXml);
    }
}