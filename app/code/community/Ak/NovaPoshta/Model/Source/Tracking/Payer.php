<?php

class Ak_NovaPoshta_Model_Source_Tracking_Payer extends Ak_NovaPoshta_Model_Source_Tracking_Abstract
{
    function __construct()
    {
        $this->_pathToXml = 'default/carriers/novaposhta/tracking/payer';
    }


    const PAYER_SENDER      = 0;
    const PAYER_RECIPIENT   = 1;
    const PAYER_THIRD_PARTY = 2;

    public function getPayer()
    {
        return $this->getAllOptions();
    }
}