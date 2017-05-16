<?php

class Ak_NovaPoshta_Model_Source_Tracking_Payment extends Ak_NovaPoshta_Model_Source_Tracking_Abstract
{
    function __construct()
    {
        $this->_pathToXml = 'default/carriers/novaposhta/tracking/from_payment';
    }


    const FROM_PAYMENT_BANK_PAYMENT = 0;
    const FROM_PAYMENT_CASH         = 1;

    public function getPaymentMethod()
    {
        return $this->getAllOptions();
    }
}