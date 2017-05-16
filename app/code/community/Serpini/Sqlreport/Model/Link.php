<?php
class Serpini_Sqlreport_Model_Link extends Mage_Core_Model_Abstract
{
	protected $id;
	protected $description;
	protected $url;
	protected $type;
	
	// Para cuando sea un report-link
	protected $reportLinkId;
	protected $column;
	protected $variables = array();
	
	protected function _construct(){
		$this->_init('sqlreport/link');
	}
	
	public function loadMe($id){
		$utils = Mage::helper('sqlreport/Utils');
		$this->id=$id;
		$coreResource = Mage::getSingleton('core/resource');
    	$connection = $coreResource->getConnection('core_read');
		$select = $connection->select()
	    			->from(array('a' => $utils->gtn('sqlrpt_link')), 
	    				   array('a.description','a.url','a.type'))
	    			->where('entity_id = ?',$this->getId());
	    $readresult=$connection->fetchAll($select);
		foreach ($readresult as $fila){
    		$this->setDescription($fila['description']);
    		$this->setUrl($fila['url']);
    		$this->setType($fila['type']);
	    }
	}
	
	public function loadMe2($description,$url,$type){
		$this->description=$description;
		$this->url=$url;
		$this->type=$type;
	}
	
	public function getId(){
		return $this->id;
	}
	
	public function setId($id){
		$this->id=$id;
	}
	
	public function getDescription(){
		return $this->description;
	}
	public function setDescription($description){
		$this->description=$description;
	}
	
	public function getUrl(){
		return $this->url;
	}
	public function setUrl($url){
		$this->url=$url;
	}
	
	public function getType(){
		return $this->type;
	}
	public function setType($type){
		$this->type=$type;
	}
	
	public function getReportLinkId(){
		return $this->reportLinkId;
	}
	public function setReportLinkId($reportLinkId){
		$this->reportLinkId=$reportLinkId;
	}
	
	public function getColumn(){
		return $this->column;
	}
	public function setColumn($column){
		$this->column=$column;
	}
	
	public function loadVariable($id_report_link,$column){
		$utils = Mage::helper('sqlreport/Utils');
		$this->setReportLinkId($id_report_link);
		$this->setColumn($column);
		$coreResource = Mage::getSingleton('core/resource');
    	$connection = $coreResource->getConnection('core_read');
		$select = $connection->select()
	    			->from(array('a' => $utils->gtn('sqlrpt_report_link_value')), 
	    				   array('a.variable','a.column_num'))
	    			->where('report_link_id = ?',$this->getReportLinkId());
	    $readresult=$connection->fetchAll($select);
		foreach ($readresult as $fila){
			$variable = array($fila['variable'],$fila['column_num']);
			array_push($this->variables,$variable);
	    }
	}
	
	public function replaceVariables($texto,$result,$columnsNames){
		$textoFinal = $texto;
		foreach ($this->variables as $variable){
			$columnName = $columnsNames[$variable[1]-1];
			$valor = $result[$columnName];
			if(""!=$valor){
				$textoFinal = str_replace("<".$variable[0].">",$valor,$textoFinal);
			}else{
				$textoFinal="";
			}
			
		}
		return $textoFinal;
	}
	
	public function saveLink(){
		try{
			$utils = Mage::helper('sqlreport/Utils');
			$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
			$dataUpdate = array(
					'description' => $this->getDescription(),
					'url'  => $this->getUrl(),
					'type'  => $this->getType()
			);
		
			$where[] = "entity_id = '".$this->getId()."'";
			$connection->beginTransaction();
			$connection->update($utils->gtn('sqlrpt_link'), $dataUpdate, $where);

			$connection->commit();
			return true;
		}catch (Exception  $err){
			return $err->getMessage();
		}
	}
	
	public function addMeAsNew(){
		try{
			$utils = Mage::helper('sqlreport/Utils');
			$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
			$connection->beginTransaction();
			$fields  = array(
					'description' => $this->getDescription(),
					'url' => $this->getUrl(),
					'type' => $this->getType()
			);
			
			$connection->insert($utils->gtn('sqlrpt_link'), $fields);
			
			$id = $utils->getLastId($connection);
			$this->setId($id);
			
			$connection->commit();

			return true;
		}catch (Exception  $err){
			return $err->getMessage() ;
		}
		
	}
	
	public function delete(){
		try{
			$utils = Mage::helper('sqlreport/Utils');
			$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		
			// Comprobamos que no haya ningún informe asociado a dicho link
			$select = $connection->select()
							->from($utils->gtn('sqlrpt_report_link'), array('numero' => 'COUNT(1)'))
							->where('link_id = ?',$this->getId());
			$fechList=$connection->fetchRow($select);
			$esta=$fechList['numero'];
			if($esta>0){
				return Mage::helper('catalog')->__('The link has associated %s reports',$esta);
			}else{
				$where = array($connection->quoteInto('entity_id=?', $this->getId()));
				$connection->beginTransaction();
				$connection->delete($utils->gtn('sqlrpt_link'), $where);
				$connection->commit();
				return true;
			}
		
		}catch (Exception  $err){
			return $err->getMessage();
		}
	}
	
	public function toJsonResponse(){
    	return json_encode($this->toArrayex());
	}
	
	public function toArrayex(){

		$salida = array("type_message" => "success-msg",
				"object_type" => "link",
				"type" => $this->getType(),
				"id" => $this->getId(),
    			"description" => $this->getDescription(),
    			"url" => $this->getUrl(),
    			"variables" => $this->getVariables(),
				"column"=> $this->getColumn());
    	return $salida;
	}
	
	public function getVariables(){
		if(count($this->variables)==0){
			$listBBDD = $this->getVariableFromDB($this->getReportLinkId());
			if(count($listBBDD)==0){
				$this->getVariableFromURL();
			}
		}
		
		return $this->variables;
	
	}
	
	private function getVariableFromURL(){
		$variables = array();
		$list = explode("<",$this->getUrl());
		foreach($list as $p){
			if(strpos($p,">")>0){
				$variable = explode(">",$p);
				$variables[$variable[0]] = "";
				$this->addVariable($variable[0],"");
			}
		}
		
		$list = explode("<",$this->getDescription());
		foreach($list as $p){
			if(strpos($p,">")>0){
				$variable = explode(">",$p);
				$variables[$variable[0]] = "";
				$this->addVariable($variable[0],"");
			}
		}
		return $variables;
	}
	
	private function getVariableFromDB($reportId){
		$variables = array();
		try{
			$utils = Mage::helper('sqlreport/Utils');
			$coreResource = Mage::getSingleton('core/resource');
    		$connection = $coreResource->getConnection('core_read');
			$select = $connection->select()
	    			->from(array('a' => $utils->gtn('sqlrpt_report_link')), 
	    				   array())
	    			->join(array('b' => $utils->gtn('sqlrpt_report_link_value')),
	    					'a.id = b.report_link_id',
	    				   array('variable' => 'b.variable','column_num' => 'b.column_num'))
	    			->where('a.link_id = ?',$this->getId())
	    			->where('a.report_id = ?',$this->getReportLinkId());
			$readresult=$connection->fetchAll($select);
			foreach ($readresult as $fila){
	    		$variables[$fila['variable']] = $fila['column_num'];
	    		$this->addVariable($fila['variable'],$fila['column_num']);
		    }
		}catch (Exception  $err){
    		
    	}
		return $variables;
	}
	
	public function addVariable($variable,$column_num){
		$this->variables[$variable]=$column_num;
	}
	
	public function getTarget(){
		$systemAdmin = Mage::getModel('sqlreport/setup');
		if(""==$systemAdmin->getValue("link_target")){
			return "_self";
		}else{
			return "_".$systemAdmin->getValue("link_target");
		}
		
	}
	
}