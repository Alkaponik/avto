<?php
class Pw_Adminhtml_Model_System_Config_Source_Shipping_Multipletablerates
{
    public function toOptionArray()
    {
        $tableRate = Mage::getSingleton('multipletablerates_shipping/carrier_multipletablerates');
        $arr = array();
        
        foreach ($tableRate->getCode('condition_name') as $k=>$v) 
        {
        	$arr[] = array('value'=>$k, 'label'=>$v);
        }
        return $arr;
    }
}