<?php

class Ak_NovaPoshta_Model_Source_Tracking_Delivery extends Ak_NovaPoshta_Model_Source_Tracking_Abstract
{
    function __construct()
    {
        $this->_pathToXml = 'default/carriers/novaposhta/tracking/delivery_form';
    }

    const DELIVERY_TO_WAREHOUSES = 0;
    const DELIVERY_TO_DOORS      = 1;

    public function getDeliveryForm()
    {
        return $this->getAllOptions();
    }
}