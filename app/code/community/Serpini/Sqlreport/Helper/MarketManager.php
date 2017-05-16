<?php
class Serpini_Sqlreport_Helper_MarketManager extends Mage_Core_Helper_Abstract {
	
	protected $MARKET_URL = "www.serpini.es";
	
	protected $MARKET_REPORTLIST 	= "/sql2report/get_reports/?post_type=reportList";
	protected $MARKET_GETREPORT  	= "/sql2report/get_reports/?post_type=reportGet&report_id=";
	protected $MARKET_GROUPLIST  	= "/sql2report/get_groups/?post_type=groupList";
	protected $MARKET_SENDREPORT 	= "/sql2report/get_reports/?post_type=sendReport";
	
	protected $timeout				= 30;
	
	protected $lastError			= "";
	
	
	public function getReports(){
		$reportmanager = Mage::helper('sqlreport/ReportManager');
		$setupmanager = Mage::getModel('sqlreport/setup');
		$jsonReports = $this->getReportListFromMarket();
		if(false!=$jsonReports){
			$data = json_decode($jsonReports, TRUE);
			$reportList = array();
			$datetime2 = new DateTime($setupmanager->getValue('lastVisitedMarket'));
			foreach ($data as $dataImport) {
				$estado = "new";	
				$code = $reportmanager->reportExists($dataImport['title'],'edit');
				if($code!=false){
					
					$report = Mage::getModel('sqlreport/report');
					$report->loadReport($code);
					$version = $report->getAtribute("version")==""?1:$report->getAtribute("version");
					if($dataImport['version']>$version){
						$estado = "update";
					}else{
						$estado = "instaled";
					}
					//echo $dataImport['title'].":".$code." v ".$version." vM ".$dataImport['version']." estado:".$estado." --- ";
					
				}else{
					$code = $dataImport['code'];
				}
				
				$datetime1 = new DateTime($dataImport['last_update']);
				$interval = $datetime2->diff($datetime1)->format('%r%d');
				$isNew = ($interval>=0?"yes":"no");
				
				$reportList[]=array("id"=>$dataImport['id'],
									"title"=>$dataImport['title'],
									"code"=>$code,
									"version"=>$dataImport['version'],
									"estado"=> $estado,
									"description"=>$dataImport['description'],
									"grupo"=>$dataImport['grupo'],
									"grupoId"=>$dataImport['grupoId'],
									"isnew"=>$isNew
								);
			}

			return $reportList;
		}else{
			return false;
		}
	}
	
	public function getGroups(){
		$setupmanager = Mage::getModel('sqlreport/setup');
		$jsonReports = $this->getGroupsFromMarket();
		if(false!=$jsonReports){
			$data = json_decode($jsonReports, TRUE);
			$groupList = array();
			
			$datetime2 = new DateTime($setupmanager->getValue('lastVisitedMarket'));
			foreach ($data as $dataImport) {
				$datetime1 = new DateTime($dataImport['last_update']);
				
				$interval = $datetime2->diff($datetime1)->format('%r%d');
					
				$isNew = ($interval>=0?"yes":"no");
				
				$groupList[]=array("id"=>$dataImport['id'],
									"title"=>$dataImport['title'],
									"iconurl"=>"",
									"isnew"=>$isNew
								);
			}
			
			return $groupList;
		}else{
			return false;
		}
	}
	
	public function getError(){
		return $this->lastError;
	}
	
	public function addReport($reportIdMarket,$reportId){
		$reportmanager = Mage::helper('sqlreport/ReportManager');
		$setup = Mage::helper('sqlreport/InstallManager');
		$utils = Mage::helper('sqlreport/Utils');
		$jsonReport = $this->getReportFromMarket($reportIdMarket);
		if(false!=$jsonReport){
			
			$reportmanager->deleteReport($reportId);
			
			$path = Mage::getBaseDir('media') . DS . 'serpini_sqlreport'. DS . 'imports';
			$fileName = "market_".$reportIdMarket.".json";
			
			$utils->createFile($path,$fileName,$jsonReport);
			$sms = $setup->importJSON($path . DS . $fileName);
			return $sms;
		}else{
			return array("type" => "error-msg","msg"  => Mage::helper('catalog')->__('No get report from market')." : ".$this->getError());
		}
	}
	
	public function sendReport($reportId){
		$report = Mage::getModel('sqlreport/report');
		$report->loadReport($reportId);
		$result=$report->toJsonComplete(false);
		$data = array('file' => $result);
		$respuesta= $this->getResponsePOST($this->MARKET_URL.$this->MARKET_SENDREPORT,$data);
		return ('"OK"'==$respuesta)?true:false;
	}

	private function getReportListFromMarket(){
		return $this->getResponse($this->MARKET_URL.$this->MARKET_REPORTLIST);
	}
	
	
	private function getGroupsFromMarket(){
		return $this->getResponse($this->MARKET_URL.$this->MARKET_GROUPLIST);
	}

	private function getReportFromMarket($reportId){
		return $this->getResponse($this->MARKET_URL.$this->MARKET_GETREPORT.$reportId);
	}
	
	private function getResponse($url){
		$setup = Mage::helper('sqlreport/InstallManager');
		//url contra la que atacamos
		$url.="&version=".$setup->getActualVersion()."&domain=".base64_encode($this->getDomain());
		$ch = curl_init($url);
		//a true, obtendremos una respuesta de la url, en otro caso,
		//true si es correcto, false si no lo es
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//establecemos el verbo http que queremos utilizar para la petición
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, $this->timeout );
		curl_setopt( $handle, CURLOPT_TIMEOUT, $this->timeout );
		//obtenemos la respuesta
		$response = curl_exec($ch);
		// recuperar error
		$this->lastError=curl_error($ch);
		// Se cierra el recurso CURL y se liberan los recursos del sistema
		curl_close($ch);
		if(!$response) {
			return false;
		}else{
			return $response;
		}
	}
	
	private function getResponsePOST($url,$postData){
		$setup = Mage::helper('sqlreport/InstallManager');
		//url contra la que atacamos
		$url.="&version=".$setup->getActualVersion()."&domain=".base64_encode($this->getDomain());
		$ch = curl_init($url);
		//a true, obtendremos una respuesta de la url, en otro caso,
		//true si es correcto, false si no lo es
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//establecemos el verbo http que queremos utilizar para la petición
		//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false); // requerido a partir de PHP 5.6.0
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		
		curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, $this->timeout );
		curl_setopt( $handle, CURLOPT_TIMEOUT, $this->timeout );
		//obtenemos la respuesta
		$response = curl_exec($ch);
		// recuperar error
		$this->lastError=curl_error($ch);
		// Se cierra el recurso CURL y se liberan los recursos del sistema
		curl_close($ch);
		if(!$response) {
			return false;
		}else{
			return $response;
		}
	}
	
	private function getDomain(){
		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
	}
}