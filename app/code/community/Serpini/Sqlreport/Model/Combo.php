<?php
class Serpini_Sqlreport_Model_Combo extends Mage_Core_Model_Abstract
{
	
	protected $id = "";
	protected $title;
	protected $type;
	protected $parameter;
	
	protected $values = array();
	protected $atributes = array();
	protected $setsList = array();
	
	
	protected $valueSet = array();
	protected $valueSetDefault = array();
	
	protected $error;
	protected $errorMsg;
	protected $errorSQL;
	
	protected $_locale;
	protected $setupmanager;
	
	protected $logger;
	
	protected function _construct(){
		$this->_init('sqlreport/combo');
		$this->setupmanager= Mage::getModel('sqlreport/setup');
		$this->logger=Mage::getModel('sqlreport/logger');
	}
	
	public function loadCombo($id){
		$utils = Mage::helper('sqlreport/Utils');
		$this->setId($id);
		$this->setValue("");
		$coreResource = Mage::getSingleton('core/resource');
		
		$connection = $coreResource->getConnection('core_read');
		
		// Recuperamos parametros basicos
		$sql = "SELECT entity_id,title,type,parameter FROM ".$utils->gtn('sqlrpt_combo')." WHERE entity_id='".$id."' ";
		$readresult=$connection->query($sql)->fetchAll();
		foreach ($readresult as $fila){
			$this->setType($fila['type']);
			if($this->getType()=="text") $this->setValue("");
			$this->setTitle($fila['title']);
			$this->setParameter($fila['parameter']);
		}
		
		$this->loadAtributes();
		

		if($this->getType() == "select"){
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
					$db->setFetchMode(Zend_Db::FETCH_ASSOC);
					$connection = $db->getConnection();
				}
				$sqlParametrizada=$this->setupmanager->reemplazaParametros($this->getAtribute('sql'));
				$readresult=$connection->query($sqlParametrizada)->fetchAll();
				//$stmt = $db->query($sqlParametrizada);
				//$stmt->setFetchMode(Zend_Db::FETCH_ASSOC);
				//$readresult = $stmt->fetchAll();
				foreach ($readresult as $fila){
					$keys=array_keys($fila);
					$this->values[$fila[$keys[0]]] = $fila[$keys[1]];
				}
				
			}catch (Exception $err){
				Mage::logException($err);
				$this->error=true;
				$this->errorMsg = $err->getMessage();
				$this->errorSQL = $sql;
			}
		}elseif ($this->getType() == "set"){
			$this->setSetsList(explode("|",$this->getAtribute('set')));
			
			foreach ($this->getSetsList() as $set){
				$this->values[$set[0]] = $set[1];
			}
		}
	}
	
	/**
	 * Crea un combo simple de tipo texto
	 * @param unknown $value
	 */
	public function loadComboText($parameter,$value){
		$this->setType("text");
		$this->setValue($value);
		$this->setId("");
		$this->setParameter($parameter);
	}
	
	public function getVaues(){
		return $this->values;
	}
	
	public function getParameter(){
		return $this->parameter;
	}
	
	public function setParameter($parameter){
		$this->parameter=$parameter;
	}
	
	public function getTitle(){
		return $this->title;
	}
	
	public function setTitle($title){
		$this->title=$title;
		
	}
	
	public function setAtribute($atribute,$value){
		$this->atributes[$atribute] = $value;
	}

	
	public function printHTML(){
		$result = "";
		switch ($this->type) {
			case "select": if("checkbox-unique"==$this->getAtribute("selectType")) {
								$result = $this->printHTMLRadio();
							}else if("checkbox-multiple"==$this->getAtribute("selectType")) {
								$result = $this->printHTMLCheck();
							} else {
								
								$result = $this->printHTMLSelect();
							}
						
				break;
			case "date": $result = $this->printHTMLDate();
				break;
			case "text": $result = $this->printHTMLText();
				break;
			case "set": $result = $this->printHTMLSet();
				break;
			default:
				;
			break;
		}
		return $result;
	}
	
	private function printHTMLSelect(){
		$class = "select";
		
		if("select-unique-chosen"==$this->getAtribute("selectType")) {
			$class .= " chosen-select";
		}
		$result = "<select id=\"".$this->getParameter()."\" name=\"".$this->getParameter()."\" title=\"".$this->getDescription()."\" class=\"".$class."\">";
		foreach ($this->getVaues() as $key=>$value){
			
			if($this->isValueSet($key)){
				$result .= "<option value=\"".$key."\" selected='yes'>".$value."</option>";
			} else {
				$result .= "<option value=\"".$key."\" >".$value."</option>";
			}
		}
		$result .= "</select> ";
		$result .= "<img class=\"link\" src=\"".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."serpini_sqlreport/sql-icon.png\" class=\"v-middle\" title=\"View Combo SQL\" alt=\"View Combo SQL\" onclick=\"reportManager.showSQL('sqlCombo".$this->getParameter()."')\" /> ";
		$result .= "<img class=\"link\" src=\"".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."serpini_sqlreport/customization.png\" class=\"v-middle\" onClick=\"reportManager.openAdminCombo('".$this->getId()."')\" >";
		
		return $result;
	}
	
	private function printHTMLDate(){
		$format = $this->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
		$format = $this->setupmanager->getValue('date_mask');
		$value = (count($this->getValueSet())==0?"":$this->valueSet[0]);
		$result = " <input class=\"input-text no-changes required-entry\" type=\"text\" id=\"".$this->getParameter()."\" name=\"".$this->getParameter()."\" value=\"".$value."\" style=\"width:5em\"> ";
		$result .= "<img class=\"link\" src=\"".Mage::getDesign()->getSkinUrl('images/grid-cal.gif')."\" class=\"v-middle\" title=\"".$this->getDescription()."\" alt=\"".$this->getDescription()."\" id=\"".$this->getParameter()."_trig\" />";
		$result .= "<script type=\"text/javascript\">
                Calendar.setup({
                    inputField : '".$this->getParameter()."',
                    ifFormat : '".$format."',
                    button : '".$this->getParameter()."_trig',
                    align : 'Bl',
                    singleClick : true
                });
                </script>";
		return $result;
	}
	
	private function printHTMLSet(){
		$class = "select";
		
		if("select-unique-chosen"==$this->getAtribute("selectType")) {
			$class .= " chosen-select";
		}
		$result = "<select id=\"".$this->getParameter()."\" name=\"".$this->getParameter()."\" title=\"".$this->getDescription()."\" class=\"".$class."\">";
		
		foreach($this->getSetsList() as $set){

			if($this->isValueSet($set[0])){
				$result .= "<option value=\"".$set[0]."\" selected='yes'>".$set[1]."</option>";
			} else {
				$result .= "<option value=\"".$set[0]."\" >".$set[1]."</option>";
			}
		}
		
		$result .= "</select> ";
		$result .= "<img class=\"link\" src=\"".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."serpini_sqlreport/customization.png\" class=\"v-middle\"  onClick=\"reportManager.openAdminCombo('".$this->getId()."')\"  >";
		
		return $result;
	}
	
	private function printHTMLText(){
		$result = " <input class=\"input-text no-changes required-entry\" type=\"text\" id=\"".$this->getParameter()."\" name=\"".$this->getParameter()."\" value=\"".$this->valueSet."\" > ";
		$result .= "<img class=\"link\" src=\"".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."serpini_sqlreport/customization.png\" class=\"v-middle\"  onClick=\"reportManager.openAdminCombo('".$this->getId()."')\" >";
		return $result;
	}
	
	private function printHTMLRadio(){
		
		$result="";
		$primero = true;
		foreach ($this->getVaues() as $key=>$value){
			$result.='<input type="radio" name="'.$this->getParameter().'" value="'.$key.'" id="'.$key.'"';
			if(($this->isValueSet($key)) || ($primero && count($this->getValueSet())==0)){
				$result .= " checked";
			} 
			$primero = false;
			$result.='/><label for="'.$key.'"> '.$value.'</label><br />';
		}
		return $result;
	}
	
	private function printHTMLCheck(){
		$result="";
		foreach ($this->getVaues() as $key=>$value){
			$result.='<input type="checkbox" name="'.$this->getParameter().'" value="'.$key.'" id="'.$key.'"';
			if($this->isValueSet($key)){
				$result .= " checked";
			} 
			
			$result.='/><label for="'.$key.'"> '.$value.'</label><br />';
		}
		return $result;
	}
	
	public function setValue($value){
		if($this->getType()=="text"){
			$this->valueSet=$value;
		}else{
			if(""!=$value) array_push($this->valueSet, $value);
		}
	}
	
	public function setValueDefault($value){
		if($this->getType()=="text"){
			$this->valueSetDefault=$value;
		}else{
			if(""!=$value){
				array_push($this->valueSetDefault, $value);				
			}
		}
	}
	
	public function getTitleOfValue($value){
		if($this->getType()=="text"||$this->getType()=="date"){
			return $value;
		}else{
			return $this->values[$value];
		}
	}

	public function getValueSet(){
		return $this->valueSet;
	}
	
	public function getValueSetString(){
		$texto = "";
		if(is_array($this->getValueSet())){
			foreach($this->getValueSet() as $valueSet){
				$texto .=", ".(string)$valueSet;
			}
			$texto = substr($texto, 2);
		}else{
			$texto = $this->getValueSet();
		}
		return $texto;
	}
	
	public function getValueSetDefault(){
		return $this->valueSetDefault;
	}
	
	public function getValueDefault(){
		$values = explode("$", $this->valueSetDefault[0]);
		return $values[0];
	}
	
	public function getTitleDefault(){
		$values = explode("$", $this->valueSetDefault[0]);
		return $values[1];
	}
	
	public function isError(){
		return $this->error;
	}
	
	public function getErrorMsg(){
		return $this->errorMsg;
	}
	
	public function getErrorSql(){
		return $this->errorSQL;
	}
	
	public function getId(){
		return $this->id;
	}
	
	public function setId($id){
		$this->id=$id;
	
	}
	
	public function getType(){
		return $this->type;
	}
	
	public function setType($type){
		$this->type=$type;
	}
	
	public function getLocale()
	{
		if (!$this->_locale) {
			$this->_locale = Mage::app()->getLocale();
		}
		return $this->_locale;
	}
	
	public function getSetsList(){
		return $this->setsList;
	}
	
	public function isLoaded(){
		return $this->id!="";
	}
	
	public function getAtribute($atribute){
		if(array_key_exists($atribute,$this->atributes )){
			return $this->atributes[$atribute];
		}else{
			return '';
		}
	}
	
	public function setSetsList($setsList){
		$this->setsList = array();
		foreach($setsList as $set){
			
			if($set<>""){
				$setDataArray = explode(";",$set);
				$value=$setDataArray[0];
				$label=$setDataArray[1];
				$this->setsList[]=array($value,$label);
			}
		}
	}
	
	public function toJsonResponse(){
		
		return json_encode($this->toArrayex());
	}
	
	public function toArrayex(){
		$atributes2json = array();
		foreach($this->atributes as $key => $value){
			if(""!=$value && null != $value){
				$atributes2json[$key] = $value;
			}	
		}
		$combosValueDef = $this->getValueSetDefault();
		$value = (count($combosValueDef)==0?"":$combosValueDef[0]);
		$salida = array("object_type" => "combo",
				"combo_id" => $this->getId(),
				"title" => $this->getTitle(),
				"parameter" => $this->getParameter(),
				"type" => $this->getType(),
				"atributes" => $atributes2json,
				"valueDefault" =>$value
		);
		return $salida;
	}
	
	public function saveCombo(){
		try{
			$utils = Mage::helper('sqlreport/Utils');
			$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
			$dataUpdate = array(
					'title' => $this->getTitle(),
					'type'  => $this->getType(),
					'parameter'    => $this->getParameter()
			);
		
			$whereMain[] = "entity_id = '".$this->getId()."'";
			$where[] = "combo_id = '".$this->getId()."'";
			$connection->beginTransaction();
		
			$connection->update($utils->gtn('sqlrpt_combo'), $dataUpdate, $whereMain);
			$connection->delete($utils->gtn('sqlrpt_combo_int'), $where);
			$connection->delete($utils->gtn('sqlrpt_combo_text'), $where);
			$connection->delete($utils->gtn('sqlrpt_combo_varchar'), $where);

			// ATTRIBUTES
			$this->saveAttributes($connection);

			$connection->commit();
			
			$this->logger->logCombo("save",$this);
			
			$data[0] = array("type" => "success-msg",
					"msg" => Mage::helper('catalog')->__('Filter saved'));
			return json_encode($data);
		
		}catch (Exception  $err){
			$data[0] = array("type" => "error-msg",
					"msg" => $err->getMessage());
			return json_encode($data);
		}
	}
	
	public function saveAttributes($connection){
		$utils = Mage::helper('sqlreport/Utils');
		$connectionRead = Mage::getSingleton('core/resource')->getConnection('core_read');
		 
		$select = $connectionRead->select()
				->from(array('a' => $utils->gtn('sqlrpt_combo_type')),
	  				   array('a.type_id', 'a.type_code','a.type'));
		 
		$readresult=$connectionRead->fetchAll($select);
		foreach ($readresult as $fila){
			if(""!=$this->getAtribute($fila['type_code'])){
				$dataInsert = array(
						'type_id' => $fila['type_id'],
						'combo_id' => $this->getId() ,
						'value' => $this->getAtribute($fila['type_code']));
				$connection->insert($utils->gtn('sqlrpt_combo_'.$fila['type']),$dataInsert);
			}
		}
		 
	}
	
	private function loadAtributes(){
		$utils = Mage::helper('sqlreport/Utils');
		try{
			$coreResource = Mage::getSingleton('core/resource');
			$connection = $coreResource->getConnection('core_read');
			$select = $connection->select()
					->from(array('a' => $utils->gtn('sqlrpt_combo_type')),
						array('a.type_code', 'a.type'))
					->joinLeft(array('b' => $utils->gtn('sqlrpt_combo_int')),
						'a.type_id = b.type_id'.
						' AND b.combo_id = "'.$this->getId().'"',
						array('value_int' => 'b.value'))
					->joinLeft(array('c' => $utils->gtn('sqlrpt_combo_text')),
						'a.type_id = c.type_id'.
						' AND c.combo_id = "'.$this->getId().'"',
						array('value_text' => 'c.value'))
					->joinLeft(array('d' => $utils->gtn('sqlrpt_combo_varchar')),
						'a.type_id = d.type_id'.
						' AND d.combo_id = "'.$this->getId().'"',
						array('value_varchar' => 'd.value'));

			$readresult=$connection->fetchAll($select);
			foreach ($readresult as $fila){
				switch ($fila['type']){
					case 'int': $this->atributes[$fila['type_code']] = $fila['value_int'];
					break;
					case 'text': $this->atributes[$fila['type_code']] = $fila['value_text'];
					break;
					case 'varchar': $this->atributes[$fila['type_code']] = $fila['value_varchar'];
					break;
				}
			}
		}catch (Exception  $err){
			$this->error=true;
			$this->errorMsg = Mage::helper('catalog')->__('Error loading atributes')." :".$err->getMessage();
			$this->errorSQL = $sql;
		}
	}
	
	public function addMeAsNew(){
		try{
			$utils = Mage::helper('sqlreport/Utils');
			$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
			$connection->beginTransaction();
			
			$fields  = array(
					'title' => $this->getTitle(),
					'type' => $this->getType(),
					'parameter' => $this->getParameter()
			);
			$connection->insert($utils->gtn('sqlrpt_combo'), $fields);
			
			$id = $utils->getLastId($connection);
			$this->setId($id);
			
			// ATTRIBUTES
			$this->saveAttributes($connection);
			
			$connection->commit();
			
			$this->logger->logCombo("add",$this);
			
			return true;
		}catch (Exception  $err){
			return $err->getMessage() ;
		}
	}
	
	public function delete(){
		$utils = Mage::helper('sqlreport/Utils');
		try{
			$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		
			// Comprobamos que no haya ning�n informe asociado a dicho combo
			$select = $connection->select()
							->from($utils->gtn('sqlrpt_report_combo'), array('numero' => 'COUNT(1)'))
							->where('combo_id = ?',$this->getId());
			$fechList=$connection->fetchRow($select);
			$esta=$fechList['numero'];
			if($esta>0){
				$data[0] = array("type" => "error-msg",
						"msg" => Mage::helper('catalog')->__('The filter has associated %s reports',$esta));
			}else{
				$where = array($connection->quoteInto('combo_id=?', $this->getId()));
				$whereMain = array($connection->quoteInto('entity_id=?', $this->getId()));
				$connection->beginTransaction();
				$connection->delete($utils->gtn('sqlrpt_combo_text'), $where);
				$connection->delete($utils->gtn('sqlrpt_combo_int'), $where);
				$connection->delete($utils->gtn('sqlrpt_combo_varchar'), $where);
				$connection->delete($utils->gtn('sqlrpt_combo'), $whereMain);
				$connection->commit();
				$data[0] = array("type" => "success-msg",
						"msg" => Mage::helper('catalog')->__('Filter deleted'));
			}
			$this->logger->logCombo("delete",$this);
			
			return json_encode($data);
		
		}catch (Exception  $err){
			$data[0] = array("type" => "error-msg",
					"msg" => $err->getMessage());
			return json_encode($data);
		}
		
	}
	
	private function isValueSet($value){
		foreach($this->getValueSet() as $valueSet){
			if((string)$valueSet==(string)$value) return true;
		}
		return false;
	}
}