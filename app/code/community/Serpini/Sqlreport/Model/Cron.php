<?php
class Serpini_Sqlreport_Model_Cron extends Mage_Core_Model_Abstract{
	
	protected $id;
	protected $report_id;
	protected $atributes = array();
	
	protected $error;
	protected $errorMsg;
	protected $errorSQL;

	protected $utils;
	
	protected function _construct(){	
        $this->_init('sqlreport/cron');
        $this->id = "";
        $this->error=false;
        $this->utils= Mage::helper('sqlreport/Utils');
    }
    
	public function isLoaded(){
    	return $this->id!="";
    }
	
	public function getId(){
    	return $this->id;
    }
    public function setId($id){
    	$this->id = $id;
    }
    
	public function getReportId(){
    	return $this->report_id;
    }
    public function setReportId($report_id){
    	$this->report_id = $report_id;
    }
    
	public function getAtribute($atribute){
    	if(array_key_exists($atribute,$this->atributes )){
    		return $this->atributes[$atribute];
    	}else{
    		return '';
    	}
    }
    
	public function setAtribute($atribute,$value){
    	$this->atributes[$atribute] = $value;
    }
    
    public function loadCron($cron_id){
    	try{
	    	$this->setId($cron_id);
	    	
	    	$coreResource = Mage::getSingleton('core/resource');
    		$connection = $coreResource->getConnection('core_read');
    		
	    	$select = $connection->select()
	    			->from(array('a' => $this->utils->gtn('sqlrpt_cron')), 
	    				   array('a.cron_id','a.report_id'))
	    			->where('cron_id = ?',$this->getId());
	    	$readresult=$connection->fetchAll($select);
	    	foreach ($readresult as $fila){
	    		$this->setReportId($fila['report_id']);
	    	}

	    	$this->loadAtributes();
	
    	}catch (Exception  $err){
    		$this->error=true;
    		$this->errorMsg = Mage::helper('catalog')->__('Error loading cron params')." :".$err->getMessage();
    		$this->errorSQL = $sql;
    	}
    	
    }

    public function addMeAsNew(){
    	$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
    	$connection->beginTransaction();
    	try{
    		
    		// CRON
    		$fields  = array(
    				'report_id' => $this->getReportId()
    		);
    		$connection->insert($this->utils->gtn('sqlrpt_cron'), $fields);
    		
    		$cron_id = $connection->lastInsertId();
    		$this->setId($cron_id);
    		
    		
    		// CRON ATTRIBUTES
    		$this->saveAttributes($connection);
			
    		$connection->commit();
    		return true;
    	}catch (Exception  $err){
    		$connection->rollBack();
    		return $err->getMessage();
    	}
    }
    
	public function saveAttributes($connection){
    	$connectionRead = Mage::getSingleton('core/resource')->getConnection('core_read');

    	foreach ($this->atributes as $name => $value){
    		$dataInsert = array(
    				'cron_id' => $this->getId(),
    				'name' => $name ,
    				'value' => $value);
    		$connection->insert($this->utils->gtn('sqlrpt_cron_text'),$dataInsert);
    		
    	}
    	
    }
    
    private function loadAtributes(){
    	try{
    		$coreResource = Mage::getSingleton('core/resource');
    		$connection = $coreResource->getConnection('core_read');
    		$select = $connection->select()
    				->from(array('a' => $this->utils->gtn('sqlrpt_cron_text')),
    					   array('a.name', 'a.value'))
    				->where('cron_id = ?',$this->getId());
    		$readresult=$connection->fetchAll($select);
    		foreach ($readresult as $fila){
    			$this->atributes[$fila['name']] = $fila['value'];
    		}
    	}catch (Exception  $err){
    		$this->error=true;
	    	$this->errorMsg = "Error loading atributes :".$err->getMessage();
	    	$this->errorSQL = $sql;
    	}
    } 
    
	public function deleteMe(){
		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
    	try{
    		$connection->beginTransaction();
    		$where = array($connection->quoteInto('cron_id=?', $this->getId()));    		
    		
    		$connection->delete($this->utils->gtn('sqlrpt_cron_text'), $where);
    		$connection->delete($this->utils->gtn('sqlrpt_cron'), $where);
    		
    		$connection->commit();

    		return true;
    	
    	}catch (Exception  $err){
    		$connection->rollBack();
    		return $err->getMessage();
    	}
    	
    }
    
	
}