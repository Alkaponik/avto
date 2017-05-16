<?php
class Testimonial_ProductHistory_Helper_Data extends Mage_Core_Helper_Abstract
{

	public function getProductHistoryCollection($id)
	{
		$currentTime = Mage::getModel('core/date')->timestamp(time());
		$filterTime = $currentTime - (30 * 24 * 60 * 60);
		return Mage::getModel('producthistory/history')->getCollection()->addFieldToFilter('product_id',  array('eq' => $id))->addFieldToFilter('date_update',  array('gt' => $filterTime));
	}

}
