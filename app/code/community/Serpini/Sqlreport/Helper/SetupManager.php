<?php
class Serpini_Sqlreport_Helper_SetupManager extends Mage_Core_Helper_Abstract {
	
	
	public function saveSetup($lista){
		$setupmanager = Mage::getModel('sqlreport/setup');
		$setupList = explode("@@|@@",$lista);
		foreach($setupList as $groupData){
			if($groupData!=""){
				$setupDataArray = explode("@@=@@",$groupData);
				$name=$setupDataArray[0];
				$value = $setupDataArray[1];
				$setupmanager->setValue($name,$value);
			}
		}
		return $setupmanager->saveSetup();
	}
	

	
	
}