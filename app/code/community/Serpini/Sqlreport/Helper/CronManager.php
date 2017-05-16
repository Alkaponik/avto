<?php

class Serpini_Sqlreport_Helper_CronManager extends Mage_Core_Helper_Abstract {
	
	public function addCron($report_id){
		
		//$time = $this->getData('groups/cronjob/fields/time/value');
        //$frequency = $this->getData('groups/cronjob/fields/frequency/value');
        $report = Mage::getModel('sqlreport/report');
		$report->loadReport($report_id);
		if($report->isError()===false){
			$CRON_STRING_PATH   = 'crontab/jobs/sqlreport_'.$report->getId().'/schedule/cron_expr';
			$CRON_MODEL_PATH 	= 'crontab/jobs/sqlreport_'.$report->getId().'/run/model';
			$CRON_VARIABLE		= 'crontab/jobs/sqlreport_'.$report->getId().'/cron/id';
	        
	        $frequency = $report->getAtribute('cronString');
	 
	        try {
	            Mage::getModel('core/config_data')
	                ->load($CRON_STRING_PATH, 'path')
	                ->setValue($frequency)
	                ->setPath($CRON_STRING_PATH)
	                ->save();
	                
	            Mage::getModel('core/config_data')
	                ->load($CRON_MODEL_PATH, 'path')
	                ->setValue('sqlreport/CronObserver::executeReport')
	                ->setPath($CRON_MODEL_PATH)
	                ->save();
	                
	            Mage::getModel('core/config_data')
	                ->load($CRON_VARIABLE, 'path')
	                ->setValue($report->getId())
	                ->setPath($CRON_VARIABLE)
	                ->save();
	                
	            Mage::getConfig()->cleanCache();
	            
	            return true;
	        } catch (Exception $e) {
	            return Mage::helper('catalog')->__('Unable to save Cron expression');
	        }
		}else{
			return $report->getErrorMsg();
		}

	}
	
	public function deleteCron($report_id){
		
		$report = Mage::getModel('sqlreport/report');
		$report->loadReport($report_id);
		
		if($report->isError()===false){
			try{
				$CRON_STRING_PATH   = 'crontab/jobs/sqlreport_'.$report_id.'/schedule/cron_expr';
				$CRON_MODEL_PATH 	= 'crontab/jobs/sqlreport_'.$report_id.'/run/model';
				$CRON_VARIABLE		= 'crontab/jobs/sqlreport_'.$report_id.'/cron/id';
				$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
				
				$installer->deleteConfigData($CRON_STRING_PATH);
				$installer->deleteConfigData($CRON_MODEL_PATH);
				$installer->deleteConfigData($CRON_VARIABLE);
				
				Mage::getConfig()->cleanCache();
				
			}catch (Exception $e) {
	            return Mage::helper('catalog')->__('Unable to delete Cron expression');
	        }
		}else{
			return $salida;
		}
		return true;
		
	}
	
}