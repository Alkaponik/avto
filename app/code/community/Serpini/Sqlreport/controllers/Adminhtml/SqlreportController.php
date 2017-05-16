<?php
class Serpini_Sqlreport_Adminhtml_SqlreportController extends Mage_Adminhtml_Controller_Action {
    
	
	public function viewReportAction() {
    	$this->loadLayout();
    	$this->_addContent($this->getLayout()->createBlock('adminhtml/template')->setTemplate('serpini/printReport.phtml'));
    	$headBlock = $this->getLayout()->getBlock('head');
    	$headBlock->addJs('serpini_sqlreport/serpini_sqlreport.js');
    	$headBlock->addJs('serpini_sqlreport/codemirror.js');
    	$headBlock->addJs('serpini_sqlreport/sql.js');
		$headBlock->addJs('serpini_sqlreport/fabtabulous.js');
		$headBlock->addJs('serpini_sqlreport/tablekit.js');
		$headBlock->addJs('serpini_sqlreport/d3.v3.js');
		$headBlock->addJs('serpini_sqlreport/nv.d3.min.js');
		$headBlock->addJs('serpini_sqlreport/chosen.proto.min.js');
    	$headBlock->addCss('sqlreport/codemirror.css');
    	$headBlock->addCss('sqlreport/nv.d3.min.css');
    	$headBlock->addCss('sqlreport/chosen.min.css');
    	$headBlock->addCss('sqlreport/sqlreport.css');
    	$this->renderLayout();
    }
    
    public function adminAction() {
    	$this->loadLayout();
    	$this->_addContent($this->getLayout()->createBlock('adminhtml/template')->setTemplate('serpini/adminReport.phtml'));
    	$headBlock = $this->getLayout()->getBlock('head');
		$headBlock->addJs('serpini_sqlreport/tiny_mce/tiny_mce.js');
    	$headBlock->addJs('scriptaculous/scriptaculous.js');
    	$headBlock->addJs('serpini_sqlreport/serpini_sqlreport.js');
    	$headBlock->addJs('serpini_sqlreport/codemirror.js');
    	$headBlock->addJs('serpini_sqlreport/sql.js');
    	$headBlock->addJs('serpini_sqlreport/jscolor.js');
    	$headBlock->addJs('serpini_sqlreport/croneditor.js');
    	$headBlock->addJs('serpini_sqlreport/slider.js');
    	
    	
    	$headBlock->addCss('sqlreport/codemirror.css');
    	$headBlock->addCss('sqlreport/sqlreport.css');
    	$this->renderLayout();
    }
    
    public function wsAction() {
    	$action = $this->getRequest()->getParam('action');
    	$reportmanager = Mage::helper('sqlreport/ReportManager');
    	$groupmanager = Mage::helper('sqlreport/GroupManager');
    	$combomanager = Mage::helper('sqlreport/ComboManager');
    	$setupmanager = Mage::helper('sqlreport/SetupManager');
    	$permitmanager = Mage::getModel('sqlreport/permissionrole');
    	$linkmanager = Mage::helper('sqlreport/LinkManager');
    	$marketmanager = Mage::helper('sqlreport/MarketManager');
    	$setup = Mage::getModel('sqlreport/setup');
    	$logger=Mage::getModel('sqlreport/logger');
    	$utils= Mage::helper('sqlreport/Utils');
    	if($action!=""){
    		switch ($action) {
    			
    			case "loadReport": $report_id=$this->getRequest()->getParam('report_id');
    							   if($permitmanager->userHasPermissionEditReport($report_id)){
    							   		$result=$reportmanager->loadReport($report_id);
    							   }else{
    							   		$data[0] = array("type" => "error-msg","msg" => Mage::helper('catalog')->__("You don't have permission to view this report"));
    							  		$result= json_encode($data);
    							   }
    							   
    				break;
    			case "delReport": $report_id=$this->getRequest()->getParam('report_id');
    							   if($permitmanager->userHasPermissionEditReport($report_id)){
    							   		$result=$reportmanager->deleteReport($report_id);
    							   }else{
    							   		$data[0] = array("type" => "error-msg","msg" => Mage::helper('catalog')->__("You don't have permission to edit this report"));
    							  		$result= json_encode($data);
    							   }
    							  
    				break;
    			case "saveReport": $report_id=$this->getRequest()->getParam('report_id');
    		 						if($permitmanager->userHasPermissionEditReport($report_id)){
    							   		$title=$this->getRequest()->getParam('title');
	    							   	$sql=$this->getRequest()->getParam('sql');
	    							   	$group_id=$this->getRequest()->getParam('group_id');
	    							   	$combo=$this->getRequest()->getParam('combo');
	    							   	$chartType = $this->getRequest()->getParam('chartType');
	    							   	$chartXValue = $this->getRequest()->getParam('chartXValue');
	    							   	$elementChartYValue = $this->getRequest()->getParam('elementChartYValue');
	    							    $linkTR = $this->getRequest()->getParam('linkTR');
	    							    $linkTRVariables = $this->getRequest()->getParam('linkTRVariables');
	    							    $linkTDVariables = $this->getRequest()->getParam('linkTDVariables');
	    							    $atributes = $this->getRequest()->getParam('atributes');
		    						    $result=$reportmanager->saveReport($report_id,$title,$sql,$group_id,$combo,$chartType,$chartXValue,$elementChartYValue,$linkTR,$linkTRVariables,$linkTDVariables,$atributes);
	    		 							
    							   	}else{
    							   		$data[0] = array("type" => "error-msg","msg" => Mage::helper('catalog')->__("You don't have permission to edit this report"));
    							  		$result= json_encode($data);
    							   	}
    							       				break;
    			case "addReport": if($permitmanager->userHasPermissionCreateReport()){
					    			  $report_id=$this->getRequest()->getParam('report_id');
					    			  $title=$this->getRequest()->getParam('title');
					    			  $sql=$this->getRequest()->getParam('sql');
					    			  $group_id=$this->getRequest()->getParam('group_id');
					    			  $combo=$this->getRequest()->getParam('combo');
					    			  $chartType = $this->getRequest()->getParam('chartType');
					    			  $chartXValue = $this->getRequest()->getParam('chartXValue');
					    			  $elementChartYValue = $this->getRequest()->getParam('elementChartYValue');
									  $linkTR = $this->getRequest()->getParam('linkTR');
	    							  $linkTRVariables = $this->getRequest()->getParam('linkTRVariables');
	    							  $linkTDVariables = $this->getRequest()->getParam('linkTDVariables');
	    							  $atributes = $this->getRequest()->getParam('atributes');
	    							  
	    							  $salida=$reportmanager->addReport($title,$sql,$group_id,$combo,$chartType,$chartXValue,$elementChartYValue,$linkTR,$linkTRVariables,$linkTDVariables,$atributes);
	    							  if($salida instanceof Serpini_Sqlreport_Model_Report){
	    							  	$data[0] = array("type" => "success-msg",
	    							  			"msg" => "Report added",
	    							  			"id" => $salida->getId()
	    							  	);
	    							  	$result= json_encode($data);
	    							  }else{
	    							  	$data[0] = array("type" => "error-msg",
	    							  			"msg" => $salida);
	    							  	$result= json_encode($data);
	    							  }
    							  }else{
    							   		$data[0] = array("type" => "error-msg","msg" => Mage::helper('catalog')->__("You don't have permission to create reports"));
    							  		$result= json_encode($data);
    							  }
    							  
    							  
    				break;
    			case "addGroup" : if($permitmanager->userHasPermissionEditGroups()){
    									$description= $this->getRequest()->getParam('description');
    									$orden= $this->getRequest()->getParam('orden');
    									
					    				$estado = $groupmanager->createGroup($description,$orden);
					    				if($estado instanceof Serpini_Sqlreport_Model_Group){
					    					$data[0] = array("type" => "success-msg",
					    							"msg" => Mage::helper('catalog')->__("Group created"),
					    							"id" => $estado->getId());
					    				}else{
					    					$data[0] = array("type" => "error-msg","msg"  => $estado);
					    				}
					    			} else {
					    					$data[0] = array("type" => "error-msg","msg" => Mage::helper('catalog')->__("You don't have permission to create link"));
					    			}
					    			$result = json_encode($data);
    							  
					break;
				case "delGroup": if($permitmanager->userHasPermissionCreateLink()){
										$groupId = $this->getRequest()->getParam('groupId');
											
										$estado = $groupmanager->deleteGroup($groupId);
										if($estado===true){
											$data[0] = array("type" => "success-msg","msg" => Mage::helper('catalog')->__("Group deleted"));
										}else{
											$data[0] = array("type" => "error-msg","msg"  => $estado);
										}
									} else {
										$data[0] = array("type" => "error-msg","msg" => Mage::helper('catalog')->__("You don't have permission to delete group"));
									}
									$result = json_encode($data);
					break;
    			case "saveGroup" : if($permitmanager->userHasPermissionCreateLink()){
    									$groupId = $this->getRequest()->getParam('groupId');
    									$description= $this->getRequest()->getParam('description');
    									$orden= $this->getRequest()->getParam('orden');
    									
    									$estado = $groupmanager->updateGroup($groupId,$description,$orden);
    									if($estado===true){
	    									$data[0] = array("type" => "success-msg",
	    													 "msg" => Mage::helper('catalog')->__("Group changed"));
	    								}else{
	    									$data[0] = array("type" => "error-msg","msg"  => $estado);
	    								}
    								} else {
    									$data[0] = array("type" => "error-msg","msg" => Mage::helper('catalog')->__("You don't have permission to edit link"));
    								}
    								$result = json_encode($data);
					break;
					
    			case "loadCombo" : $combo_id=$this->getRequest()->getParam('combo_id');
    							   if($permitmanager->userHasPermissionViewFilter($combo_id)){
    							   		$result=$combomanager->loadCombo($combo_id);
    							   }else{
    							   		$data[0] = array("type" => "error-msg","msg" => Mage::helper('catalog')->__("You don't have permission to load this filter"));
    							  		$result= json_encode($data);
    							   }
    							   
    				break;
    			case "saveCombo" : $combo_id=$this->getRequest()->getParam('combo_id');
    							   if($permitmanager->userHasPermissionEditFilter($combo_id)){
    							   		  $title = $this->getRequest()->getParam('title');
		    							  $parameter=$this->getRequest()->getParam('parameter');
		    							  $tipo=$this->getRequest()->getParam('tipo');
		    							  $sql=$this->getRequest()->getParam('sql');
		    							  $setValues=$this->getRequest()->getParam('setValues');
		    							  $selectType=$this->getRequest()->getParam('selectType');
		    							  
		    							  $result=$combomanager->saveCombo($combo_id,$title,$parameter,$tipo,$sql,$setValues,$selectType);
							       }else{
							       		$data[0] = array("type" => "error-msg","msg" => Mage::helper('catalog')->__("You don't have permission to edit this filter"));
							      		$result= json_encode($data);
							       }
    							  
    				break;
    			case "addCombo" : if($permitmanager->userHasPermissionCreateFilter()){
    							   	  $combo_id=$this->getRequest()->getParam('combo_id');
	    							  $title = $this->getRequest()->getParam('title');
	    							  $parameter=$this->getRequest()->getParam('parameter');
	    							  $tipo=$this->getRequest()->getParam('tipo');
	    							  $sql=$this->getRequest()->getParam('sql');
	    							  $setValues=$this->getRequest()->getParam('setValues');
	    							  $selectType=$this->getRequest()->getParam('selectType');
	    							  
	    							  $result=$combomanager->addComboByList($combo_id,$title,$parameter,$tipo,$sql,$setValues,$selectType);
							       }else{
							       		$data[0] = array("type" => "error-msg","msg" => Mage::helper('catalog')->__("You don't have permission to edit this filter"));
							      		$result= json_encode($data);
							       }
							       
    				
    				break;
    			case "delCombo" :$combo_id=$this->getRequest()->getParam('combo_id');
    							 if($permitmanager->userHasPermissionEditFilter($combo_id)){
    							   		$result=$combomanager->deleteCombo($combo_id);
							       }else{
							       		$data[0] = array("type" => "error-msg","msg" => Mage::helper('catalog')->__("You don't have permission to edit this filter"));
							      		$result= json_encode($data);
							       }
    							 
    				break;
    			case "saveSetup" : if($permitmanager->userHasPermissionEditAdmin()){
    							   		$data=$this->getRequest()->getParam('data');
    							  		$result=$setupmanager->saveSetup($data);
    							   }else{
    							   		$data[0] = array("type" => "error-msg","msg" => Mage::helper('catalog')->__("You don't have permission to edit setup"));
    							  		$result= json_encode($data);
    							   }
    								
    							  
    				break;
    			case "getPermissionRole" : 	if($permitmanager->userHasPermissionEditAdmin()){
    							   				$roleId = $this->getRequest()->getParam('role');
			    								$role = Mage::getModel('sqlreport/permissionrole');
			    								$role->loadMe($roleId);
			    								$result = $role->toJsonResponse();
		    							   }else{
		    							   		$data[0] = array("type" => "error-msg","msg" => Mage::helper('catalog')->__("You don't have permission to edit setup"));
		    							  		$result= json_encode($data);
		    							   }
    							   
    				break;
    			case "savePermissionRole" : if($permitmanager->userHasPermissionEditAdmin()){
	    										$roleId = $this->getRequest()->getParam('role_id');
	    										$permissions = $this->getRequest()->getParam('permissions');
	    										$role = Mage::getModel('sqlreport/permissionrole');
	    										$role->loadMe($roleId);
	    										$role->changePermission(json_decode($permissions));
	    										$estado = $role->save();
	    										if($estado===true){
	    											$data[0] = array("type" => "success-msg","msg" => Mage::helper('catalog')->__("Permissions changed"));
	    										}else{
	    											$data[0] = array("type" => "error-msg","msg"  => $estado);
	    										}
		    							   	}else{
		    							   		$data[0] = array("type" => "error-msg","msg" => Mage::helper('catalog')->__("You don't have permission to edit setup"));
		    							   	}
											$result = json_encode($data);
    				break;
    			case "saveLink" : if($permitmanager->userHasPermissionEditLink()){
    									$linkId = $this->getRequest()->getParam('linkId');
    									$description= $this->getRequest()->getParam('description');
    									$url= $this->getRequest()->getParam('url');
    									$type = $this->getRequest()->getParam('type');
    									
    									$estado = $linkmanager->updateLink($linkId,$description,$url,$type);
    									if($estado===true){
	    									$data[0] = array("type" => "success-msg",
	    													 "msg" => Mage::helper('catalog')->__("Link changed"));
	    								}else{
	    									$data[0] = array("type" => "error-msg","msg"  => $estado);
	    								}
    								} else {
    									$data[0] = array("type" => "error-msg","msg" => Mage::helper('catalog')->__("You don't have permission to edit link"));
    								}
    								$result = json_encode($data);
    			
    				break;
    			case "addLink":if($permitmanager->userHasPermissionCreateLink()){
    									$description= $this->getRequest()->getParam('description');
    									$url= $this->getRequest()->getParam('url');
    									$type = $this->getRequest()->getParam('type');
    									
    									$estado = $linkmanager->createLink($description,$url,$type);
    									if($estado instanceof Serpini_Sqlreport_Model_Link){
	    									$data[0] = array("type" => "success-msg",
	    													 "msg" => Mage::helper('catalog')->__("Link created"),
	    													 "id" => $estado->getId());
	    								}else{
	    									$data[0] = array("type" => "error-msg","msg"  => $estado);
	    								}
    								} else {
    									$data[0] = array("type" => "error-msg","msg" => Mage::helper('catalog')->__("You don't have permission to create link"));
    								}
    								$result = json_encode($data);
    				break;
    			case "delLink": if($permitmanager->userHasPermissionCreateLink()){
    									$linkId = $this->getRequest()->getParam('linkId');
    									
    									$estado = $linkmanager->deleteLink($linkId);
    									if($estado===true){
	    									$data[0] = array("type" => "success-msg","msg" => Mage::helper('catalog')->__("Link deleted"));
	    								}else{
	    									$data[0] = array("type" => "error-msg","msg"  => $estado);
	    								}
    								} else {
    									$data[0] = array("type" => "error-msg","msg" => Mage::helper('catalog')->__("You don't have permission to delete link"));
    								}
    								$result = json_encode($data);
    				break;
    			case "getLink": $linkId = $this->getRequest()->getParam('linkId');
    							$reportId = $this->getRequest()->getParam('reportId');
    							$link = Mage::getModel('sqlreport/link');
			    				$link->loadMe($linkId);
			    				$link->setReportLinkId($reportId);
			    				$result = $link->toJsonResponse();
    				break;
    			case "loadComboValues": $comboList = $this->getRequest()->getParam('comboList');
    							$valuesList = $combomanager->getComboListValue($comboList);
			    				$data[0] = array("type" => "success-msg",
			    						"values"  => $valuesList);
			    				$result = json_encode($data);
    				break;
    			case "loadMarket": $reportsList = $marketmanager->getReports();
					     		   
    							if(false!=$reportsList){
    								$groupList = $marketmanager->getGroups();
    								$data[0] = array("type" => "success-msg",
    										"reportList"  => $reportsList,
    										"groupList"   => $groupList);
    								
    								$setup->setValue('lastVisitedMarket',date("Y-m-d"));
    								$setup->saveSetup();
    							}else{
    								$data[0] = array("type" => "error-msg",
    												  "msg"  => $marketmanager->getError());
    							}
    							
    							
				    			
    							$result = json_encode($data);
    							
    				break;
    			case "addReportMarket" :
    							if($permitmanager->userHasPermissionCreateReport()){
					    			  $codeMarket=$this->getRequest()->getParam('codeMarket');
					    			  $report_id=$this->getRequest()->getParam('code');
					    			  
	    							  $data[0]=$marketmanager->addReport($codeMarket,$report_id);
	    							 
    							  }else{
    							   		$data[0] = array("type" => "error-msg","msg" => Mage::helper('catalog')->__("You don't have permission to create reports"));
    							  		$result= json_encode($data);
    							  }
		    				$result = json_encode($data);
    				break;
    			case "sendReportMarket" :
    						$report_id=$this->getRequest()->getParam('code');
    				
    						$estado=$marketmanager->sendReport($report_id);
    						if($estado){
    							$data[0] = array("type" => "success-msg",
    									"msg"  => "Thank you for sending the report to the market, the team will study for inclusion");
    						}else{
    							$data[0] = array("type" => "error-msg",
    									"msg"  => $marketmanager->getError());
    						}
    							
    						$result = json_encode($data);
    					break;
    			case "removeCronLog" :$report_id=$this->getRequest()->getParam('report_id');
    		 						if($permitmanager->userHasPermissionEditReport($report_id)){
    		 							$cronLogList=$this->getRequest()->getParam('cronLogList');
    		 							$estado = $logger->removeLogReportEmail($report_id,$cronLogList);
    		 							if($estado===true){
	    									$data[0] = array("type" => "success-msg","msg" => "Log deleted");
	    								}else{
	    									$data[0] = array("type" => "error-msg","msg"  => $estado);
	    								}
	    								$result = json_encode($data);
    		 						}else{
    							   		$data[0] = array("type" => "error-msg","msg" => Mage::helper('catalog')->__("You don't have permission to edit this report"));
    							  		$result= json_encode($data);
    							   	}
    				
    				break;
    			case "testConnection" : $db_host=$this->getRequest()->getParam('db_host');
    									$db_name=$this->getRequest()->getParam('db_name');
    									$db_username=$this->getRequest()->getParam('db_username');
    									$db_password=$this->getRequest()->getParam('db_password');
    									$res=$utils->testConnection($db_host,$db_name,$db_username,$db_password);
    									if(true===$res){
    										$data[0] = array("type" => "success-msg","msg" => Mage::helper('catalog')->__("Connection successfully"), "status" => "ok");
    									}else{
    										$data[0] = array("type" => "success-msg","msg" => $res, "status" => "ko");
    									}
    									$result= json_encode($data);
    				
    				break;
    			default : $data[0] = array("type" => "error-msg",
    									   "msg"  => Mage::helper('catalog')->__("No action implements for ").$action);
    					  $result = json_encode($data);
    			break;

    		}
    	}else{
    		$data[0] = array("type" => "error-msg",
    				"msg"  => "No action inform ");
    		$result = json_encode($data);
    	}
    	$this->getResponse()->setHeader('Content-type', 'application/json');
    	$this->getResponse()->setBody($result);
    }
    
    public function importAction() {
    	$setup = Mage::helper('sqlreport/InstallManager');
    	$action = $this->getRequest()->getParam('action');
    	$mensaje = "";
    	$fileName = '';
    	if (isset($_FILES['import_file']['name']) && $_FILES['import_file']['name'] != '') {
    		try {
    			$fileName       = $_FILES['import_file']['name'];
    			$fileExt        = strtolower(substr(strrchr($fileName, ".") ,1));
    			$fileNamewoe    = rtrim($fileName, $fileExt);
    			$fileName       = preg_replace('/\s+', '', $fileNamewoe) . time() . '.' . $fileExt;
    	
    			$uploader       = new Varien_File_Uploader('import_file');
    			$uploader->setAllowedExtensions(array('json','zip'));
    			$uploader->setAllowRenameFiles(false);
    			$uploader->setFilesDispersion(false);
    			$path = Mage::getBaseDir('media') . DS . 'serpini_sqlreport'. DS . 'imports';
    			
    			if(!is_dir($path)){
    				mkdir($path, 0777, true);
    			}
    			$uploader->save($path . DS, $fileName );
    			
    			if("json"==$fileExt){
    				$mensaje .=json_encode($setup->importJSON($path . DS . $fileName));
    			}else if("zip"==$fileExt){
    				/*
    				 * Descomprimir
    				 * para cada fichero
    				 * 	   importJSON
    				 *     eliminar fichero
    				 * fin para
    				 * 
    				 */
    				$enzipado = new ZipArchive();
 
					//Abrimos el archivo a descomprimir
					$enzipado->open($path . DS . $fileName);
					 
					//Extraemos el contenido del archivo dentro de la carpeta especificada
					$extraido = $enzipado->extractTo($path);

					/* Si el archivo se extrajo correctamente listamos los nombres de los
					 * archivos que contenia de lo contrario mostramos un mensaje de error
					*/
					$sms = array();
					if($extraido == TRUE){
					 	for ($x = 0; $x < $enzipado->numFiles; $x++) {
					 		$archivo = $enzipado->statIndex($x);
					 		$sms =array_merge($sms,$setup->importJSON($path . DS . $archivo['name']));
					 		unlink($path . DS . $archivo['name']);
					 	}
					 	$mensaje .=json_encode($sms);
					} else {
					 	$mensaje .= json_encode( array("type" => "error-msg",
    							"msg" => Mage::helper('catalog')->__("Error on zip "),
    							"object_type" => $dataImport['zip']));
					}
					
					$enzipado->close();
					    				
    			}

    			unlink($path . DS . $fileName);
    	
    		} catch (Exception $e) {
    			$error = true;
    		}
    	}
    	
    	$result="<script type='text/javascript'>
    			top.reportManager.postToFrameComplete('".$mensaje."');
			</script>";
    	$this->getResponse()->setBody($result);
    } 
    
    
    protected function _addContent(Mage_Core_Block_Abstract $block)
    {
    	$this->getLayout()->getBlock('content')->append($block);
    	return $this;
    }
    
    protected function _initAction() {
    	$this->loadLayout();
    	return $this;
    }
    
    public function exportAction() {
    	
   		$result="";
   		$fileName="";
   		$path=".";
   		$contentType='application/octet-stream';
   		
    	$method = $this->getRequest()->getParam('method');
    	$idReport = $this->getRequest()->getParam('id');
    	$filters = $this->getRequest()->getParam('filter');
    	
    	$report = Mage::getModel('sqlreport/report');
    	$report->loadReport($idReport);
    	$report->setRowPerPage("all");
    	if($filters!=""){
    		$report->setComboValues($filters);
    	}else{
    		$report->loadDataReport();
    	}
    	if($report->isLoaded()){
    		switch ($method) {
    			case "CSV": $result=$report->exportCsv();
    						$fileName = strtolower('sql2report_'.$report->getId()).'.csv';
    			break;
    			case "XML": $result=$report->exportXml(strtolower($report->getId()));
    						$fileName = strtolower('sql2report_'.$report->getId()).'.xls';
    			break;
    			case "REPORT": $result=$report->toJsonComplete(false);
    						$fileName = strtolower('sql2report_'.$report->getId()).'.json';
    			break;
    			case "PDF": $result=$report->exportPDF('sql2report_'.strtolower($report->getId()).'.pdf',$path);
    						$fileName = 'sql2report_'.strtolower($report->getId()).'.pdf';
    						$contentType='application/pdf';
    			break;
    		}
    	}

    	$this->_sendUploadResponse($fileName, $result,$path,$contentType);
    }
    
    protected function _sendUploadResponse($fileName, $content,$path, $contentType)
    {
    	$response = $this->getResponse();
    	$response->setHeader('HTTP/1.1 200 OK','');
    	$response->setHeader('Pragma', 'public', true);
    	$response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
    	$response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
    	$response->setHeader('Last-Modified', date('r'));
    	$response->setHeader('Accept-Ranges', 'bytes');
    	$response->setHeader('Content-type', $contentType);
    	if($contentType=='application/pdf'){
    		$response->setHeader('Content-Length', filesize($path.DS.$fileName));
    		$pdf = Zend_Pdf::load($path.DS.$fileName);
    		$response->setBody($pdf->render());
    		unlink($path . DS . $fileName);
    	}else{
    		$response->setHeader('Content-Length', strlen($content));
    		$response->setBody($content);
    	}
    	
    	$response->sendResponse();
    	die;
    }
    
    //SUPEE  6285 Access Denied
    protected function _isAllowed(){
    	return true;
    }
    
    
}

?>