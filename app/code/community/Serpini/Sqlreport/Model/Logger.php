<?php
class Serpini_Sqlreport_Model_Logger extends Mage_Core_Model_Abstract
{
	
	private static $activ = true; 
	
	protected function _construct(){
		$this->_init('sqlreport/logger');
	}
	
	public function disable(){
		self::$activ = false;
	}
	
	public function active(){
		self::$activ = true; 
	}
	
	public function logReport($action,$report){
		
	}
	
	public function logCombo($action,$combo){
		
	}
	
	public function logReportEmail($report,$text,$to,$subject,$cc,$bcc,$error){
		try{
			$utils= Mage::helper('sqlreport/Utils');
			$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
			$connection->beginTransaction();
			
			$dataInsert = array(
					'report_id' => $report->getId(),
					'to' => $to,
					'cc' => $cc,
					'bcc'=>$bcc,
					'subject'=>$subject,
					'text'=>$text,
					'error'=>$error
			);
			$connection->insert($utils->gtn('sqlrpt_cron_log'),$dataInsert);
			$connection->commit();
		}catch (Exception  $err){
				Mage::log(get_class($this).'.'.__FUNCTION__.' unable insert report email '.$err->getMessage(), Zend_Log::INFO,'sqlreport.log');
		}
	}
	
	public function getLogReportEmail($id){
		try{
			$utils= Mage::helper('sqlreport/Utils');
			$coreResource = Mage::getSingleton('core/resource');
			$connection = $coreResource->getConnection('core_read');
			
			$select = $connection->select()
							->from(array('a' => $utils->gtn('sqlrpt_cron_log')),array('a.*'))
							->where('a.report_id = ?',$id)
							->order(array('a.created_at DESC'));
			$readresult=$connection->fetchAll($select);
			return $readresult;
			
		}catch (Exception  $err){
				Mage::log(get_class($this).'.'.__FUNCTION__.' unable get report log '.$err->getMessage(), Zend_Log::INFO,'sqlreport.log');
		}
	}
	
	public function removeLogReportEmail($report_id,$cron_id_list){
		$idList = explode("|", $cron_id_list);
		$where = "";
		foreach($idList as $idRem){
			if(""!=$idRem){
				$where.=",".$idRem;
			}
		}
		$where = substr($where, 1);
		$where = "log_id IN (".$where.") AND report_id='".$report_id."'";
		try{
			$utils= Mage::helper('sqlreport/Utils');
			$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
			$connection->beginTransaction();
			
			$whereDel = array($connection->quoteInto($where));
			$connection->delete($utils->gtn('sqlrpt_cron_log'), $whereDel);
			
			$connection->commit();
			return true;
		}catch (Exception  $err){
    		return $err->getMessage();
    	}
	}
}