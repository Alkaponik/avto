<?php
class Serpini_Sqlreport_Helper_ReportManager extends Mage_Core_Helper_Abstract {
	
	public $MESSAGE_OK="success";
	public $MESSAGE_ERROR="error";
	public $MESSAGE_WARNING="warning";
	public $MESSAGE_NOTICE="notice";
	
	/*
	 * Devuelve un array con la lista de informes
	 * @action = Acctión read|edit
	 */
	
	public function getReportsList($action){
		$reportsList = array();
		$coreResource = Mage::getSingleton('core/resource');
		$connection = $coreResource->getConnection('core_read');
		$role = Mage::getModel('sqlreport/permissionrole');
		
		
		$select = $connection->select()
							->from(array('a' => $this->gtn('sqlrpt_report')),
								   array('report_id' => 'entity_id',
								   		 'report_title'=>'title'))
							->join(array('d'=>$this->gtn('sqlrpt_group')),
										 'a.group_id = d.entity_id',
										 array('group_description'=>'description'))
							->joinLeft(array('b'=>$this->gtn('sqlrpt_report_int')),
									   'a.entity_id = b.report_id',
									   array())
						    ->joinLeft(array('c'=>$this->gtn('sqlrpt_report_type')),
						   		'b.value_id = c.type_id AND c.type_code =\'order\'',
						   		array())
						   	->joinLeft(array('e'=>$this->gtn('sqlrpt_report_role')),
						   		'a.entity_id = e.report_id AND e.role =\''.$role->getRoleIdUser().'\'',
						   		array('read'=>'read','edit'))
						   	->where('1=(CASE WHEN e.'.$action.' is null then (SELECT (CASE WHEN COUNT(1)>0 THEN 0 ELSE 1 END) AS access
									                                          FROM '.$this->gtn('sqlrpt_report_role').'
									                                          WHERE role =\''.$role->getRoleIdUser().'\')
							              WHEN e.'.$action.' = 0 THEN 0
							              ELSE 1 END)')
						   	->order(array('d.orden','IFNULL(b.value,9999)'));
		try{
			$readresult=$connection->fetchAll($select);
			foreach ($readresult as $fila){
				$report_id = $fila['report_id'];
				$report_description = $fila['report_title'];
				$group_description = $fila['group_description'];
				$reportingroupList = (array_key_exists($group_description, $reportsList)?$reportsList[$group_description]:array());
				$reportingroupList[$report_id]=$report_description;
				$reportsList[$group_description]=$reportingroupList;
			}
		}catch (Exception  $err){
			echo $err->getMessage();
		}
		return $reportsList;
		
	}
	
	public function getGroupsList(){
		$groupsList = array();
		$coreResource = Mage::getSingleton('core/resource');
		$connection = $coreResource->getConnection('core_read');
		$select = $connection->select()
							->from($this->gtn('sqlrpt_group'), array('description','entity_id'))
							->order('orden');
		$readresult=$connection->fetchAll($select);
		foreach ($readresult as $fila){
			$code = $fila['entity_id'];
			$description = $fila['description'];
			$groupsList[$code]=$description;
		}
		return $groupsList;
	}
	
    public function deleteReport($report_id){
    	$report = Mage::getModel('sqlreport/report');
    	$report->loadReport($report_id);
    	return $report->deleteReport();
    }
    
    public function loadReport($report_id){
    	$report = Mage::getModel('sqlreport/report');
    	$report->loadReport($report_id);
    	return $report->toJsonResponse();
    }
    
    public function saveReport($report_id,$title,$sql,$group_id,$combo,$chartType,$chartXValue,$elementChartYValue,$linkTR,$linkTRVariables,$linkTD,$atributes){
    	$report = Mage::getModel('sqlreport/report');
    	$report->loadReport($report_id);

    	$report->setAtribute('cronActiveLast',$report->getAtribute('cronActive'));
    	$report->setAtribute('cronStringLast',$report->getAtribute('cronString'));
    	
    	$report->setTitle($title);
    	$report->setSql($sql);
    	$report->setGroup($group_id);
    	$comboList = explode("|", $combo);
    	$report->setComboList($comboList);
    	
    	$report->setAtribute('chart_type',$chartType);
    	$report->setAtribute('chartXValue',$chartXValue);
    	$chartYValues = explode("|", $elementChartYValue);
    	$report->setChartSeries($chartYValues);
    	
    	$atributesList = explode("|", $atributes);
    	foreach ($atributesList as $atribute ){
    		if($atribute<>""){
    			$atributeValues = explode("=", $atribute);
    			$codigo = substr($atribute, 0,strpos($atribute,"="));
    			$valor = substr($atribute, strpos($atribute,"=")+1);
    			//$report->setAtribute($atributeValues[0],$atributeValues[1]);
    			$report->setAtribute($codigo,$valor);
    		}
    	}
    	
    	
    	$report->resetLinks();
    	if(""!=$linkTR){
	    	$link = Mage::getModel('sqlreport/link');
	    	$link->loadMe($linkTR);
	    	$linkListTR = explode("|", $linkTRVariables);
	    	foreach ($linkListTR as $linkVariableTR ){
	    		if($linkVariableTR<>""){
	    			$data = explode(";", $linkVariableTR);
	    			$link->addVariable($data[0],$data[1]);
	    		}
	    	}
	    	$report->addLink($link);
    	}
    	
    	$linkListTD = explode("|", $linkTD);
    	foreach ($linkListTD as $linkVariableTD ){
    		if($linkVariableTD<>""){
    			$data = explode(";", $linkVariableTD);
    			$link = Mage::getModel('sqlreport/link');
    			$link->loadMe($data[0]);
    			$link->setColumn($data[1]);
    			$link->setReportLinkId($report_id);
    			$report->addLink($link);
    		}
    	}
    	

    	return $report->saveReport();
    }
    
    public function addNewReport($report_id,$title,$sql,$group,$atributes,$combos,$chart_series,$links){
    	$report = Mage::getModel('sqlreport/report');
    	
    	$report->setId($report_id);
    	$report->setTitle($title);
    	$report->setSql($sql);
    	$report->setGroup($group);
    	
    	foreach ($atributes as $key => $value){
			$report->setAtribute($key,$value);
		}
		
		$report->setCombosListBasic($combos);
		
		$report->setChartSeries($chart_series);
		
		foreach ($links as $link){
			$report->addLink($link);
		}
		
		$report->setAtribute('cronActiveLast','false');
		
		$salida = $report->addMeAsNew();
		if(true==$salida){
			return $report;
		}else{
			return $salida;
		}

    }
    
    public function addReport($title,$sql,$group_id,$combo,$chartType,$chartXValue,$elementChartYValue,$linkTR,$linkTRVariables,$linkTD,$atributes){
    	$report = Mage::getModel('sqlreport/report');
    	
    	$report->setTitle($title);
    	$report->setSql($sql);
    	$report->setGroup($group_id);
    	
    	$comboList = explode("|", $combo);
    	$report->setCombosListBasic($comboList);
    	
    	$report->setAtribute('chart_type',$chartType);
    	$report->setAtribute('chartXValue',$chartXValue);
    	
    	$chartSeriesList = explode("|", $elementChartYValue);
    	$report->setChart_series($chartSeriesList);
    	
    	$atributesList = explode("|", $atributes);
    	foreach ($atributesList as $atribute ){
    		if($atribute<>""){
    			$atributeValues = explode("=", $atribute);
    			$report->setAtribute($atributeValues[0],$atributeValues[1]);
    		}
    	}
    	
    	$report->resetLinks();
    	if(""!=$linkTR){
	    	$link = Mage::getModel('sqlreport/link');
	    	$link->loadMe($linkTR);
	    	$linkListTR = explode("|", $linkTRVariables);
	    	foreach ($linkListTR as $linkVariableTR ){
	    		if($linkVariableTR<>""){
	    			$data = explode(";", $linkVariableTR);
	    			$link->addVariable($data[0],$data[1]);
	    		}
	    	}
	    	$report->addLink($link);
    	}
    	
    	$linkListTD = explode("|", $linkTD);
    	foreach ($linkListTD as $linkVariableTD ){
    		if($linkVariableTD<>""){
    			$data = explode(";", $linkVariableTD);
    			$link = Mage::getModel('sqlreport/link');
    			$link->loadMe($data[0]);
    			$link->setColumn($data[1]);
    			$link->setReportLinkId($report_id);
    			$report->addLink($link);
    		}
    	}
    	
    	$report->setAtribute('cronActiveLast','false');
    	
    	$salida = $report->addMeAsNew();
    	if(true==$salida){
    		return $report;
    	}else{
    		return $salida;
    	}
   }

	public function _prepareDownloadResponse(
			$fileName,
			$content,
			$contentType = 'application/octet-stream',
			$contentLength = null)
	{
		$session = Mage::getSingleton('admin/session');
		if ($session->isFirstPageAfterLogin()) {
			$this->_redirect($session->getUser()->getStartupPageUrl());
			return $this;
		}
	
		$isFile = false;
		$file   = null;
		if (is_array($content)) {
			if (!isset($content['type']) || !isset($content['value'])) {
				return $this;
			}
			if ($content['type'] == 'filename') {
				$isFile         = true;
				$file           = $content['value'];
				$contentLength  = filesize($file);
			}
		}
	
		$this->getResponse()
		->setHttpResponseCode(200)
		->setHeader('Pragma', 'public', true)
		->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
		->setHeader('Content-type', $contentType, true)
		->setHeader('Content-Length', is_null($contentLength) ? strlen($content) : $contentLength)
		->setHeader('Content-Disposition', 'attachment; filename="'.$fileName.'"')
		->setHeader('Last-Modified', date('r'));
	
		if (!is_null($content)) {
			if ($isFile) {
				$this->getResponse()->clearBody();
				$this->getResponse()->sendHeaders();
	
				$ioAdapter = new Varien_Io_File();
				$ioAdapter->open(array('path' => $ioAdapter->dirname($file)));
				$ioAdapter->streamOpen($file, 'r');
				while ($buffer = $ioAdapter->streamRead()) {
					print $buffer;
				}
				$ioAdapter->streamClose();
				if (!empty($content['rm'])) {
					$ioAdapter->rm($file);
				}
			} else {
				$this->getResponse()->setBody($content);
			}
		}
		return $this;
	}
	
	/**
	 * Set redirect into response
	 *
	 * @param   string $path
	 * @param   array $arguments
	 */
	protected function _redirect($path, $arguments=array())
	{
		$this->getResponse()->setRedirect(Mage::getUrl($path, $arguments));
		return $this;
	}
	
	/**
	 * Retrieve response object
	 *
	 * @return Mage_Core_Controller_Response_Http
	 */
	public function getResponse()
	{
		return Mage::app();
	}
	
	public function importFromXML($filename){
		$xml = simplexml_load_file($filename);
		
		echo $xml->getName() . "<br/>";
		
		foreach($xml->children() as $child)
		{
			echo $child->getName() . ": " . $child->name . "<br/>";
		}
	}
	
	public function reportExists($report_title,$action){
		$reportLista=$this->getReportsList($action);
		foreach($reportLista as $group => $reportListGroup){
			foreach($reportListGroup as $reportCode => $reportDescription){
				if($reportDescription==$report_title){
					return $reportCode;
				}
			}
		}
		return false;
	}
	
	public function gtn($tableName){
		return Mage::getSingleton('core/resource')->getTableName($tableName);
	}
	
}
?>