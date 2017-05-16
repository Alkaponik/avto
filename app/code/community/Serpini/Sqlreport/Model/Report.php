<?php
class Serpini_Sqlreport_Model_Report extends Mage_Core_Model_Abstract
{
	protected $columnsName = array();
	protected $result;
	protected $title;
	protected $combosList = array();
	protected $combosListBasic = array();
	protected $resultSum = array();
	protected $columnsType = array();
	protected $filterHeader = array();
	protected $id;
	protected $sql;
	protected $sqlCustom;
	protected $group;
	protected $links = array();
	protected $totalRows;
	protected $actualPage;
	protected $rowPerPage;
	
	protected $combosSeted;
	
	protected $error;
	protected $errorMsg;
	protected $errorSQL;
	
	protected $setupmanager;
	protected $utils;
	
	protected $atributes = array();
	protected $chart_series = array();
	
	protected $logger;
	protected $_xAxisDateFormat = false;
	protected $_minY1 = 0;
	protected $_minY2 = 0;
	protected $_maxY1 = null;
	protected $_maxY2 = null;
	
	protected $atributesDefault = array(
			"pdfDefault" => array("pdfLogoUrl","pdfLogoWidth","pdfLogoHeight",
						"pdfTitleFontName","pdfTitleFontSize","pdfTitleFontBold","pdfTitleFontItalic","pdfTitleFontColor",
						"pdfDescriptionFontName","pdfDescriptionFontSize","pdfDescriptionAlignLeft","pdfDescriptionAlignCenter","pdfDescriptionAlignRight","pdfDescriptionFontBold","pdfDescriptionFontItalic","pdfDescriptionFontColor","pdfDescriptionSpace",
						"pdfFiltersShow	","pdfFiltersFontName","pdfFiltersFontSize","pdfFilterFontBold","pdfFilterFontItalic","pdfFilterFontColor",
						"pdfTableHFontName","pdfTableHFontSize","pdfTableHAlignLeft","pdfTableHAlignCenter","pdfTableHAlignRight","pdfTableHFontBold","pdfTableHFontItalic","pdfTableHBackgroundColor","pdfTableHFontColor","pdfTableHBorderAll","pdfTableHBorderTop","pdfTableHBorderBottom","pdfTableHBorderRight","pdfTableHBorderLeft","pdfTableHBorderInV","pdfTableHBorderInH",
						"pdfTableDFontName","pdfTableDFontsize","pdfTableDAlignLeft","pdfTableDAlignCenter","pdfTableDAlignRight","pdfTableDFontBold","pdfTableDFontItalic","pdfTableDOBackgroundColor","pdfTableDEBackgroundColor","pdfTableDFontColor","pdfTableDBorderAll","pdfTableDBorderTop","pdfTableDBorderBottom","pdfTableDBorderRight","pdfTableDBorderLeft","pdfTableDBorderInV","pdfTableDBorderInH",
						"pdfTableFShow","pdfTableFFontName","pdfTableFFontsize","pdfTableFAlignLeft","pdfTableFAlignCenter","pdfTableFAlignRight","pdfTableFFontBold","pdfTableFFontItalic","pdfTableFBackgroundColor","pdfTableFFontColor","pdfTableFBorderAll","pdfTableFBorderTop","pdfTableFBorderBottom","pdfTableFBorderRight","pdfTableFBorderLeft","pdfTableFBorderInV",
						"pdfFooterFontName","pdfFooterFontSize","pdfFooterAlignLeft","pdfFooterAlignCenter","pdfFooterAlignRight","pdfFooterFontBold","pdfFooterFontItalic","pdfFooterFontColor","pdfFooterString"),
			"xlsDefault" => array("xlsEvenRowAlignCenter","xlsEvenRowAlignLeft","xlsEvenRowAlignRight","xlsEvenRowBackgroundColor","xlsEvenRowBold","xlsEvenRowBorderAll","xlsEvenRowBorderBottom","xlsEvenRowBorderLeft","xlsEvenRowBorderRight","xlsEvenRowBorderTop","xlsEvenRowColor","xlsEvenRowFont","xlsEvenRowItalic","xlsEvenRowSize",
						"xlsHeaderAlignCenter","xlsHeaderAlignLeft","xlsHeaderAlignRight","xlsHeaderBackgroundColor","xlsHeaderBold","xlsHeaderBorderAll","xlsHeaderBorderBottom","xlsHeaderBorderLeft","xlsHeaderBorderRight","xlsHeaderBorderTop","xlsHeaderColor","xlsHeaderFont","xlsHeaderItalic","xlsHeaderSize",
						"xlsOddRowAlignCenter","xlsOddRowAlignLeft","xlsOddRowAlignRight","xlsOddRowBackgroundColor","xlsOddRowBold","xlsOddRowBorderAll","xlsOddRowBorderBottom","xlsOddRowBorderLeft","xlsOddRowBorderRight","xlsOddRowBorderTop","xlsOddRowColor","xlsOddRowFont","xlsOddRowItalic","xlsOddRowSize")
	);
	
	// Si uno de �stos est� seteado como true, los otros se pone a false
	protected $atributesGroups = array(
			array("pdfDescriptionAlignLeft","pdfDescriptionAlignCenter","pdfDescriptionAlignRight"),
			array("pdfTableHAlignLeft","pdfTableHAlignCenter","pdfTableHAlignRight"),
			array("pdfTableDAlignLeft","pdfTableDAlignCenter","pdfTableDAlignRight"),
			array("pdfTableFAlignLeft","pdfTableFAlignCenter","pdfTableFAlignRight"),
			array("pdfFooterAlignLeft","pdfFooterAlignCenter","pdfFooterAlignRight"),
			array("xlsHeaderAlignLeft","xlsHeaderAlignCenter","xlsHeaderAlignRight"),
			array("xlsEvenRowAlignLeft","xlsEvenRowAlignCenter","xlsEvenRowAlignRight"),
			array("xlsOddRowAlignLeft","xlsOddRowAlignCenter","xlsOddRowAlignRight")
	);

    protected function _construct(){	
        $this->_init('sqlreport/report');
        $this->id = "";
        $this->error=false;
        $this->actualPage=1;
        $this->hasChangedSQL=false;
        $this->sqlCustom="";
        $this->setupmanager= Mage::getModel('sqlreport/setup');
        $this->rowPerPage=$this->setupmanager->getValue("rowPerPage");
        $this->group=Mage::getModel('sqlreport/group');
        $this->utils= Mage::helper('sqlreport/Utils');
        $this->logger=Mage::getModel('sqlreport/logger');
		$this->_date = new DateTime();
		$this->_date->setTimezone(new DateTimeZone(Mage::app()->getStore()->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));
    }
    
    protected function _construct2($id,$title,$sql,$group_code){
    	$this->_init('sqlreport/report');
    	$this->id = $id;
    	$this->error=false;
    	$this->setupmanager= Mage::getModel('sqlreport/setup');
    	$this->title=$title;
    	$this->sql=$sql;
    	$this->sqlCustom="";
    	$this->group->loadMe($group_code);
    	$this->utils= Mage::helper('sqlreport/Utils');
    	
    }
    
    public function isLoaded(){
    	return $this->id!="";
    }
    
    
    public function loadReport($id){
    	try{
	    	$this->id = $id;
	    	$this->combosSeted=false;
	    	$coreResource = Mage::getSingleton('core/resource');
    		$connection = $coreResource->getConnection('core_read');
	    	$select = $connection->select()
	    			->from(array('a' => $this->utils->gtn('sqlrpt_report')), 
	    				   array('a.title','a.report_sql','a.group_id'))
	    			->join(array('b' => $this->utils->gtn('sqlrpt_group')),
	    					'a.group_id = b.entity_id',
	    				   array('group_des' => 'b.description','group_orden' => 'b.orden'))
	    			->where('a.entity_id = ?',$this->getId());
	    	$readresult=$connection->fetchAll($select);
	    	foreach ($readresult as $fila){
	    		$this->title = $fila['title'];
	    		$this->sql = $fila['report_sql'];
	    		$this->group->loadMe($fila['group_id']);
	    	}
	    	$this->loadComboList();
	    	$this->loadAtributes();
	    	$this->loadAtributesDefault();
	    	if($this->hasChart()){
	    		$this->loadChartSeries();
	    	}
	    	$this->loadLinkList();
	    	
	    	
    	}catch (Exception  $err){
    		$this->error=true;
    		$this->errorMsg = Mage::helper('catalog')->__('Error loading report params').":".$err->getMessage();
    		$this->errorSQL = $sql;
    	}
    }
    
    
    public function loadDataReport(){
    	if(!$this->hasCombos()){
    		$this->executeQuery();
    	}
    }
    
    public function setComboValues($comboValues){
    	if($this->hasCombos()){
    		$this->combosSeted = true;
    		$combosValuesDecode = base64_decode($comboValues);
    		$comboListValues = explode("&",$combosValuesDecode);
    		foreach ($comboListValues as $comboValue){
    			$parameterList = explode("=",$comboValue);
    			$parameter = $parameterList[0];
    			$valueList = explode("=",$comboValue);
    			$value = urldecode($valueList[1]);
    			foreach($this->getComboList() as $combo){
    				if(($combo->getParameter() == $parameter) && (""!=$parameter)){
    					$combo->setValue($value);
    				}
    			}
    		}
    		$this->executeQuery();
    	}
    }
    
    public function setFilterHeader($comboValues){
    	$combosValuesDecode = base64_decode($comboValues);
    	$comboListValues = explode("&",$combosValuesDecode);
    	foreach ($comboListValues as $comboValue){
    		
    		array_push($this->filterHeader,$comboValue);
    	}
    }
    
    public function getValueFilterHeader($parameterValue){
    	foreach ($this->filterHeader as $comboValue){
	    		$comboValue = urldecode($comboValue);
	    		$parameterList = explode("=",$comboValue);
	    		$parameter = $parameterList[0];
	    		$valueList = explode("=",$comboValue);
	    		$value = urldecode($valueList[1]);
	    		if($parameterValue==$parameter) return $value;
    	}
    	
    	return "";
    }
    
    public function setComboValuesDefault(){
    	if($this->hasCombos()){
    		foreach($this->getComboList() as $combo){
    			$combo->setValue($combo->getValueDefault());
    		}
    		$this->executeQuery();
    	}
    	
    }
    
    protected function loadComboList(){    
    	try{
    		$coreResource = Mage::getSingleton('core/resource');
    		$connection = $coreResource->getConnection('core_read');

    		$select = $connection->select()
    				->from($this->utils->gtn('sqlrpt_report_combo'), array('combo_id' => 'combo_id','value'=>'value'))
    				->where('report_id = ?',$this->getId())
    				->order(array('order_n'));
    		$readresult=$connection->fetchAll($select);
    		
	    	foreach ($readresult as $fila){
	    		$combo = Mage::getModel('sqlreport/combo');
	    		$combo->loadCombo($fila['combo_id']);
	    		if($combo->isError()){
	    			$this->error=true;
	    			$this->errorMsg= Mage::helper('catalog')->__('Error in filter')." [".$combo->getId()."]: ".$combo->getErrorMsg();
	    			$this->errorSQL;
	    		}
	    		$combo->setValueDefault($fila['value']);
	    		$this->addCombo($combo);
	    	}
    	}catch (Exception  $err){
    		$this->error=true;
	    	$this->errorMsg = Mage::helper('catalog')->__('Error loading filter list')." :".$err->getMessage();
	    	$this->errorSQL = $sql;
    	}
    }
    
    public function addCombo($combo){
    	array_push($this->combosList,$combo);
    	array_push($this->combosListBasic,$combo->getId());
    }
    
    
    private function loadAtributes(){
    	try{
    		$coreResource = Mage::getSingleton('core/resource');
    		$connection = $coreResource->getConnection('core_read');
    		$select = $connection->select()
    				->from(array('a' => $this->utils->gtn('sqlrpt_report_type')),
    					   array('a.type_code', 'a.type'))
    				->joinLeft(array('b' => $this->utils->gtn('sqlrpt_report_int')),
    						'a.type_id = b.type_id'.
    					    ' AND b.report_id = "'.$this->getId().'"',
    						array('value_int' => 'b.value'))
					->joinLeft(array('c' => $this->utils->gtn('sqlrpt_report_text')),
    						'a.type_id = c.type_id'.
    					    ' AND c.report_id = "'.$this->getId().'"',
    						array('value_text' => 'c.value'))
					->joinLeft(array('d' => $this->utils->gtn('sqlrpt_report_varchar')),
    						'a.type_id = d.type_id'.
    					    ' AND d.report_id = "'.$this->getId().'"',
    						array('value_varchar' => 'd.value'))
					->joinLeft(array('e' => $this->utils->gtn('sqlrpt_setup')),
    						'a.type_code = e.name',
    						array('value_def' => 'e.value'));
					
    		$readresult=$connection->fetchAll($select);
    		
    		foreach ($readresult as $fila){
    			$valueAtribute = "";
    			switch ($fila['type']){
    				case 'int': //$valueAtribute = ($fila['value_int']!=""?$fila['value_int']:$fila['value_def']);
    							//if(""==$fila['value_int']) $valueAtribute = $fila['value_def'];
    					$valueAtribute=$fila['value_int'];
    					break;
    				case 'text': //$valueAtribute = (""!=$fila['value_text']?$fila['value_text']:$fila['value_def']);
    							 //if(""==$fila['value_text']) $valueAtribute = $fila['value_def'];
    					$valueAtribute=$fila['value_text'];
    					break;
    				case 'varchar': //$valueAtribute = ($fila['value_varchar']!=""?$fila['value_varchar']:$fila['value_def']);
    								//if(""==$fila['value_varchar']) $valueAtribute = $fila['value_def'];
    								$valueAtribute=$fila['value_varchar'];
    					break;
    			}
    			if(""!=$valueAtribute){
    				$this->atributes[$fila['type_code']]=$valueAtribute;
    				if("true"==$valueAtribute) $this->addPrivateAtributesGrouped($fila['type_code']);
    			}
    			
    		}
    		
    	}catch (Exception  $err){
    		$this->error=true;
	    	$this->errorMsg = Mage::helper('catalog')->__('Error loading atributes')." :".$err->getMessage();
	    	$this->errorSQL = $sql;
    	}
    } 
    
    private function loadChartSeries(){
    	try{
    		$coreResource = Mage::getSingleton('core/resource');
    		$connection = $coreResource->getConnection('core_read');
    		$select = $connection->select()
					    		->from($this->utils->gtn('sqlrpt_chart_series'), array('serie_num','column_num'))
					    		->where('report_id = ?',$this->getId())
					    		->order(array('serie_num'));
    		$readresult=$connection->fetchAll($select);
			
    		foreach ($readresult as $fila){
    			$this->chart_series[$fila['serie_num']] = $fila['column_num'];
    		}
    	}catch (Exception  $err){
    		$this->error=true;
    		$this->errorMsg = Mage::helper('catalog')->__('Error loading filter list')." :".$err->getMessage();
    		$this->errorSQL = $sql;
    	}
    }
    
    public function setComboList($comboList){
    	$this->combosList = array();
    	$lista="";
    	foreach ($comboList as $comboId ){
    		if($comboId<>""){
    			$comboList = explode("=", $comboId);
    			$comboId2=$comboList[0];
    			$lista .= $comboId2.",";
    			$value = (count($comboList)>1?$comboList[1]:"");
    			$combo = Mage::getModel('sqlreport/combo');
    			$combo->loadCombo($comboId2);
    			if($combo->isError()){
    				$this->error=true;
    				$this->errorMsg= Mage::helper('catalog')->__('Error in filter')." [".$combo->getId()."]: ".$combo->getErrorMsg();
    				$this->errorSQL;
    			}
    			$combo->setValueDefault($value);
    			
    			array_push($this->combosList,$combo);
    		}
    	}
    }
    
    protected function executeQuery(){
    	//$sql = $this->setParams($this->sql);
    	$sql = $this->getSqlYesParams(false);
    	try{
    		$connection = "";
    		if($this->setupmanager->getValue('db_host')==""){
    			$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
    		}else{
    			$db= Zend_Db::factory('Pdo_Mysql',array(
    					'host' => $this->setupmanager->getValue('db_host'),
    					'username' => $this->setupmanager->getValue('db_username'),
    					'password' => $this->setupmanager->getValue('db_password'),
    					'dbname' => $this->setupmanager->getValue('db_name')
    			));
    			$db->setFetchMode(Zend_Db::FETCH_NUM);
    			$connection = $db->getConnection();
    		}
	    	
	    	// Total rows
	    	$sqlTotalRows = "SELECT COUNT(1) as cuenta FROM (".$sql.") a";
	    	$readresult=$connection->query($sqlTotalRows);
	    	$resultTotal = $readresult->fetchAll();
	    	foreach ($resultTotal as $fila){
	    		$this->setTotalRow($fila['cuenta']);
	    	}
	    	
	    	// Filter Header
	    	$where = "";
	    	foreach ($this->filterHeader as $comboValue){
	    		$comboValue = urldecode($comboValue);
	    		$parameterList = explode("=",$comboValue);
	    		$parameter = $parameterList[0];
	    		$valueList = explode("=",$comboValue);
	    		$value = urldecode($valueList[1]);
	    		$tipo = substr($parameter,0,1);
	    		$parameter = substr($parameter,1);
	    		
	    		switch ($tipo){
					// String
	    			case 1: $where.=" AND a.`".$parameter."` LIKE ('%".$value."%')";
	    				break;
	    			// Number
	    			case 2: $subtipo = substr($parameter,0,1);
	    					$parameter = substr($parameter,1);
	    					$igualdad = ($subtipo=="F"?" >= ":" <= ");
	    					$where .= " AND a.`".$parameter."`".$igualdad.$value;
	    				break;
	    			// Date
	    			case 4:	$subtipo = substr($parameter,0,1);
	    					$parameter = substr($parameter,1);
	    					$parameterArr = explode("$$",$parameter);
	    					$formatPHP = $parameterArr[0];
	    					$parameter = $parameterArr[1];
	    					$igualdad = ($subtipo=="F"?" >= ":" <= ");
	    					$format = $this->utils->dateFormat2sql($formatPHP);
	    					$where .= " AND DATE_FORMAT(STR_TO_DATE( a.`".$parameter."`,'".$format."'), '%Y-%m-%d') ".$igualdad."STR_TO_DATE('".$value."','".$this->setupmanager->getValue('date_mask')."')";
	    				break;
	    			default:$where.=" AND a.`".$parameter."` = '".$value."'";
	    		}
	    	}
	    	$where =substr($where,4);
	    	if(""!=$where) $where = " WHERE ".$where;
	    	// Limit
	    	if($this->getRowPerPage()!="all" || ""!=$where){
	    		$limit = "";
	    		if($this->getRowPerPage()!="all"){
	    			$inferior = ($this->getActualPage()==1?0:($this->getActualPage()-1)*$this->getRowPerPage());
	    			if($inferior>$this->getTotalRows()){
	    				$this->setActualPage($this->getNumPages());
	    				$inferior = ($this->getActualPage()==1?0:($this->getActualPage()-1)*$this->getRowPerPage());
	    			}
	    			$limit = "LIMIT ".$this->getRowPerPage()." OFFSET ".$inferior;
	    		}
	    		
	    		$sql = "SELECT * FROM (".$sql.") a ".$where." ".$limit;
	    	}
	    	$readresult=$connection->query($sql);
	    	$readresult->setFetchMode(Zend_Db::FETCH_ASSOC);
	    	$this->result = $readresult->fetchAll();
	    	$this->resultSum = array();
	    	$this->columnsType = array();
	    	foreach ($this->result as $fila){
	    		foreach ($fila as $key => $value){
	    			array_push($this->columnsName,$key);
	    			array_push($this->resultSum,0);
	    			array_push($this->columnsType,"");
	    		}
	    		break 1;
	    	}	


	    }catch (Exception  $err){
		Mage::log($sql);
	    	$this->error=true;
	    	$this->errorMsg = Mage::helper('catalog')->__('Error in sql')." :".$err->getMessage();
	    	$this->errorSQL = $sql;
	   	}

    }
    
    public function getColumnsName(){
    	return $this->columnsName;
    }
    
    public function getHtmlProperty($column){
    	return 'width="500"';
    }
    
    public function getWidthTable(){
    	return 100*sizeof($this->getColumnsName());
    }
    
    public function getCssTable(){
    	//return "width: ".$this->getWidthTable()."px";
    	return "";
    }
    
    public function getHeaderHtmlProperty($column){
    	return 'class=" no-link"';
    }
    
    public function getHeaderHtml($column){
    	return $column;
    }
    
    public function getSize(){
    	return sizeof($this->result);
    }
    
    public function getResults(){
    	return $this->result;
    }
    
    public function getCssProperty($value){
    	if(is_numeric($value)){
    		return "a-right";
    	}else{
    		return "";
    	}
    }
    
    public function getTitle(){
    	return $this->title;
    }
    
    public function setTitle($title){
    	$this->title = $title;
    }
    
    
    public function getComboList(){
    	return $this->combosList;
    }
    
    public function getId(){
    	return $this->id;
    }
    
    public function setId($id){
    	$this->id = $id;
    }
    
    public function hasCombos(){
    	if(sizeof($this->combosList)>0){
    		// No contamos con los evaluated
    		foreach ($this->combosList as $combo){
    			
    			if($combo->getType()!="evaluated"){
    				return true;
    			}
    		}
    		return false;
    	}else{
    		return false;
    	}
    }
    
    // Elimina un combo por su par�metro
    public function removeCombo($parameter){
    	foreach ($this->combosList as $key=>$combo){
    		if($combo->getParameter()==$parameter){
    			unset($this->combosList[$key]);
    		}
    	}
    }
    
    public function hasRows(){
    	return sizeof($this->result)>0;
    }
    
    public function isComboSeted(){
    	return $this->combosSeted;
    }
    
    public function isError(){
    	return $this->error;
    }
    
    public function getErrorMsg(){
    	return $this->errorMsg;
    }
    
    public function getResultSum(){
    	return $this->resultSum;
    }
    
    
    public function addResult2Sum($column,$value){
    	if(is_numeric($this->resultSum[$column])){
    		if(is_numeric($value)){
    			$this->resultSum[$column] =$this->resultSum[$column] + $value;
    		}else if($value!=""){
    			$this->resultSum[$column] = "s";
    		}
    	}
    	$this->addResult2Type($column,$value);
    	
    }
    
    public function resetResult2Sum(){
    	$columnas = count($this->resultSum);
    	for($i=0;$i<$columnas;$i++){
    		$this->resultSum[$i]=0;
    	}
    }
    
    /* Tipos
    *  		0: Null
    *  		1: String
    *  		2: Number
    *  		3: Boolean
    */
    private function addResult2Type($column,$value){
    	if(""==$this->columnsType[$column]){
    		//Se reinicia con el primer tipo
    		$this->columnsType[$column] = $this->utils->getTypeData($value);
    	}else{
    		// Si el tipo anterior es diferente al actual, se pone ya como String
    		if(1!=$this->columnsType[$column] && $this->columnsType[$column]!=$this->utils->getTypeData($value)){
    			$this->columnsType[$column]=1;
    		}
    	}
    }
    
    public function getTypeColumn($column){
    	return $this->columnsType[$column];
    }
    
    public function haveResultSum(){
    	foreach ($this->resultSum as $value){
    		if ($value!="s") {
    			return true;
    		}
    	}
    	return false;
    }
    
    public function setParams($sql){
    	$salida = $sql;
    	$salida = str_replace($this->setupmanager->getValue('prefix_table'),Mage::getConfig()->getTablePrefix() ,$salida);
    	foreach($this->getComboList() as $combo){
    		if($combo->getType()=="date"){
    			
    			$valor = $combo->getValueSet();
    			if(is_array($valor)){
    				$valor=reset($valor);
    			}
    			
    			$salida = str_replace ($this->setupmanager->getValue('prefix_parameter').$combo->getParameter(),"STR_TO_DATE('".$valor."','".$this->setupmanager->getValue('date_mask')."')",$salida);
    		}else if($combo->getType()=="evaluated"){
    			$valor = "";
    			$sentence = $combo->getAtribute("sql");
    			try{
			    	eval("\$valor = ".$sentence.";");
			    	$salida = str_replace ($this->setupmanager->getValue('prefix_parameter').$combo->getParameter(),$valor,$salida);
			    }catch (Exception  $err){
			    	echo "error";
			    }
    		}else if($combo->getType()=="text"){
    			
    			$valor = $combo->getValueSet();
    			$salida = str_replace ($this->setupmanager->getValue('prefix_parameter').$combo->getParameter(),$valor,$salida);
    		}else {
    			$valor="";
    			if("checkbox-multiple"==$combo->getAtribute("selectType")){
    				$valor ="(";
    				foreach($combo->getValueSet() as $valueSet){
    					$valor.="'".$valueSet."',";
    				}
    				$valor = substr($valor, 0, strlen($valor)-1);
    				$valor .=")";
    			}else{
    				$valor = $combo->getValueSet();
					if(is_array($valor)){
						$valor=reset($valor);
					}
    			}
    			$salida = str_replace ($this->setupmanager->getValue('prefix_parameter').$combo->getParameter(),$valor,$salida);
    		}
    	}
    	
    	return $salida;
    }
    
    public function setParamsTitle($sql){
    	$salida = $sql;
    	//$salida = str_replace($this->setupmanager->getValue('prefix_table'),Mage::getConfig()->getTablePrefix() ,$salida);
    	foreach($this->getComboList() as $combo){
    		if($combo->getType()=="date"){
    			 

    			$valor = $combo->getValueSet();
    			$valor=$valor[0];
    			 
    			$salida = str_replace ($this->setupmanager->getValue('prefix_parameter').$combo->getParameter(),$valor,$salida);
    		}else if($combo->getType()=="evaluated"){
    			$valor = "";
    			$sentence = $combo->getAtribute("sql");
    			try{
    				eval("\$valor = ".$sentence.";");
    				$salida = str_replace ($this->setupmanager->getValue('prefix_parameter').$combo->getParameter(),$valor,$salida);
    			}catch (Exception  $err){
    				echo "error";
    			}
    		}else if($combo->getType()=="text"){
    			 
    			$valor = $combo->getValueSet();
    			$salida = str_replace ($this->setupmanager->getValue('prefix_parameter').$combo->getParameter(),$valor,$salida);
    		}else {
    			$valor="";
    			if("checkbox-multiple"==$combo->getAtribute("selectType")){
    				$valor ="(";
    				foreach($combo->getValueSet() as $valueSet){
    					$valor.="'".$valueSet."',";
    				}
    				$valor = substr($valor, 0, strlen($valor)-1);
    				$valor .=")";
    			}else{
    				$valor = $combo->getValueSet();
    				if(count($valor)>0){
    					$valor=$valor[0];
    				}
    				$valor=$combo->getTitleOfValue($valor);
    			}
    			$salida = str_replace ($this->setupmanager->getValue('prefix_parameter').$combo->getParameter(),$valor,$salida);
    		}
    	}
    	 
    	return $salida;
    }
    
    public function getNumColumns(){
    	return sizeof($this->columnsName);
    }
    
    public function getSqlCustom(){
    	return ($this->sqlCustom=="")?$this->sql:$this->sqlCustom;
    }
    
    public function getSqlNoParams($original=false){
    	return ($original)?$this->sql:$this->getSqlCustom();
    }
    
    public function getSqlYesParams($original=false){
    	//return $this->setParams($this->sql);
    	return ($original)?$this->setParams($this->sql):$this->setParams($this->getSqlCustom());
    }
    
    public function setSql($sql){
    	$this->sql=$sql;
    }
    
    public function setSqlCustom($sql){
    	$this->sqlCustom = $sql;
    }
    
    public function getGroup(){
    	return $this->group;
    }
    
    public function setGroup($groupId){
    	$this->group->loadMe($groupId);
    }
    
    public function getAtributeOLD($atribute){
    	if(array_key_exists($atribute,$this->atributes )){
    		return $this->atributes[$atribute];
    	}else{
    		return '';
    	}
    }
   
    // Eliminar si funciona 
   /*  public function getXlsAtribute($atribute){
    	$byDefault = $this->getAtribute('xlsDefault');
    	$atributeDefault = "xlsDef".substr($atribute,3,strlen($atribute));
    	if($byDefault=="true"){
      		
    		return $this->setupmanager->getValue($atributeDefault);
    	}else{
    		$value = $this->getAtribute($atribute);
    		if(""!=$value){
	    		return $value;
    		}else{
	    		return $this->setupmanager->getValue($atributeDefault);
    		}
    	}
    } */
    public function getXlsAtribute($atribute){
    	return $this->getAtribute($atribute);
    	
    }
    
    public function getAtribute($atribute){
    	if(array_key_exists($atribute,$this->atributes )){
    		return $this->atributes[$atribute];
    	}else{
    		return $this->setupmanager->getValue($atribute);
    	}
    }
    
    public function getAtributeDefault($atribute){
    	return $this->setupmanager->getValue($atribute);
    }
    
    public function getCombosListBasic(){
    	return $this->combosListBasic;
    }
    
    public function setCombosListBasic($list){
    	$this->combosListBasic = $list;
    }
    
    public function setAtribute($atribute,$value){
    	$this->atributes[$atribute] = $value;
    }
    
    public function setAtributeDefault($atribute,$value){
    	if(!array_key_exists($atribute,$this->atributes)) $this->atributes[$atribute] = $value;
    }
    
    public function hasChart(){
    	return "" != $this->getAtribute('chart_type');
    }
    
    public function toJsonResponse(){
    	
    	$data[0] = $this->toArrayex();
    	return json_encode($data);
    }
    
    public function toJsonComplete($default=true){
    	$data = array();
    	// GROUP
    	$data[0] = array("object_type" => "group",
    					 "group_id" => $this->getGroup()->getId(),
    					 "description" =>$this->getGroup()->getDescription(),
    					 "orden" => $this->getGroup()->getOrden());
    	// FILTERS
    	foreach($this->getComboList() as $combo){
    		array_push($data,$combo->toArrayex());
    	}
    	
    	// LINKS
    	foreach($this->links as $link){
    		array_push($data,$link->toArrayex());
    	}
    	
    	// REPORT
    	array_push($data,$this->toArrayex($default));
    	
    	return json_encode($data);
    }
    
    public function toArrayex($default=true){
    	$comboList = $this->getComboList();
    	$comboList2json = array();
    	foreach ($comboList as $combo){
    		if(count($combo->getValueSetDefault())>0 && ""!=$combo->getValueSetDefault()){
    			$valueDef = $combo->getValueSetDefault();
    			$comboList2json[]=$combo->getId()."=".$valueDef[0];
    		}else{
    			$comboList2json[]=$combo->getId();
    		}
    		
    	}
    	 
    	$chart_series2json = array();
    	foreach ($this->chart_series as $serie => $column){
    		$chart_series2json[]=$column;
    	}
    	 
    	$atributes2json = array();
    	foreach($this->atributes as $key => $value){
    		if(""!=$value && null != $value){
    			if($this->getAtribute($key)!=$this->getAtributeDefault($key)||$default){
    				$atributes2json[$key] = $value;
    			}
    		}
    	}
    	
    	$cronlog = "";
    	if($default) $cronlog = $this->getCronLog();
    		
    	$salida = array("object_type" => "report",
    			"report_id" => $this->getId(),
    			"title" => $this->getTitle(),
    			"report_sql" => $this->getSqlNoParams(),
    			"group_id" => $this->getGroup()->getId(),
    			"atributes" => $atributes2json,
    			"chart_series" => $chart_series2json,
    			"combos" =>$comboList2json,
    			"linkTR" => $this->getLinkList("TR"),
    			"linkTRVariables" => $this->getLinkVariableTR(),
    			"linkTD" => $this->getLinkList("TD"),
    			"cronLog" => $cronlog);
    	return $salida;
    }
    
    public function saveReport(){
    	try{
    		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
    		
    		$data = array(
    				'title' => $this->getTitle(),
    				'report_sql'  => $this->getSqlNoParams(),
    				'group_id'    => $this->getGroup()->getId()
    		);
    		
    		$where = array($connection->quoteInto('report_id=?', $this->getId()));
    		$whereMain = array($connection->quoteInto('entity_id=?', $this->getId()));
    		$where2 = array($connection->quoteInto('report_link_id in (SELECT id FROM '.$this->utils->gtn('sqlrpt_report_link').' WHERE report_id = ?)', $this->getId()));
    		$connection->beginTransaction();
    		
    		$connection->update($this->utils->gtn('sqlrpt_report'), $data, $whereMain);
    		
    		$connection->delete($this->utils->gtn('sqlrpt_report_combo'), $where);
    		$connection->delete($this->utils->gtn('sqlrpt_report_int'), $where);
    		$connection->delete($this->utils->gtn('sqlrpt_report_text'), $where);
    		$connection->delete($this->utils->gtn('sqlrpt_report_varchar'), $where);
    		$connection->delete($this->utils->gtn('sqlrpt_chart_series'), $where);
    		
    		$connection->delete($this->utils->gtn('sqlrpt_report_link_value'), $where2);
    		$connection->delete($this->utils->gtn('sqlrpt_report_link'), $where);
    		
    		$i=1;
    		foreach ($this->getComboList() as $combo){

    			$combosValueDef = $combo->getValueSetDefault();
    			$value = (count($combosValueDef)==0?"":$combosValueDef[0]);
    			$dataInsert = array(
    					'report_id' => $this->getId(),
    					'combo_id' => $combo->getId(),
    					'order_n' => $i,
    					'value' => $value
    			);
    			$connection->insert($this->utils->gtn('sqlrpt_report_combo'),$dataInsert);
    			$i++;
    		}
    		
    		// ATTRIBUTES
    		$this->saveAttributes($connection);
    		
    		// CHART SERIES
    		$this->saveChartSeries($connection);
    		
    		// LINKS
    		$this->saveLinks($connection);
    		
    		$connection->commit();
    		
    		$smsCron = $this->activateCron();
    		$data[0] = array("type" => "success-msg",
    				"msg" => "Report saved ".$smsCron);
    		
    		$this->logger->logReport("save",$this);
    		
    		return json_encode($data);
    		
    	}catch (Exception  $err){
	    	$data[0] = array("type" => "error-msg",
	    			"msg" => $err->getMessage());
	    	return json_encode($data);
	   	}
    }
    
    public function deleteReport(){
    	try{
    		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
    		
    		$connection->beginTransaction();
    		
    		$cronmanager = Mage::helper('sqlreport/CronManager');
    		$sms = $cronmanager->deleteCron($this->getId());
    		
    		$where = array($connection->quoteInto('report_id=?', $this->getId()));
    		$whereMain = array($connection->quoteInto('entity_id=?', $this->getId()));
    		$where2 = array($connection->quoteInto('report_link_id in (SELECT id FROM '.$this->utils->gtn('sqlrpt_report_link').' WHERE report_id = ?)', $this->getId()));  		
    		
    		$connection->delete($this->utils->gtn('sqlrpt_report_combo'), $where);
    		$connection->delete($this->utils->gtn('sqlrpt_report_int'), $where);
    		$connection->delete($this->utils->gtn('sqlrpt_report_text'), $where);
    		$connection->delete($this->utils->gtn('sqlrpt_report_varchar'), $where);
    		$connection->delete($this->utils->gtn('sqlrpt_chart_series'), $where);
    		$connection->delete($this->utils->gtn('sqlrpt_report_link_value'), $where2);
    		$connection->delete($this->utils->gtn('sqlrpt_report_link'), $where);
    		
    		$connection->delete($this->utils->gtn('sqlrpt_report_role'), $where);
    		$connection->delete($this->utils->gtn('sqlrpt_report'), $whereMain);
    		
    		$connection->commit();
    		
    		$this->logger->logReport("delete",$this);
    		
    		$data[0] = array("type" => "success-msg",
    				"msg" => "Report deleted");
    		return json_encode($data);
    	
    	}catch (Exception  $err){
    		$data[0] = array("type" => "error-msg",
    				"msg" => $err->getMessage());
    		return json_encode($data);
    	}
    	
    }
    
    public function hasCombo($combo_id){
    	foreach ($this->getComboList() as $combo){
    		if($combo->getId()==$combo_id){
    			return true;
    		}	
    	}
    	return false;
    }
    
    public function setChartSeries($series){
    	$this->chart_series=$series;
    }
    
    public function addMeAsNew(){
    	try{
    		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
    		$connection->beginTransaction();
    		// REPORT
    		$fields  = array(
    				'title' => $this->getTitle(),
    				'report_sql' => $this->getSqlNoParams(),
    				'group_id' => $this->getGroup()->getId()
    		);
    		$connection->insert($this->utils->gtn('sqlrpt_report'), $fields);
    		$id = $this->utils->getLastId($connection);
    		$this->setId($id);
    		
    		// FILTERS
    		$i=1;
    		foreach ($this->combosListBasic as $combo){
    			if($combo!=""){
    				$comboList = explode("=", $combo);
    				$comboId=$comboList[0];
    				$value = (count($comboList)>1?$comboList[1]:"");
    				$dataInsert = array(
    						'report_id' => $this->getId(),
    						'combo_id' => $comboId ,
    						'order_n' => $i,
    						'value' => $value
    				);
    				$connection->insert($this->utils->gtn('sqlrpt_report_combo'),$dataInsert);
    				$i++;
    			}
    		}
    		
    		// ATTRIBUTES
    		$this->saveAttributes($connection);
    		// CHART SERIES
    		$this->saveChartSeries($connection);
    		// LINKS
    		$this->saveLinks($connection);
    		
    		
    		$connection->commit();
    		
    		$smsCron = $this->activateCron();
    		
    		if(""==$smsCron){
    			$this->logger->logReport("add",$this);
    			return true;
    		}else{
    			return $smsCron;
    		}

    	}catch (Exception  $err){
    		return $err->getMessage();
    	}
    }
    
    public function saveAttributes($connection){
    	$connectionRead = Mage::getSingleton('core/resource')->getConnection('core_read');
    	
    	$select = $connectionRead->select()
    	->from(array('a' => $this->utils->gtn('sqlrpt_report_type')),
    			array('a.type_id', 'a.type_code','a.type'));
    	
    	$readresult=$connectionRead->fetchAll($select);
    	foreach ($readresult as $fila){
    		if(""!=$this->getAtribute($fila['type_code']) && $this->getAtribute($fila['type_code'])!=$this->getAtributeDefault($fila['type_code'])){
    			$dataInsert = array(
    					'type_id' => $fila['type_id'],
    					'report_id' => $this->getId() ,
    					'value' => $this->getAtribute($fila['type_code']));
    			$connection->insert($this->utils->gtn('sqlrpt_report_'.$fila['type']),$dataInsert);
    		}
    	}
    	
    }
    
    private function loadAtributesDefault(){
    	$connectionRead = Mage::getSingleton('core/resource')->getConnection('core_read');
    	 
    	$select = $connectionRead->select()
    							 ->from(array('a' => $this->utils->gtn('sqlrpt_report_type')),
    								    array('a.type_id', 'a.type_code','a.type'));
    	 
    	$readresult=$connectionRead->fetchAll($select);
    	foreach ($readresult as $fila){
    		$valueSystem = $this->setupmanager->getValue($fila['type_code']);
    		if(!array_key_exists($fila['type_code'],$this->atributes)){
    			$this->atributes[$fila['type_code']] = $valueSystem;
    		}else{
    			// El valor est� setado, comprobamos si el de por defecto (check) indica que se ha de coger el seteado o el por defecto
    			foreach($this->atributesDefault as $key=>$values){
    				$valorDefault = $this->getAtribute($key);
    				if("true"==$valorDefault && in_array($fila['type_code'],$values)){
    					// Se coge el de por defecto del sistema y no el setado
    					$this->setAtribute($fila['type_code'], $valueSystem);
    				}
    			}
    		}
    	}
    }
    
    // A�ade con valor false a la lista de atributos, los atributos agrupados �nicos
    private function addPrivateAtributesGrouped($atribute){
    	foreach ($this->atributesGroups as $group){
    		if(in_array($atribute,$group)){
    			foreach ($group as $atributeUnique){
    				if($atribute!=$atributeUnique){
    					$this->setAtribute($atributeUnique, "false");
    				}
    			}
    		}
    	}
    }
    
    public function saveChartSeries($connection){
    	$i=0;
    	foreach($this->chart_series as $serie){
    		if($serie!=""){
    			$dataInsert = array(
    					'report_id' => $this->getId(),
    					'serie_num' =>  $i+1,
    					'column_num' => $serie);
    			$connection->insert($this->utils->gtn('sqlrpt_chart_series'),$dataInsert);
    			$i++;
    		}
    		 
    	}
    }
    
    public function printJSChart(){
    	$xName = $this->getColumnsName();
    	$xName = $xName[$this->getAtribute('chartXValue') - 1];
    	
    	$salida = "nv.addGraph(function() {";
    	switch ($this->getAtribute('chart_type')){
    		case 'lineChart': 
    			$salida .="	var chart = nv.models.lineChart();";
    			break;
    		case 'stackedAreaChart': 
    			$salida .= "var chart = nv.models.stackedAreaChart()
                .x(function(d) { return d[0] })
                .y(function(d) { return d[1] })
                .clipEdge(true);";
    			break;
    		case 'discreteBarChart':
    			$salida .="var chart = nv.models.discreteBarChart()
					      .x(function(d) { return d.label })
					      .y(function(d) { return d.value })
					      .staggerLabels(true)
					      .tooltips(false)
					      .showValues(true);";
    			break;
    		case 'multiBarChart':
				$chartType = count($this->chart_series) > 2 && count($this->chart_series) <= 8
					? 'multiChart'
					: 'multiBarChart';
    			$salida .= "var chart = nv.models.{$chartType}();";
				if (count($this->chart_series) >= 2
					&& count($this->chart_series) <= 8){
					if (!is_null($this->_maxY1)){
						//$salida .= "chart.yDomain1([{$this->_minY1}, {$this->_maxY1}]);";
					}
					if ($this->_xAxisDateFormat){
						$salida .= "chart.xAxis.tickFormat(function(d) { return d3.time.format('{$this->_xAxisDateFormat}')(new Date(d*1000)); });";
					}
					//$salida .= "chart.xAxis.tickFormat(function(d) { console.log(d); console.log(new Date(d).value); return new Date(d).value; });";
					//$salida .= "chart.yAxis1.tickFormat(d3.format(',.1f'));";
					//$salida .= "chart.yAxis2.tickFormat(d3.format(',.1f'));";
				}
    			break;
    		case 'multiBarHorizontalChart':
    			$salida .= "var chart = nv.models.multiBarHorizontalChart()
					      .x(function(d) { return d.label })
					      .y(function(d) { return d.value })
					      .margin({top: 30, right: 20, bottom: 50, left: 175})
					      .showValues(true)
					      .tooltips(false)
					      .showControls(false);";
    			break;
    		case 'pieChart':
    			$salida .="var chart = nv.models.pieChart()
					      .x(function(d) { return d.label })
					      .y(function(d) { return d.value })
					      .showLabels(true);";
    			break;
    		case 'pieDonutChart':
    				$salida .="var chart = nv.models.pieChart()
					      .x(function(d) { return d.label })
					      .y(function(d) { return d.value })
					      .showLabels(true)
    					  .donut(true);";
    				break;
    	}
    	
    	/*if($this->getAtribute('chart_type') != 'pieChart' ||
    	   $this->getAtribute('chart_type') != 'pieDonutChart'){
    		$salida .= " chart.xAxis
			      .axisLabel('".$xName."')
			      .tickFormat(d3.format(',r'));";
		}
		if($this->getAtribute('chart_type') != 'pieChart' ||
    	   $this->getAtribute('chart_type') != 'pieDonutChart'){
			$salida .= "  chart.yAxis
			      .tickFormat(d3.format(',.2f'));";
    	}
    	*/
		$salida .= "d3.select('#chart svg')
			      .datum(data)
			      .transition().duration(500)
			      .call(chart);
			  nv.utils.windowResize(chart.update);
			  return chart;
			});";
    	
    	return $salida;
    }
    
    public function setChart_series($serie){
    	$this->chart_series=$serie;
    }
    
    public function printDataChart(){
    	
    	$xName = $this->getColumnsName();
    	$xName = $xName[$this->getAtribute('chartXValue') - 1];
		$addExtraAxis = count($this->chart_series) >= 2
			&& count($this->chart_series) <= 8;
		$result = $this->getResults();
		if (is_array($result)
			&& $firstItem = reset($result)){
			$formats = array(
				'%Y-%m-%d %H'	=> '/^\d{4}-\d{2}-\d{2} \d{2}$/',
				'%Y-%m-%d'		=> '/^\d{4}-\d{2}-\d{2}$/',
				'%Y-%m'			=> '/^\d{4}-\d{2}$/',
				'%Y'			=> '/^20\d{2}$/'
			);
			foreach ($formats as $format => $expr){
				if (preg_match($expr, $firstItem[$xName])){
					$this->_xAxisDateFormat = $format;
					break;
				}
			}
		}
    	$salida = "data = [";
    	
    	for($i = 1; $i<= sizeof($this->chart_series);$i++){
    		$columnNumber = $this->chart_series[$i];
    		$yName = $this->getColumnsName();
    		$yName = $yName[$columnNumber-1];
	    	switch ($this->getAtribute('chart_type')){
	    		case 'lineChart': 
	    		case 'multiBarChart':
					$yIndex = ($i-1)%2+1;
	    			$salida .="{key: \"".$yName."\", values: [ ";
	    			foreach ($this->getResults() as $result){
						$value = $this->prs($result[$yName]);
						if ($addExtraAxis) {
							if ($yIndex == 1
								&& (is_null($this->_minY1)
									|| $this->_minY1 > $value)){
								$this->_minY1 = $value;
							}
							if ($yIndex == 1
								&& (is_null($this->_maxY1)
									|| $this->_maxY1 < $value)){
								$this->_maxY1 = $value;
							}
							if ($yIndex == 2
								&& (is_null($this->_minY2)
									|| $this->_minY2 > $value)){
								$this->_minY2 = $value;
							}
							if ($yIndex == 2
								&& (is_null($this->_maxY2)
									|| $this->_maxY2 < $value)){
								$this->_maxY2 = $value;
							}
						}
			    		$salida .= "{x:".$this->prs($result[$xName], $this->_xAxisDateFormat).",y:".$value."},";
			      	}
			      	$salida .= "]";
					if ($addExtraAxis){
						$salida .= ', "yAxis": '.$yIndex;
							$salida .= count($this->chart_series) > 4
								? ', "type": "line"'
								: ', "type": "bar"';
					}
					$salida .= "},";
	    		break;
	    		case 'stackedAreaChart':
	    			$salida .="{key: \"".$yName."\", values: [ ";
	    			foreach ($this->getResults() as $result){
	    				$salida .= "[".$this->prs($result[$xName]).",".$this->prs($result[$yName])."],";
	    			}
	    			$salida .= "]},";
	    		break;
	    		case 'discreteBarChart':
	    		case 'multiBarHorizontalChart':
	    			$salida .="{key: \"".$yName."\", values: [ ";
	    			foreach ($this->getResults() as $result){
			    		$salida .= "{\"label\":".$this->prs($result[$xName]).",\"value\":".$this->prs($result[$yName])."},";
			      	}
			      	$salida .= "]},";
			    break;
	    		case 'lineAndBar':
	    			$salida .="{key: \"".$yName."\",bar: true, values: [ ";
	    			foreach ($this->getResults() as $result){
			    		$salida .= "{\"label\":".$this->prs($result[$xName]).",\"value\":".$this->prs($result[$yName])."},";
			      	}
			      	$salida .= "]},";
	    			break;
	    		case 'pieChart':
	    		case 'pieDonutChart':
	    			foreach ($this->getResults() as $result){
			    		$salida .= "{\"label\":".$this->prs($result[$xName]).",\"value\":".$this->prs($result[$yName])."},";
			      	}
	    		break;
	    	}
	    	
    	}
		$salida = substr($salida,0,strlen ($salida)-1);
		$salida.="];";
    	
    	return $salida;
    }
    
    private function prs($value, $dateFormat = null){
		if ($dateFormat){
			$parts = date_parse_from_format(str_replace('%', '', $dateFormat), $value);
			$this->_date->setDate($parts['year'], $parts['month'], $parts['day']);
			$this->_date->setTime($parts['hour'], $parts['minute']);
			return $this->_date->getTimestamp();
		}
    	if(is_numeric($value)) {
    		return $value;
    	}else{
    		return '"'.$value.'"';
    	}
    }
   
    
    /**
     * Export report grid to CSV format
     */
    public function exportCsv(){
    	return $this->exportCsvResult($this->getResults());
    }
    
    public function exportCsvResult($resultSet){
    	$result="";
    	$sep=$this->setupmanager->getValue("exp_col_delimiter");
    	 
    	if($this->setupmanager->getValue("svn_print_header")){
    		$primero=true;
    		foreach ($this->getColumnsName() as $_column){
    			if($primero){
    				$result.= $_column;
    				$primero=false;
    			}else{
    				$result.= $sep.$_column;
    			}
    		}
    		$result.="\r\n";
    	}
    	
    	foreach ($resultSet as $rowResult){
    		$primero=true;
    		foreach ($this->getColumnsName() as $_column){
    			if($primero){
    				$primero=false;
    			}else{
    				$result.=$sep;
    			}
    			if($this->utils->getTypeData($rowResult[$_column])==1){
    				$result.=$this->setupmanager->getValue("svn_text_qualifier").$rowResult[$_column].$this->setupmanager->getValue("svn_text_qualifier");
    			}else{
    				$result.=$rowResult[$_column];
    			}
    		}
    		$result.="\r\n";
    	}
    	
    	return $result;
    }
    
    /**
     * Export report grid to XML format
     */
    public function exportXml($wsName) {
    	return $this->exportXmlResult($wsName,$this->getResults());
    }
    public function exportXmlResult($wsName,$resultSet,$offset=1){
    	
    	$lengtColum = array();
    	$whereColum = array();
    	$xml = '<'.'?xml version="1.0"?'.'><'.'?mso-application progid="Excel.Sheet"?'.'>
				<Workbook xmlns:x="urn:schemas-microsoft-com:office:excel"
  				xmlns="urn:schemas-microsoft-com:office:spreadsheet"
  				xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
    	
    	$xml .= '<Styles>
				  <Style ss:ID="Default" ss:Name="Normal">
				   <Alignment/>
				   <Borders/>
				   <Font/>
				   <Interior/>
				   <NumberFormat/>
				   <Protection/>
				  </Style>
				  <Style ss:ID="header">';
    	
    	$xml .= $this->getXmlStyle("Header");
    	
		$xml .= '</Style>
				  <Style ss:ID="dataOdd">';
		
    	$xml .= $this->getXmlStyle("OddRow");
				   
		$xml .= '</Style>  
    			  <Style ss:ID="dataEven">';
    	$xml .= $this->getXmlStyle("EvenRow");
				   
		$xml .= '</Style>
				 </Styles>';
    	$xml .= '<Worksheet ss:Name="'.$wsName.'"><Table>';
    	// HEADER
    	$data = '<Row>';
    	$column = 1;
    	foreach ($this->getColumnsName() as $_column){
    		$data .= '<Cell ss:StyleID="header"><Data ss:Type="String">'.$_column.'</Data></Cell>';
    		$lengtColum[$column]=strlen($_column);
    		$whereColum[$column]="Header";
    		$column++;
    	}
    	$data .= '</Row>';
    	
    	// DATA
    	$impar = true;
    	foreach ($resultSet as $rowResult){
    		$data .= '<Row>';
    		$style = ($impar)?"dataOdd":"dataEven";
    		$column = 1;
    		foreach ($this->getColumnsName() as $_column){
    			$data .= '<Cell ss:StyleID="'.$style.'"><Data ss:Type="String">'.$rowResult[$_column].'</Data></Cell>';
    			if(strlen($rowResult[$_column])>$lengtColum[$column]){
    				$lengtColum[$column]=strlen($rowResult[$_column]);
    				$whereColum[$column]=($impar)?"OddRow":"EvenRow";
    			}
    			$column++;
    		}
    		
    		$data .= '</Row>';
    		$impar = !$impar;
    	}
    	$widthCol = '';
    	for($i = 1; $i<= sizeof($lengtColum);$i++){
    		$size = ($this->getXlsAtribute("xls".$whereColum[$i]."Size")=="")?10:$this->getXlsAtribute("xls".$whereColum[$i]."Size");
    		$ancho = $lengtColum[$i]*$size*0.7;
    		$widthCol .= '<Column ss:AutoFitWidth="0" ss:Width="'.$ancho.'"/>';
    	}
    	
    	$xml .= $widthCol.$data.'</Table></Worksheet>';
    	$xml .= '</Workbook>';
    	return $xml;
    }

    private function getXmlStyle($where){
    	$font = '<Font ';
	    	if("true"==$this->getXlsAtribute("xls".$where."Bold")) $font.= 'ss:Bold="1" ';
	    	if("true"==$this->getXlsAtribute("xls".$where."Italic")) $font.= 'ss:Italic="1" ';
	    	if(""!=$this->getXlsAtribute("xls".$where."Size")) $font.= 'ss:Size="'.$this->getXlsAtribute("xls".$where."Size").'" ';
	    	if(""!=$this->getXlsAtribute("xls".$where."Font")) $font.= 'ss:FontName="'.$this->getXlsAtribute("xls".$where."Font").'" ';
	    	if(""!=$this->getXlsAtribute("xls".$where."Color")) $font.= 'ss:Color="#'.$this->getXlsAtribute("xls".$where."Color").'" ';
    	$font .= "/>";
    	
    	$alignment = '<Alignment ';
    		if("true"==$this->getXlsAtribute("xls".$where."AlignCenter")) $alignment.= 'ss:Horizontal="Center" ';
    		if("true"==$this->getXlsAtribute("xls".$where."AlignRight")) $alignment.= 'ss:Horizontal="Right" ';
    		if("true"==$this->getXlsAtribute("xls".$where."AlignLeft")) $alignment.= 'ss:Horizontal="Left" ';
    	$alignment .= "/>";
    	
    	$interior = '<Interior ';
    		if(""!=$this->getXlsAtribute("xls".$where."BackgroundColor")) $interior.= 'ss:Color="#'.$this->getXlsAtribute("xls".$where."BackgroundColor").'" ss:Pattern="Solid" ';
    	$interior .= "/>";
    	
    	$border = '<Borders>';
    		if("true"==$this->getXlsAtribute("xls".$where."BorderBottom")||"true"==$this->getXlsAtribute("xls".$where."BorderAll")) $border.= '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/> ';
    		if("true"==$this->getXlsAtribute("xls".$where."BorderLeft")||"true"==$this->getXlsAtribute("xls".$where."BorderAll")) $border.= '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/> ';
    		if("true"==$this->getXlsAtribute("xls".$where."BorderRight")||"true"==$this->getXlsAtribute("xls".$where."BorderAll")) $border.= '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/> ';
    		if("true"==$this->getXlsAtribute("xls".$where."BorderTop")||"true"==$this->getXlsAtribute("xls".$where."BorderAll")) $border.= '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/> ';
    	$border .= '</Borders>';
    	
    	return $font.$alignment.$interior.$border;
    }
    
	protected function loadLinkList(){    
    	try{
    		$coreResource = Mage::getSingleton('core/resource');
    		$connection = $coreResource->getConnection('core_read');
    		$select = $connection->select()
    				->from(array('a' => $this->utils->gtn('sqlrpt_report_link')),
    					   array('id_report_link' =>'a.id','a.column'))
    				->join(array('b' => $this->utils->gtn('sqlrpt_link')),
    						'a.link_id=b.entity_id'.
    					    ' AND a.report_id = "'.$this->getId().'"',
    						array('b.entity_id','b.description','b.url','b.type'));
    		$readresult=$connection->fetchAll($select);
	    	foreach ($readresult as $fila){
	    		$link = Mage::getModel('sqlreport/link');
	    		$link->loadMe2($fila['description'],$fila['url'],$fila['type']);
	    		$link->setId($fila['entity_id']);
	    		$link->loadVariable($fila['id_report_link'],$fila['column']);
	    		$link->setReportLinkId($this->getId());
	    		array_push($this->links,$link);
	    	}
    	}catch (Exception  $err){
    		$this->error=true;
	    	$this->errorMsg = "Error loading link list :".$err->getMessage();
	    	$this->errorSQL = $sql;
    	}
    }
    
    public function addLink($link){
    	array_push($this->links,$link);
    }
    
    public function getLinkList($type){
    	$list = array();
    	foreach ($this->links as $link){
    		if($type==$link->getType()){
    			$data = array($link->getId() => $link->getColumn());
    			array_push($list,$data);
    		}
    	}
    	return $list;
    }
    
    public function getLinkVariableTR(){
    	foreach ($this->links as $link){
    		if("TR"==$link->getType()){
    			return $link->getVariables();
    		}
    	}
    	return array();
    }
    
    public function getLinkResult($type,$column,$result){
    	if("TR" == $type){
    		$urlLink = "";
    		foreach ($this->links as $link){
    			
    			if($type==$link->getType()){
    				$urlLink = $link->replaceVariables($link->getUrl(),$result,$this->getColumnsName());
    			}
    		}
    		if(""!=$urlLink){
    			$title = $link->replaceVariables($link->getDescription(),$result,$this->getColumnsName());
    			$urlLink = Mage::helper('adminhtml')->getUrl($urlLink, array());
    			return 'title= "'.$title.'" href = "'.$urlLink.'" target="'.$link->getTarget().'"';
    		}else{
    			return "";
    		}	
    	}else if("TD"==$type){
	    	// LINKS
		    $textoFinal = "";
		    if($result!=""){
	    		foreach ($this->links as $link){
	    			$urlLink = $link->getType();
	    			if($type==$link->getType() && $column==$link->getColumn()){
	    				$urlLink = $link->replaceVariables($link->getUrl(),$result,$this->getColumnsName());
	    				$ruta = $link->getUrl();
		    			$urlLink = Mage::helper('adminhtml')->getUrl($ruta, array());
		    			$textoFinal = '<a href="'.$urlLink.'" title="'.$link->getDescription().'" target="'.$link->getTarget().'">'.$result.'</a>';
	    			}
	    		}
	    		
				if($textoFinal==""){
					// EMAIL
					$regex = '/(\S+@\S+\.\S+)/';
			    	$replace = '<a href="mailto:$1">$1</a>';
			    	$textoFinal = preg_replace($regex, $replace, $result);
				}
	    	}

		    return $textoFinal;
    	}
    	
    }
    
    public function resetLinks(){
    	$this->links=array();
    }
    
    private function saveLinks($connection){
    	foreach($this->links as $link){
    		$dataInsert = array(
    				'report_id' => $this->getId(),
    				'link_id' =>  $link->getId(),
    				'column' => $link->getColumn());
    		$connection->insert($this->utils->gtn('sqlrpt_report_link'),$dataInsert);
    		$id = $connection->lastInsertId();
    		$variablesList = $link->getVariables();
    		foreach($variablesList as $key=>$value){
    			$dataInsert = array(
    				'report_link_id' => $id,
    				'variable' =>  $key,
    				'column_num' => $value);
    			$connection->insert($this->utils->gtn('sqlrpt_report_link_value'),$dataInsert);
    		}
    	}
    }
    
    private function activateCron(){
    	$lastCronActive = $this->getAtribute("cronActiveLast")==""?"false":$this->getAtribute("cronActiveLast");
    	$cronmanager = Mage::helper('sqlreport/CronManager');
    	if($lastCronActive=="false" && $this->getAtribute("cronActive")=="true"){
    		$sms = $cronmanager->addCron($this->getId());
    		if(true==$sms){
    			return Mage::helper('catalog')->__('Cron activated correctly');
    		}else{
    			return $sms;
    		}
    	}if($lastCronActive=="true" && $this->getAtribute("cronActive")=="false"){
    		$sms = $cronmanager->deleteCron($this->getId());
    		if(true==$sms){
    			return Mage::helper('catalog')->__('Cron desactivated correctly');
    		}else{
    			return $sms;
    		}
    	}else if($this->getAtribute("cronActive")=="true" && $this->getAtribute("cronStringLast")!=$this->getAtribute("cronString")){
    		$sms = $cronmanager->deleteCron($this->getId());
    		if(true==$sms){
    			$sms = $cronmanager->addCron($this->getId());
    			if(true==$sms){
    				return Mage::helper('catalog')->__('Cron updated correctly');
    			}else{
    				return $sms;
    			}
    		}else{
    			return "Unable delete cron for update";
    		}
    	}
    	return "";
    }
    
    public function getResultAsHTML($resultSet,$offset){
    	$salida = "";

    	if(sizeof($resultSet)>0){
    	    $salida .='<table cellspacing="0" class="data sortable resizable mergeable ">';
    	    $columAct = 0;
    	    foreach ($this->getColumnsName() as $_column){
    	    	$columAct++;
    	    	if($columAct>=$offset) $salida .='<col '.$this->getHtmlProperty($_column).'/>';
    	    }
    	    $salida .='   <thead>';
    		$salida .='		<tr class="headings">';
    		$columAct = 0;
    		foreach ($this->getColumnsName() as $_column){
    			$columAct++;
    			if($columAct>=$offset) $salida .='	<th ><span class="no-br">'.$this->getHeaderHtml($_column).'</span></th>';
    		}
    		$salida .='		</tr>';
    	    $salida .='    </thead>';
			$salida .='    <tbody>';
    	    if ($this->getSize()>0){ 
    	        $fila=0;
    	        foreach ($resultSet as $result){  
    	        	$fila++;
    	        	$columna=0;
    	        	
    	        	$salida .='<tr '.$this->getLinkResult("TR",0,$result).'>';
    	        	$columAct = 0;
    	        	foreach ($this->getColumnsName() as $_column){  
    	        		$columAct++;
    	        		$this->addResult2Sum($columna,$result[$_column]);
    	        		$columna++;
    	        		if($columAct>=$offset) $salida .='	<td class="'.$this->getCssProperty($result[$_column]).' ">'.$this->getLinkResult("TD",$columna,$result[$_column]).'</td>';
    	        	}
    	        	$salida .='</tr>';
    	            
				}
    		}
    		$salida .='	</tbody>';
    		$salida .='	<tfoot>';
    	        	
    	    if($this->haveResultSum()){
    	    	$columna=0;
	    	    $salida .='	    	<tr class="totals">';
	    	    $columAct = 0;
	    	    foreach ($this->getResultSum() as $resultSum){
	    	    	$columAct++;
	    	    	if($columAct>=$offset){
		    	    	$columna++;
		    	    	if($columna==1 && $resultSum=="s"){
		    	    		$salida .=' <th>Total of '. sizeof($resultSet).' Rows</th>';
		    	    	}else{
		    	     		$salida .=' <th class=" a-right">'.($resultSum=="s")?"":$resultSum.'</th>';
		    	        }
	    	   		}
	    	    }
				$salida .='	</tr>';
    	    }else{
    	        $salida .='	<tr class="totals">';
    	        $salida .='		<th colspan="'.$this->getNumColumns().'">'.$this->getSize().' Rows</th>';
    	        $salida .='	</tr>';
    	    }
    	    $salida .='    </tfoot>';
    	    $salida .='</table>';
		} 
		return $salida;
    }
    
    public function getNumPages(){
    	if("all"==$this->getRowPerPage()){
    		return 1;
    	}else{
    		return ceil($this->getTotalRows()/$this->getRowPerPage());
    	}
    }
    
    public function setActualPage($page){
    	if("all"==$this->getRowPerPage()){
    		$this->actualPage=1;
    	}else{
    		$this->actualPage=$page;
    	}
    }
    
    public function getActualPage(){
    	return $this->actualPage;
    }
    
    public function setRowPerPage($rowPerPage){
    	$this->rowPerPage=$rowPerPage;
    }
    
    public function getRowPerPage(){
    	return $this->rowPerPage;
    }
    
    private function setTotalRow($cuenta){
    	$this->totalRows=$cuenta;
    }
    
    public function getTotalRows(){
    	return $this->totalRows;
    }
    
    public function getCronLog(){
    	return $this->logger->getLogReportEmail($this->getId());
    }


    /**
     * Export report grid to PDF format
     */
    public function exportPDF($fileName,$path) {
    	return $this->exportPDFResult($fileName,$path,$this->getResults());
    }
    
    public function exportPDFResult($fileName,$path,$resultSet,$offset=1){
    	try {
    		// setting default styles
    		// Reset
    		$this->resetResult2Sum();
    		$this->setPDFStyleDefault();
    		//$pdf = Mage::getModel('sqlreport/pdftable_document');
    		$pdf = new Serpini_Sqlreport_Model_PdfTable_Document($fileName, $path);
    		// header
    		if($this->hasCombos()){
    			$header = $this->getPDFHeader();
    			$pdf->setHeader($header);
    		}
    		// create page
    		$page = $pdf->createPage();
    		// define font resource
    		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
    		// set font
    		$page->setFont($font, 6);
    		// insert logo
    		$this->pdfInsertLogo($pdf);
    		
    		// insert title
    		$pdf->setTitle($this->setParamsTitle($this->getTitle()));
    		$pdf->setTitleSize($this->getAtribute("pdfTitleFontSize"));
    		$pdf->setTitleFontName($this->getPdfFontName($this->getAtribute("pdfTitleFontName"), $this->getAtribute("pdfTitleFontBold"), $this->getAtribute("pdfTitleFontItalic")));
    		$pdf->setTitleColor("#".$this->getAtribute("pdfTitleFontColor"));
    		
    		// insert footer
    		$pdf->setFooter($this->getPDFFooter());
    		// create table
    		$table = $this->getPDFTable($resultSet,$offset);
    		
    		// add table to page
    		$page->addTable($table, 0, 0);
    		
    		// add page to document
    		$pdf->addPage($page);
    		// save as file
    		$pdf->save();
			
    		$this->resetResult2Sum();
    		return true;
    	} catch (Zend_Pdf_Exception $e) {
    		Mage::log(get_class($this).'.'.__FUNCTION__.' error generating pdf '.$fileName.' ERROR:'.print_r($e,true), Zend_Log::INFO,'sqlreport.log');
    		return $e->getMessage();
    	} catch (Exception $e) {
    		Mage::log(get_class($this).'.'.__FUNCTION__.' error generating pdf '.$fileName.' ERROR:'.print_r($e,true), Zend_Log::INFO,'sqlreport.log');
    		return $e->getMessage();
    	}
    }
    
    private function getPDFTable($resultSet,$offset){
    	// Fuente datos tabla
    	$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
    	
    	$columns = $this->getColumnsName();
    	$numberColumns = count($columns)-intval($offset)+1;
    	Mage::log(get_class($this).'.'.__FUNCTION__.' numberColumns '.$numberColumns, Zend_Log::INFO,'sqlreport.log');
    	$table = new Serpini_Sqlreport_Model_PdfTable_Table(intval($numberColumns));
    	// Header
    	$row = new Serpini_Sqlreport_Model_PdfTable_Row();
    	$cols = array();
    	$columna=0;
    	$columAct=0;
    	foreach ($this->getColumnsName() as $label){
    		$columAct++;
    		if($columAct>=$offset){
	    		$columna++;
	    		$col = new Serpini_Sqlreport_Model_PdfTable_Column();
	    		$col->setText($label);
	    		$col->setAlignment($this->getPdfAlign($this->getAtribute('pdfTableHAlignLeft'), $this->getAtribute('pdfTableHAlignCenter'), $this->getAtribute('pdfTableHAlignRight')));
	    		$col->setColor(new Zend_Pdf_Color_Html("#".$this->getAtribute('pdfTableHFontColor')));
	    		if($columna==1){
	    			if("true"==$this->getAtribute("pdfTableHBorderAll")||"true"==$this->getAtribute("pdfTableHBorderLeft")) $col->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::LEFT, new Zend_Pdf_Style());
	    		}else if($columna == $numberColumns){
	    			if("true"==$this->getAtribute("pdfTableHBorderAll")||"true"==$this->getAtribute("pdfTableHBorderRight")) $col->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::RIGHT, new Zend_Pdf_Style());
	    			if("true"==$this->getAtribute("pdfTableHBorderInV")) $col->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::LEFT, new Zend_Pdf_Style());
	    		}else{
	    			if("true"==$this->getAtribute("pdfTableHBorderInV")) $col->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::LEFT, new Zend_Pdf_Style());
	    		}
	    		if("true"==$this->getAtribute("pdfTableHBorderAll")||"true"==$this->getAtribute("pdfTableHBorderInH")){
	    			$col->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::TOP, new Zend_Pdf_Style());
	    			$col->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::BOTTOM, new Zend_Pdf_Style());
	    		}
	    		$cols[] = $col;
    		}
    	}
    	$row->setColumns($cols);
    	$row->setFont($this->getPdfFontName($this->getAtribute("pdfTableHFontName"), $this->getAtribute("pdfTableHFontBold"), $this->getAtribute("pdfTableHFontItalic")), $this->getAtribute('pdfTableHFontSize'));
    	$row->setBackgroundColor("#".$this->getAtribute("pdfTableHBackgroundColor"));
    	$row->setCellPaddings(array(2,2,2,2));
    	$table->setHeader($row);
    	 
    	// Datos
    	$isOdd=true;
    	$rowAct = 0;
    	$rowNum = count($resultSet);
    	foreach ($resultSet as $rowResult){
    		$rowAct++;
    		$row = new Serpini_Sqlreport_Model_PdfTable_Row();
    		$cols = array();
    		$columna=0;
    		$columAct=0;
    		foreach ($columns as $_column){
    			$columAct++;
    			if($columAct>=$offset){
	    			$col = new Serpini_Sqlreport_Model_PdfTable_Column();
	    			$col->setText($rowResult[$_column]);
	    			$this->addResult2Sum($columna,$rowResult[$_column]);
	    			$col->setAlignment($this->getPdfAlign($this->getAtribute('pdfTableDAlignLeft'), $this->getAtribute('pdfTableDAlignCenter'), $this->getAtribute('pdfTableDAlignRight')));
	    			$col->setColor(new Zend_Pdf_Color_Html("#".$this->getAtribute('pdfTableDFontColor')));
	    			$columna++;
	    			if($columna==1){
	    				if("true"==$this->getAtribute("pdfTableDBorderAll")||"true"==$this->getAtribute("pdfTableDBorderLeft")) $col->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::LEFT, new Zend_Pdf_Style());
	    			}else if($columna == $numberColumns){
	    				if("true"==$this->getAtribute("pdfTableDBorderAll")||"true"==$this->getAtribute("pdfTableDBorderRight")) $col->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::RIGHT, new Zend_Pdf_Style());
	    				if("true"==$this->getAtribute("pdfTableDBorderInV")) $col->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::LEFT, new Zend_Pdf_Style());
	    			}else{
	    				if("true"==$this->getAtribute("pdfTableDBorderInV")) $col->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::LEFT, new Zend_Pdf_Style());
	    			}	
	    			if($rowAct<=$rowNum && "true"==$this->getAtribute("pdfTableDBorderInH")) $col->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::TOP, new Zend_Pdf_Style());
	    			$cols[] = $col;
    			}
    		}
    		
    		$row->setColumns($cols);
    		if($isOdd){
    			$row->setBackgroundColor("#".$this->getAtribute("pdfTableDOBackgroundColor"));
    		}else{
    			$row->setBackgroundColor("#".$this->getAtribute("pdfTableDEBackgroundColor"));
    		}
    		$isOdd=!$isOdd;
    		$row->setFont($this->getPdfFontName($this->getAtribute("pdfTableDFontName"), $this->getAtribute("pdfTableDFontBold"), $this->getAtribute("pdfTableDFontItalic")), $this->getAtribute('pdfTableDFontsize'));
    		
    		if($rowAct == 1 && ("true"==$this->getAtribute("pdfTableDBorderAll")||"true"==$this->getAtribute("pdfTableDBorderTop"))) $row->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::TOP, new Zend_Pdf_Style());
    		if($rowAct == $rowNum && ("true"==$this->getAtribute("pdfTableDBorderAll")||"true"==$this->getAtribute("pdfTableDBorderBottom"))) $row->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::BOTTOM, new Zend_Pdf_Style());

    		$row->setCellPaddings(array(2,2,2,2));
    		$table->addRow($row);
    	}
    	
    	// Resultados
    	if("true"==$this->getAtribute("pdfTableFShow")){
    		$footer = $this->getPDFRowFooter($resultSet,$offset);
    		$table->setFooter($footer);
    	}
    	
    	return $table;
    }
    
    private function getPDFHeader(){
    	$table = new Serpini_Sqlreport_Model_PdfTable_Table(1);
    	
    	if(""!=$this->getAtribute('reportDescription')){
    		$cols = array();
    		$col = new Serpini_Sqlreport_Model_PdfTable_Column();
    		$col->setText($this->setParamsTitle($this->getAtribute('reportDescription')));
    		$col->setAlignment($this->getPdfAlign($this->getAtribute('pdfDescriptionAlignLeft'), $this->getAtribute('pdfDescriptionAlignCenter'), $this->getAtribute('pdfDescriptionAlignRight')));
    		$col->setColor(new Zend_Pdf_Color_Html("#".$this->getAtribute('pdfDescriptionFontColor')));
    		$cols[] = $col;

    		$row = new Serpini_Sqlreport_Model_PdfTable_Row();
    		$row->setColumns($cols);
    		$row->setFont($this->getPdfFontName($this->getAtribute("pdfDescriptionFontName"), $this->getAtribute("pdfDescriptionFontBold"), $this->getAtribute("pdfDescriptionFontItalic")), $this->getAtribute('pdfDescriptionFontSize'));
    		$row->setCellPaddings(array(2,2,2,$this->getAtribute("pdfDescriptionSpace")));
    		$table->addRow($row);
    	}
    	
    	if("true"==$this->getAtribute('pdfFiltersShow')){
	    	foreach ($this->getComboList() as $combo){
	    		if($combo->getType()!="evaluated" && ""!=$combo->getTitle()){
	    			$cols = array();
	    			$col = new Serpini_Sqlreport_Model_PdfTable_Column();
	    			$col->setText($combo->getTitle().": ".$combo->getValueSetString());
	    			$col->setColor(new Zend_Pdf_Color_Html("#".$this->getAtribute('pdfFilterFontColor')));
	    			$cols[] = $col;
	    			
	    			$row = new Serpini_Sqlreport_Model_PdfTable_Row();
	    			$row->setColumns($cols);
	    			$row->setFont($this->getPdfFontName($this->getAtribute("pdfFiltersFontName"), $this->getAtribute("pdfFilterFontBold"), $this->getAtribute("pdfFilterFontItalic")), $this->getAtribute('pdfFiltersFontSize'));
	    			$row->setCellPaddings(array(2,2,2,20));
	    			$table->addRow($row);
	    		}
	    	}
    	}

    	return $table;
    }
    
    private function getPDFRowFooter($resultSet,$offset){
    	$row = new Serpini_Sqlreport_Model_PdfTable_Row();
    	$columna=0;
    	$cols = array();
    	$columns = count($this->getResultSum())-$offset+1;
    	$columAct=0;
    	foreach ($this->getResultSum() as $resultSum){
    		$columAct++;
    		if($columAct>=$offset){
				$columna++;
				$texto="";
				if($columna==1 && $resultSum=="s"){
	    	        $texto="Total of ".count($resultSet)." Rows";
				}else{
	    	        $texto=($resultSum=="s")?"":$resultSum; 
				}
				
				$col = new Serpini_Sqlreport_Model_PdfTable_Column();
				$col->setText($texto);
				$col->setBackgroundColor(new Zend_Pdf_Color_Html("#".$this->getAtribute("pdfTableFBackgroundColor")));
				$col->setAlignment($this->getPdfAlign($this->getAtribute('pdfTableFAlignLeft'), $this->getAtribute('pdfTableFAlignCenter'), $this->getAtribute('pdfTableFAlignRight')));
				$col->setColor(new Zend_Pdf_Color_Html("#".$this->getAtribute('pdfTableFFontColor')));
				
	    		if($columna==1){
	    			if("true"==$this->getAtribute("pdfTableFBorderAll")||"true"==$this->getAtribute("pdfTableFBorderLeft")) $col->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::LEFT, new Zend_Pdf_Style());
	    		}else if($columna == $columns){
	    			if("true"==$this->getAtribute("pdfTableFBorderAll")||"true"==$this->getAtribute("pdfTableFBorderRight")) $col->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::RIGHT, new Zend_Pdf_Style());
	    			if("true"==$this->getAtribute("pdfTableFBorderInV")) $col->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::LEFT, new Zend_Pdf_Style());
	    		}else{
	    			if("true"==$this->getAtribute("pdfTableFBorderInV")) $col->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::LEFT, new Zend_Pdf_Style());
	    		}
	    		if("true"==$this->getAtribute("pdfTableFBorderAll")||"true"==$this->getAtribute("pdfTableFBorderTop")) $col->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::TOP, new Zend_Pdf_Style());
	    		if("true"==$this->getAtribute("pdfTableFBorderAll")||"true"==$this->getAtribute("pdfTableFBorderBottom")) $col->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::BOTTOM, new Zend_Pdf_Style());
				
				$cols[] = $col;
    		}
    	 }
    	 $row->setColumns($cols);
    	 $row->setFont($this->getPdfFontName($this->getAtribute("pdfTableFFontName"), $this->getAtribute("pdfTableFFontBold"), $this->getAtribute("pdfTableFFontItalic")), $this->getAtribute('pdfTableFFontsize'));
    	 
    	 $row->setCellPaddings(array(2,2,2,2));
    	 return $row;
    }
    
    private function getPDFFooter(){
    	$table = null;
    	if(""!=$this->getAtribute('pdfFooterString')){
    		
    		$table = new Serpini_Sqlreport_Model_PdfTable_Table(1);
    		$cols = array();
    		$col = new Serpini_Sqlreport_Model_PdfTable_Column();
    		$col->setText($this->setParamsTitle($this->getAtribute('pdfFooterString')));
    		$col->setAlignment($this->getPdfAlign($this->getAtribute('pdfFooterAlignLeft'), $this->getAtribute('pdfFooterAlignCenter'), $this->getAtribute('pdfFooterAlignRight')));
    		$col->setColor(new Zend_Pdf_Color_Html("#".$this->getAtribute('pdfFooterFontColor')));
    		$cols[] = $col;
    	
    		$row = new Serpini_Sqlreport_Model_PdfTable_Row();
    		$row->setColumns($cols);
    		$row->setFont($this->getPdfFontName($this->getAtribute("pdfFooterFontName"), $this->getAtribute("pdfFooterFontBold"), $this->getAtribute("pdfFooterFontItalic")), $this->getAtribute('pdfFooterFontSize'));
    		$row->setCellPaddings(array(2,2,2,$this->getAtribute("pdfDescriptionSpace")));
    		$table->addRow($row);
    	}
    	return $table;
    }

    private function pdfInsertLogo($document){
    	// images/media/logo.png
    	$fullFileName="";
    	if(""==$this->getAtribute("pdfLogoUrl")){
    		$fullFileName = $this->getUrlLogo();
    	}else{
    		$fullFileName = $this->getAtribute("pdfLogoUrl");
    	}
    	
    	
    	if (file_exists($fullFileName)) {
    		if(mime_content_type($fullFileName)=="image/gif"){
    			$fileNamePNG = substr($fullFileName, 0,strlen($fullFileName)-3)."png";
    			imagepng(imagecreatefromstring(file_get_contents($fullFileName)), $fileNamePNG);
    			$fullFileName = $fileNamePNG;
    		}
    		$image       = Zend_Pdf_Image::imageWithPath($fullFileName);
    		$document->setLogo($image,$this->getAtribute("pdfLogoWidth"),$this->getAtribute("pdfLogoHeight"));
    	}

    }
    
    private function getUrlLogo(){
    	$fileName =Mage::getStoreConfig('design/email/logo');
    	if ($fileName) {
    		$uploadDir = Mage_Adminhtml_Model_System_Config_Backend_Email_Logo::UPLOAD_DIR;
    		$fullFileName = Mage::getBaseDir('media') . DS . $uploadDir . DS . $fileName;
    		return $fullFileName;
    	}
    }
    
    private function getPdfFontName($name,$bold,$italic){
    	$name = Zend_Pdf_Font::FONT_COURIER;
    	switch ($name){
    		case "Courier": if("false"==$bold && "false"==$italic){
    							$name = Zend_Pdf_Font::FONT_COURIER;
    						}else if("true"==$bold && "false"==$italic){
    							$name = Zend_Pdf_Font::FONT_COURIER_BOLD;
    						}else if("false"==$bold && "true"==$italic){
    							$name = Zend_Pdf_Font::FONT_COURIER_ITALIC;
    						}else if("true"==$bold && "true"==$italic){
    							$name = Zend_Pdf_Font::FONT_COURIER_BOLD_ITALIC;
    						}
    			break;
    		case "Helvetica":if("false"==$bold && "false"==$italic){
    							$name = Zend_Pdf_Font::FONT_HELVETICA;
    						}else if("true"==$bold && "false"==$italic){
    							$name = Zend_Pdf_Font::FONT_HELVETICA_BOLD;
    						}else if("false"==$bold && "true"==$italic){
    							$name = Zend_Pdf_Font::FONT_HELVETICA_ITALIC;
    						}else if("true"==$bold && "true"==$italic){
    							$name = Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC;
    						}
    			break;
    		case "Times":if("false"==$bold && "false"==$italic){
    							$name = Zend_Pdf_Font::FONT_TIMES;
    						}else if("true"==$bold && "false"==$italic){
    							$name = Zend_Pdf_Font::FONT_TIMES_BOLD;
    						}else if("false"==$bold && "true"==$italic){
    							$name = Zend_Pdf_Font::FONT_TIMES_ITALIC;
    						}else if("true"==$bold && "true"==$italic){
    							$name = Zend_Pdf_Font::FONT_TIMES_BOLD_ITALIC;
    						}
    			break;
    	}
    	return Zend_Pdf_Font::fontWithName($name);
    }
    
    private function getPdfAlign($left,$center,$right){
    	if("true"==$right){
    		return Serpini_Sqlreport_Model_PdfTable_Pdf::RIGHT;
    	}else if ("true"==$center){
    		return Serpini_Sqlreport_Model_PdfTable_Pdf::CENTER;
    	}else{
    		return Serpini_Sqlreport_Model_PdfTable_Pdf::LEFT;
    	}
    }
    
    public function getColumnsGroup(){
    	$resultado = array();
    	$valorBruto = $this->getAtribute("columnGroup");
    	$valoresGroup = explode("@@",$valorBruto);
    	foreach ($valoresGroup as $valorGroup){
    		if(""!=$valorGroup){
    			$setGroup = explode("::",$valorGroup);
    			array_push($resultado,$setGroup);
    		}
    		
    	}
    	return $resultado;
    }
    
    public function isChangedSQL(){
    	return $this->sqlCustom!="";
    }
    
}
?>