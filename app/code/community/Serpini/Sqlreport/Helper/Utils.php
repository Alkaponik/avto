<?php
class Serpini_Sqlreport_Helper_Utils extends Mage_Core_Helper_Abstract
{
	
	protected $formatDate = array("M d,Y","m/d/y","Y-m-d H:i:s","d/m/Y");
	protected $dateFormatConvert = array("d"=>"%d",
										 "j"=>"%e",
										 "D"=>"%a",
										 "z"=>"%j",
										 "F"=>"%M",
										 "M"=>"%b",
										 "m"=>"%m",
										 "n"=>"%c",
										 "Y"=>"%Y",
										 "y"=>"%y",
										 "a"=>"%p",
										 "g"=>"%l",
										 "h"=>"%h",
										 "G"=>"%k",
										 "H"=>"%H",
										 "i"=>"%i",
										 "s"=>"%s",
										 "u"=>"%f"
	);
	
	public function getFilesDirectoryByExtension($path,$extension){
    	$lista = array();
    	try{
    		$directorio=opendir($path);
	    	while ($archivo = readdir($directorio)){
	    		$ext = pathinfo($archivo, PATHINFO_EXTENSION);
	    		if($ext == $extension){
	    			$lista[] = $archivo;
	    		}
	    	}
    	}catch (Exception $e) {}
    	closedir($directorio); 
    	
		return $lista;
    }
    
	public function gtn($tableName){
    	return Mage::getSingleton('core/resource')->getTableName($tableName);
    }
    
    /* Devuelve el tipo de dato pasado
     *  return: 0: Null
     *  		1: String
     *  		2: Number
     *  		3: Boolean
     *  		4: Date
     */
    public function getTypeData($data){
    	if(is_null($data)){
    		return 0;
    	}else if(""==$data){
    		return 0;
    	}else if($this->is_date($data)){
    		return "4".$this->getDateFormat($data);
    	}else if("true"==strtolower ($data)||"false"==strtolower ($data)){
    		return 3;
    	}else if(is_numeric($data)){
    		return 2;
    	}else{
    		return 1;
    	}
    	
    }
    
    private function is_date($data){
    	$setupmanager= Mage::getModel('sqlreport/setup');
    	$format = $this->getDateFormat($data);
    	$ymd = DateTime::createFromFormat($format, $data);
    	
    	if(false==$ymd){
    		return false;
    	}else{
    		return true;
    	}
    }
    
    /**
     * Recupera de un string, el formato de un date
     * @param unknown $data
     * @return string
     */
    public function getDateFormat($data){
    	foreach ($this->formatDate as $format){
    		$ymd = DateTime::createFromFormat($format, $data);
    		if(false!=$ymd) return $format;
    	}
    	return "";
    }
    
    /**
     * Convierte un formato de date de PHP a mysql
     * @param unknown $dateFormatPHP
     * @return string
     */
    public function dateFormat2sql($dateFormatPHP){
    	$salida = $dateFormatPHP;
    	foreach($this->dateFormatConvert as $php=>$mysql){
    		$salida = str_replace($php,$mysql,$salida);
    	}
    	return $salida;
    }
    
    public function sendMail($text,$to,$subject,$cc="",$bcc="",$fileName="",$filePath="",$fileType="",$contentType="application/xls"){
    	//Send email
    	$mail = new Zend_Mail('utf-8');
    	 
    	// Params
    	$senderName = Mage::getStoreConfig('trans_email/ident_general/name');
    	$senderEmail = Mage::getStoreConfig('trans_email/ident_general/email');
    	 
    	$mail->setBodyHTML($text);
    	$mail->setFrom($senderEmail, $senderName);
    	$mail->addTo($to);
    	$mail->addCc($cc);
    	$mail->addBcc($bcc);
    	$mail->setSubject($subject);
    	
    	// Attachment
    	if(""!=$fileName){
    		$content = file_get_contents($filePath); // e.g. ("attachment/abc.pdf")
    		$attachment = new Zend_Mime_Part($content);
    		$attachment->type = $contentType;
    		$attachment->type = $fileType;
    		$attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
    		$attachment->encoding = Zend_Mime::ENCODING_BASE64;
    		$attachment->filename = $fileName; // name of file
    		
    		$mail->addAttachment($attachment);
    	}
    	
    	
    	try{
    		//Confimation E-Mail Send
    		$mail->send();
    		Mage::log("Email send to ".$to." Subject: ".$subject);
    		return "";
    	}catch(Exception $error){
    		Mage::getSingleton('core/session')->addError($error->getMessage());
    		Mage::log(get_class($this).'.'.__FUNCTION__.' Email failure to '.$to.' : '.$error->getMessage(), Zend_Log::INFO,'sqlreport.log');
    		return $error->getMessage();
    	}
    }
    
    public function createFile($path,$fileName,$content){
    	
    	if(!is_dir($path)){
    		mkdir($path, 0777, true);
    	}
    	$myfile = fopen($path. DS. $fileName, "w") or die("Unable to open file!".$path. DS. $fileName);
    	fwrite($myfile, $content);
    	fclose($myfile);
    	return $path. DS. $fileName;
    }
    
    public function testConnection($db_host,$db_name,$db_username,$db_password){
    	try{
    		$db= Zend_Db::factory('Pdo_Mysql',array(
    				'host' => $db_host,
    				'username' => $db_username,
    				'password' => $db_password,
    				'dbname' => $db_name 
    		));
    		$connection = $db->getConnection();
    		return true;
    	}catch (Exception  $err){
    			return $err->getMessage();
    	}
    }
    
    public function getLastId($connection){
    	$lastId=false;	
    	$sql = "SELECT LAST_INSERT_ID() as last_id";
    	$readresult=$connection->query($sql);
    	$resultTotal = $readresult->fetchAll();
    	foreach ($resultTotal as $fila){
    		$lastId= $fila['last_id'];
    	}
    	
    	return $lastId;
    }
    
}