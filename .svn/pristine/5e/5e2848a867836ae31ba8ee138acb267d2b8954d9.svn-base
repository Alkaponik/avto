<?php

class Ak_NovaPoshta_Model_Source_Tracking_Backdelivery extends Ak_NovaPoshta_Model_Source_Tracking_Abstract
{
    function __construct()
    {
        $this->_pathToXml = 'default/carriers/novaposhta/tracking/back_delivery';
    }

    const BACK_DELIVERY_NO  = 0;
    const BACK_DELIVERY_YES = 1;
    const BACK_DELIVERY_NONE= 2;

    public function getBackDelivery()
    {
        return $this->getAllOptions($this->_pathToXml);
    }
}