<?php
class Serpini_Sqlreport_Model_Permissionrole extends Mage_Core_Model_Abstract
{
	private $role;
	// 0 custom
	// 1 ALL
	private $permission = array();
	private $utils;
	private $PERMISSION_DEFAULT=false;
	
	protected function _construct(){	
        $this->_init('sqlreport/permissionrole');
        $this->role = "";
    }
    
    public function loadMe($role){
    	$this->utils = Mage::helper('sqlreport/Utils');
    	$this->role=$role;
    	
    	$coreResource = Mage::getSingleton('core/resource');
		$connection = $coreResource->getConnection('core_read');
		$select = $connection->select()
					->from($this->utils->gtn('sqlrpt_report_role'), array('report_id','read','edit'))
					->where('role = ?',$this->getRole())
					->order('report_id');
    	$readresult=$connection->fetchAll($select);
    	
    	$this->permission = $readresult;
    }
    
    public function getRole(){
    	return $this->role;
    }
    
    public function getAccess(){
    	if(sizeof($this->readresult)>0){
    		return 0;
    	}else{
    		return 1;
    	}
    }
    
    public function getPermission(){
    	return $this->permission;
    }
    
	public function toJsonResponse(){
		return json_encode($this->getPermission());
	}
	
	public function getRoleIdUser(){
		$user = Mage::getSingleton('admin/session');
		if(""==$user->getUser()){
			return 1;
		}else{
			$username = $user->getUser()->getUsername();
			$role_data = Mage::getModel('admin/user')->getCollection()->addFieldToFilter('username',$username)->getFirstItem()->getRole()->getData();
			return $role_data['role_id'];
		}
	}
	
	public function changePermission($permissions){
		$this->permission = array();
		
		foreach($permissions as $permission){
			// Sólo almacenamos si alguno de los dos es distinto al permiso por defecto
			if($permission->read != $this->PERMISSION_DEFAULT || $permission->edit!=$this->PERMISSION_DEFAULT){
				$this->permission[]=array(
									    "report_id" => $permission->report_id,
									    "read" => $permission->read,
										"edit" => $permission->edit
									);
			}
		}
	}
	
	public function save(){
		try{
			$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
	    	$connection->beginTransaction();
	    	$where = array($connection->quoteInto('role=?', $this->getRole()));
	    	$connection->delete($this->utils->gtn('sqlrpt_report_role'), $where);
	    	
	    	foreach ($this->getPermission() as $permission){
	    		$dataInsert = array(
	    			'report_id' => $permission['report_id'],
	    			'role' => $this->getRole(),
	    			'read' => $permission['read'],
	    			'edit' => $permission['edit']
	    		);
    			$connection->insert($this->utils->gtn('sqlrpt_report_role'),$dataInsert);
	    	}
	    	
	    	$connection->commit();
	    	return true;
		}catch (Exception  $err){
	    	return $err->getMessage();
	   	}
	}
	
	public function userHasPermissionViewReport($report_id){
		$this->loadMe($this->getRoleIdUser());
		$default = true;
		foreach ($this->getPermission() as $permission){
			$default = $this->PERMISSION_DEFAULT;
			if($permission['report_id']==$report_id){
				return ($permission['read']==0?false:true);
			}
		}
		return $default;
	}
	
	public function userHasPermissionEditReport($report_id){
		$this->loadMe($this->getRoleIdUser());
		$default = true;
		foreach ($this->getPermission() as $permission){
			$default = $this->PERMISSION_DEFAULT;
			if($permission['report_id']==$report_id){
				return ($permission['edit']==0?false:true);
			}
		}
		return $default;
	}
	
	public function userHasPermissionCreateReport(){
		// TODO
		return true;
	}
	
	public function userHasPermissionEditAdmin(){
		// TODO
		return true;
	}
	
	public function userHasPermissionEditGroups(){
		// TODO
		return true;
	}
	
	public function userHasPermissionViewFilter($filter_id){
		// TODO
		return true;
	}
	
	public function userHasPermissionEditFilter($filter_id){
		//TODO
		return true;
	}
	
	public function userHasPermissionCreateFilter(){
		// TODO
		return true;
	}
	
	public function userHasPermissionEditLink(){
		// TODO
		return true;
	}
	
	public function userHasPermissionCreateLink(){
		//TODO
		return true;
	}
	
	
}