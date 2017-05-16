<?php
class Serpini_Sqlreport_Helper_GroupManager extends Mage_Core_Helper_Abstract {
	
	protected $groupList = null;
	
	public function getGroupList(){
		$utils = Mage::helper('sqlreport/Utils');
		if($this->groupList == null){
			$groupList = array();
			$coreResource = Mage::getSingleton('core/resource');
			$connection = $coreResource->getConnection('core_read');
			$select = $connection->select()
								->from($utils->gtn('sqlrpt_group'), array('entity_id','description','orden'))
								->order(array('orden ASC'));
			try{
				$readresult=$connection->fetchAll($select);
				foreach ($readresult as $fila){
					$group_id = $fila['entity_id'];
					$description = $fila['description'];
					$orden = $fila['orden'];
					$groupList[]=array($group_id,$description,$orden);
				}
				$this->groupList= $groupList;
			}catch (Exception  $err){
				echo $err->getMessage();
			}
			
		}
		return $this->groupList;
		
	}
	
	public function updateGroup($groupId,$description,$orden){
		$group = Mage::getModel('sqlreport/group');
		$group->loadMe($groupId);
		$group->setDescription($description);
		$group->setOrden($orden);
		return $group->saveGroup();
	}
	
	public function saveGroup($group_id,$description,$orden){
		try{
			$utils = Mage::helper('sqlreport/Utils');
			$coreResource = Mage::getSingleton('core/resource');
			$connection = $coreResource->getConnection('core_write');
			if($this->groupExists($description,$this->getGroupList())){
				$fields = array('description' => $description,
						'orden'=>$orden);
				$where = $connection->quoteInto('entity_id =?', $group_id);
				$connection->update($utils->gtn('sqlrpt_group'), $fields, $where);
			}else{
				$fields = array(
						'description' => $description,
						'orden'=>$orden);
				$connection->insert($utils->gtn('sqlrpt_group'),$fields);
				$id = $utils->getLastId($connection);
				$this->groupList[]=array($group_id,$description,$orden);
			}
			$connection->commit();
			return true;
		}catch (Exception  $err){
			return $err->getMessage();
		}
	}
	
	public function createGroup($description,$orden){
		if(!$this->groupExists($description,$this->getGroupList())){
			$group = Mage::getModel('sqlreport/group');
			$group->loadMe2($description,$orden);
			$estado = $group->addMeAsNew();
			if($estado){
				$this->groupList[]=array($group->getId(),$description,$orden);
				return $group;
			}else{
				return $estado;
			}
		}else{
			return Mage::helper('catalog')->__('The group already exists');
		}
	}
	
	public function saveGroupByList($lista){
		$utils = Mage::helper('sqlreport/Utils');
		$groupListModified = array();
		$groupsList = explode("|",$lista);
		$coreResource = Mage::getSingleton('core/resource');
		$connection = $coreResource->getConnection('core_write');
		try{
			$connection->beginTransaction();
			foreach($groupsList as $groupData){
				
				if($groupData!=""){
					$groupDataArray = explode(";",$groupData);
					$code = $groupDataArray[0];
					$description = $groupDataArray[1];
					$orden = $groupDataArray[2];
					$this->saveGroup($code,$description,$orden);
					$groupListModified[]=array($code,$description);
				}
			}
			
			// Eliminamos lo que no hayan aparecido
			foreach($this->getGroupList() as $grupo){
				if(!$this->groupExists($grupo[1], $groupListModified)){
					if($this->getReportsNumber($grupo[0])>0){
						$connection->rollBack();
						$data[0] = array("type" => "error-msg",
										 "msg" => Mage::helper('catalog')->__('The %s group can not be deleted because it is associated reports',$grupo[0]));
						return json_encode($data);
					}else {
						$where = $connection->quoteInto('entity_id =?', $grupo[0]);
						$connection->delete($utils->gtn('sqlrpt_group'), $where);
					}
				}
			}
			
			$connection->commit();
	
			$data[0] = array("type" => "success-msg",
					"msg" => Mage::helper('catalog')->__('Groups successfully saved'));
			return json_encode($data);
		}catch (Exception  $err){
			$connection->rollBack();
			$data[0] = array("type" => "error-msg",
							 "msg" => $err->getMessage());
			return json_encode($data);
		}
	}
	
	protected function groupExists($description,$groupLista){
		foreach($groupLista as $grupo){
			if($grupo[1]==$description){
				return true;
			}
		}
		return false;
	}
	
	public function getGroupIdByDescription($group_description){
		$groupLista=$this->getGroupList();
		foreach($groupLista as $grupo){
			if($grupo[1]==$group_description){
				return $grupo[0];
			}
		}
		return -1;
	}
	
	public function getGroupByDescription($group_description){
		$groupLista=$this->getGroupList();
		foreach($groupLista as $grupo){
			if($grupo[1]==$group_description){
				$group = Mage::getModel('sqlreport/group');
				$group->loadMe2($grupo[1],$grupo[2]);
				$group->setId($grupo[0]);
				return $group;
			}
		}
		return false;
	}
	
	public function getReportsNumber($group_id){
		$coreResource = Mage::getSingleton('core/resource');
		$utils = Mage::helper('sqlreport/Utils');
		$connection = $coreResource->getConnection('core_write');
		// SELECT COUNT(1) AS numero FROM sqlrpt_report where group_id='$group_id'
		$select = $connection->select()
					->from($utils->gtn('sqlrpt_report'), array('numero' => 'COUNT(1)'))
					->where('group_id = ?',$group_id);
		
		$readresult=$connection->fetchRow($select);
		return $readresult['numero'];
	}
	
	public function deleteGroup($groupId){
		$group = Mage::getModel('sqlreport/group');
		$group->loadMe($groupId);
		return $group->delete();
	}
	
	public function getLasOrder(){
		$ultimo = 0;
		$groupLista=$this->getGroupList();
		foreach($groupLista as $grupo){
			if($grupo[2]>$ultimo){
				$ultimo=$grupo[2];
			}
		}
		return $ultimo;
	}
	
}
?>