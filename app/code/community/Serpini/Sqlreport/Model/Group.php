<?php
class Serpini_Sqlreport_Model_Group extends Mage_Core_Model_Abstract
{
	protected $id;
	protected $description;
	protected $orden;
	protected $lastOrder;
	
	protected function _construct(){
		$this->_init('sqlreport/group');
		
	}
	
	public function loadMe($id){
		$utils = Mage::helper('sqlreport/Utils');
		$this->id=$id;
		$coreResource = Mage::getSingleton('core/resource');
    	$connection = $coreResource->getConnection('core_read');
		$select = $connection->select()
	    			->from(array('a' => $utils->gtn('sqlrpt_group')), 
	    				   array('a.entity_id','a.description','a.orden'))
	    			->where('entity_id = ?',$this->getId());
	    $readresult=$connection->fetchAll($select);
		foreach ($readresult as $fila){
    		$this->setDescription($fila['description']);
    		$this->setOrden($fila['orden']);
	    }
	    	
	}
	
	public function loadMe2($description,$orden){
		$this->description=$description;
		$this->setOrden($orden);
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
	
	public function getOrden(){
		return $this->orden;
	}
	
	private function getLastOrden(){
		return $this->lastOrder;
	}
	
	public function setOrden($orden){
		if(""==$this->lastOrder){
			$this->orden=$orden;
			$this->lastOrder=$orden;
		}else{
			$this->orden=$orden;
		}
	}
	
	public function addMeAsNew(){
		try{
			$utils = Mage::helper('sqlreport/Utils');
			$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
			$connection->beginTransaction();
			$fields  = array(
					'description' => $this->getDescription(),
					'orden' => $this->getOrden()
			);
				
			$connection->insert($utils->gtn('sqlrpt_group'), $fields);
				
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
	
			// Comprobamos que no haya ningún informe asociado a dicho grupo
			$select = $connection->select()
				->from($utils->gtn('sqlrpt_report'), array('numero' => 'COUNT(1)'))
				->where('group_id = ?',$this->getId());
			$fechList=$connection->fetchRow($select);
			$esta=$fechList['numero'];
			if($esta>0){
				return Mage::helper('catalog')->__('The group has associated %s reports',$esta);
			}else{
				$where = array($connection->quoteInto('entity_id=?', $this->getId()));
				$connection->beginTransaction();
				$connection->delete($utils->gtn('sqlrpt_group'), $where);
				
				$connection->query("UPDATE ".$utils->gtn('sqlrpt_group').' SET orden=orden-1 WHERE orden>'.$this->getOrden());

				$connection->commit();
				return true;
			}
	
		}catch (Exception  $err){
			return $err->getMessage();
		}
	}
	
	public function saveGroup(){
		try{
			$utils = Mage::helper('sqlreport/Utils');
			$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
			$connection->beginTransaction();
			// Comprobamos si hemos cambiado el orden
			if($this->getLastOrden()!=$this->getOrden()){
				$dataUpdate = array('orden'  => $this->getLastOrden());
				$where = array("orden = ".$this->getOrden());
				$update = $connection->update($utils->gtn('sqlrpt_group'), $dataUpdate, $where);
			}
			
			$dataUpdate = array(
					'description' => $this->getDescription(),
					'orden'  => $this->getOrden()
			);
	
			$where = array("entity_id = '".$this->getId()."'");
			
			$connection->update($utils->gtn('sqlrpt_group'), $dataUpdate, $where);
	
			$connection->commit();
			return true;
		}catch (Exception  $err){
			return $err->getMessage();
		}
	}
	
}

