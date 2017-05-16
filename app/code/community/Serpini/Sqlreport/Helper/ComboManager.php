<?php
class Serpini_Sqlreport_Helper_ComboManager extends Mage_Core_Helper_Abstract {
	
	protected $comboList = null;
	
	public function getComboList(){
		if($this->comboList == null){
			$comboList = array();
			$coreResource = Mage::getSingleton('core/resource');
			$connection = $coreResource->getConnection('core_read');
			$select = $connection->select()
						->from($this->gtn('sqlrpt_combo'), array('entity_id','title','type','parameter'))
						->order('entity_id');
			try{
				$readresult=$connection->fetchAll($select);
				foreach ($readresult as $fila){
					$combo_id = $fila['entity_id'];
					$title = $fila['title'];
					$type = $fila['type'];
					$parameter = $fila['parameter'];
					$comboList[]=array($combo_id,$title,$type,$parameter);
				}
				$this->comboList= $comboList;
			}catch (Exception  $err){
				echo $err->getMessage();
			}
		}
		return $this->comboList;
	}
	
	public function resetComboList(){
		$this->comboList = null;
	}
	
	public function getComboListValue($comboList){
		$salida = array();
		$comboListArray = explode("|", $comboList);
		foreach ($comboListArray as $combo_id ){
			$combo = Mage::getModel('sqlreport/combo');
			$combo->loadCombo($combo_id);
			$salida = array_merge($salida,array($combo->getId() => $combo->getVaues()));
		}
		return $salida;
	}
	
	public function loadCombo($combo_id){
		$combo = Mage::getModel('sqlreport/combo');
		$combo->loadCombo($combo_id);
		return $combo->toJsonResponse();
	}
	
	public function saveCombo($combo_id,$title,$parameter,$tipo,$sql,$setValues,$selectType){
		if($this->comboExists($combo_id,$parameter)){
			$combo = Mage::getModel('sqlreport/combo');
			$combo->loadCombo($combo_id);
			$combo->setTitle($title);
			$combo->setParameter($parameter);
			$combo->setType($tipo);
			$combo->setAtribute('sql',$sql);
			$combo->setAtribute('set',$setValues);
			$combo->setAtribute('selectType',$selectType);

			return $combo->saveCombo();
		}else{
			$data[0] = array("type" => "error-msg",
					"msg" => Mage::helper('catalog')->__('Filter not exists'));
			return json_encode($data);
		}
	}
	
	public function addNewCombo($title,$parameter,$tipo,$atributes){
		$combo = Mage::getModel('sqlreport/combo');
		$combo->setTitle($title);
		$combo->setParameter($parameter);
		$combo->setType($tipo);
		foreach ($atributes as $key => $value){
			$combo->setAtribute($key,$value);
		}
		
		$salida = $combo->addMeAsNew();
		if($salida===true){
			$this->comboList[]=array($combo->getId(),$title,$tipo,$atributes);
			return $combo;
		}
		return $salida;
	}
	
	public function addComboByList($combo_id,$title,$parameter,$tipo,$sql,$setValues,$selectType){
		if($this->comboExists("",$parameter)){
			$data[0] = array("type" => "error-msg",
					"msg" => Mage::helper('catalog')->__('Parameter already exists'));
			return json_encode($data);
		}else{
			$combo = Mage::getModel('sqlreport/combo');
			$combo->setTitle($title);
			$combo->setParameter($parameter);
			$combo->setType($tipo);
			$combo->setAtribute("sql",$sql);
			$combo->setAtribute("set",$setValues);
			$combo->setAtribute('selectType',$selectType);
			
			$salida = $combo->addMeAsNew();
			if($salida){
				$data[0] = array("type" => "success-msg",
						"msg" => Mage::helper('catalog')->__('Filter added'),
						"id"  => $combo->getId()
				);
				return json_encode($data);
			}else{
				$data[0] = array("type" => "error-msg",
						"msg" => $salida);
				return json_encode($data);
			}
			
		}
	}
	
	public function comboExists($comboId,$parameter){
		$comboLista=$this->getComboList();
		foreach($comboLista as $combo){
			if(""==$comboId && $combo[3]==$parameter){
				return $combo[0];
			}else if(""!=$comboId && $combo[0]==$comboId){
				return $combo[0];
			}
		}
		return false;
	}
	
	public function deleteCombo($combo_id){
		$combo = Mage::getModel('sqlreport/combo');
    	$combo->loadCombo($combo_id);
    	return $combo->delete();
	}
	
	public function gtn($tableName){
		return Mage::getSingleton('core/resource')->getTableName($tableName);
	}
}

?>