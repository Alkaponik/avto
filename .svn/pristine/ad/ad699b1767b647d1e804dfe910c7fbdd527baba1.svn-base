<?php
/**
 * Phoenix SMS Gateway
 *
 * NOTICE OF LICENSE
 * 
 * This source file is subject to license that is bundled with
 * this package in the file LICENSE.txt.
 *
 * @category   Phoenix
 * @package    Phoenix_SmsGateway
 * @copyright  Copyright (c) 2009 by Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 */

class Phoenix_SmsGateway_Model_Source_Apis
{
    public function toOptionArray()
    {
        $options =  array();       ;
        foreach (Mage::getSingleton('smsgateway/config')->getApis() as $code => $name) {
            $options[] = array(
                   'value' => $code,
                   'label' => $name
            );
        }

        return $options;
    }
}



