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
class Phoenix_SmsGateway_Model_Config
{
	/**
	 * Retrieve array of gatewys
	 *
	 * @return array
	*/
	public function getApis()
	{
		$modes = array();
		foreach (Mage::getConfig()->getNode('phoenix/smsgateway/api')->asArray() as $data) {
			$modes[$data['code']] = $data['name'];
		}
		return $modes;
	}
}