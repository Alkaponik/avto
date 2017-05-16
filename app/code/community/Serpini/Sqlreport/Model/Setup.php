<?php
class Serpini_Sqlreport_Model_Setup extends Mage_Core_Model_Abstract
{
	protected $value = array();
	
	protected function _construct(){
		$this->_init('sqlreport/setup');
		$this->loadAdmin();
	}
	
	public function loadAdmin(){
		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		try{
			$select = $connection->select()
					->from($this->gtn('sqlrpt_setup'),array('name', 'value'));
			$readresult=$connection->fetchAll($select);
			foreach ($readresult as $fila){
				$this->value[$fila['name']]=$fila['value'];
			}
		}catch (Exception  $err){
			$resultOut[0] = array("type" => "error-msg",
					"msg" => $err->getMessage());
			return json_encode($resultOut);
		}
	}
	
	public function getValue($key){
		if (array_key_exists($key, $this->value)) {
			return $this->value[$key];
		} else{
			return "";
		}
	}
	
	public function setValue($key,$value){
		$this->value[$key]=$value;
	}
	
	public function saveSetup(){
		try{
			$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
			$connection->beginTransaction();
			//limpiamos la tabla para hacer inserts
			$connection->delete($this->gtn('sqlrpt_setup'), array());
			
			foreach($this->value as $key=>$value){
				//$data = array('value' => $value);
					
				//$where[0] = "name = '".$key."'";
				$dataInsert = array('name' => $key,'value' => $value);
    			$connection->insert($this->gtn('sqlrpt_setup'),$dataInsert);
    				
				//$connection->update($this->gtn('sqlrpt_setup'), $data, $where);
				
			}
			
			$connection->commit();
			$resultOut[0] = array("type" => "success-msg",
					"msg" => "Admin parameters saved");
			return json_encode($resultOut);
			
		}catch (Exception  $err){
			
			$resultOut[0] = array("type" => "error-msg",
					"msg" => $err->getMessage());
			return json_encode($resultOut);
		}
	}
	
	public function reemplazaParametros($sql){
		// prefijo tabla
		$salida = str_replace($this->getValue('prefix_table'),Mage::getConfig()->getTablePrefix() ,$sql);
		return $salida;
	}
	
	public function gtn($tableName){
		return Mage::getSingleton('core/resource')->getTableName($tableName);
	}
	
    //Pretty text
    public function pt($texto){
    	//Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/edit', array('id' => $_pullProduct->getId()))
    	// LINKS
    	$ruta = "adminhtml/catalog_product/edit";
    	$parametro = "id";
    	$valor = "1";
    	
    	$textoFinal = Mage::helper('adminhtml')->getUrl($ruta, array($parametro => $valor));
    	
    	// EMAIL
    	$regex = '/(\S+@\S+\.\S+)/';
	    $replace = '<a href="mailto:$1">$1</a>';
	
	    return preg_replace($regex, $replace, $texto);
    }
}