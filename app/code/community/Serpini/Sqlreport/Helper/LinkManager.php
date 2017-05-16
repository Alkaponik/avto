<?php
class Serpini_Sqlreport_Helper_LinkManager extends Mage_Core_Helper_Abstract {
	
	protected $linkListTR = null;
	protected $linkListTD = null;
	
	public function getLinkList($type){
		if("TR" == $type){
			if($this->linkListTR == null){
				$this->linkListTR= $this->getLinkListDB($type);
			}
			return $this->linkListTR;
		} else if("TD" == $type){
			if($this->linkListTD == null){
				$this->linkListTD= $this->getLinkListDB($type);
			}
			return $this->linkListTD;
		} else {
			return array();
		}
	}
	
	public function resetLinkList(){
		$this->linkListTR = null;
		$this->linkListTD = null;
	}
	
	private function getLinkListDB($type){
		$utils = Mage::helper('sqlreport/Utils');
		$linkList = array();
		$coreResource = Mage::getSingleton('core/resource');
		$connection = $coreResource->getConnection('core_read');
		$select = $connection->select()
					->from($utils->gtn('sqlrpt_link'), array('entity_id','description','url','type'))
					->where('type = ?',$type)
					->order('entity_id');
		try{
			$readresult=$connection->fetchAll($select);
			foreach ($readresult as $fila){
				$linkList[]=array($fila['entity_id'],$fila['description'],$fila['url'],$fila['type']);
			}
			return $linkList;
		}catch (Exception  $err){
			echo $err->getMessage();
		}
	}
	
	public function updateLink($linkId,$description,$url,$type){
		if($this->linkExists($description,$url,$type)){
			$link = Mage::getModel('sqlreport/link');
			$link->loadMe2($description,$url,$type);
			$link->setId($linkId);
			return $link->saveLink();
		}else{
			return Mage::helper('catalog')->__('link not exists');
		}
	}
	
	public function createLink($description,$url,$type){
		if(!$this->linkExists($description,$url,$type)){
			$link = Mage::getModel('sqlreport/link');
			$link->loadMe2($description,$url,$type);
			$estado = $link->addMeAsNew();
			if($estado){
				return $link;
			}else{
				return $estado;
			}
		}else{
			return Mage::helper('catalog')->__('the link already exists');
		}
	}
	
	public function linkExists($description,$url,$type){
		$linkLista=$this->getLinkList("TR");
		foreach($linkLista as $link){
			if($link[1]==$description && $link[2]==$url && $link[3]==$type){
				return true;
			}
		}
		
		$linkLista=$this->getLinkList("TD");
		foreach($linkLista as $link){
			if($link[1]==$description && $link[2]==$url && $link[3]==$type){
				return true;
			}
		}
		return false;
	}
	
	public function getLinkByData($description,$url,$type){
		$linkLista=$this->getLinkList("TR");
		foreach($linkLista as $link){
			if($link[1]==$description && $link[2]==$url && $link[3]==$type){
				$linkObj = Mage::getModel('sqlreport/link');
				$linkObj->loadMe($link[0]);
				return $linkObj;
			}
		}
		
		$linkLista=$this->getLinkList("TD");
		foreach($linkLista as $link){
			if($link[1]==$description && $link[2]==$url && $link[3]==$type){
				$linkObj = Mage::getModel('sqlreport/link');
				$linkObj->loadMe($link[0]);
				return $linkObj;
			}
		}
		return false;
	}
	
	public function deleteLink($linkId){
		$link = Mage::getModel('sqlreport/link');
    	$link->loadMe($linkId);
    	return $link->delete();
		
	}
}