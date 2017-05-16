<?php
class Pw_Adminhtml_Model_System_Config_Backend_Shipping_Multipletablerates extends Mage_Core_Model_Config_Data
{
    public function _afterSave()
    {
		Mage::getResourceModel('multipletablerates_shipping/carrier_multipletablerates')->uploadAndImport($this);
    }
}
