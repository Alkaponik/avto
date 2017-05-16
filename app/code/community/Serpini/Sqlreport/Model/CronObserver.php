<?php
class Serpini_Sqlreport_Model_CronObserver
{
    
	public function executeReport($schedule){
		
		$CRON_VARIABLE		= 'crontab/jobs/'.$schedule->getJobCode().'/cron/id';
       
       	$report_id = Mage::getModel('core/config_data')
                ->load($CRON_VARIABLE, 'path')
                ->getValue();
       	
                
       	Mage::log($schedule->getJobCode()." : ".$report_id);
       	Mage::log(get_class($this).'.'.__FUNCTION__.' execute cron report id:'.$report_id, Zend_Log::INFO,'sqlreport.log');
       
		$report = Mage::getModel('sqlreport/report');
		$report->loadReport($report_id);
		$report->setRowPerPage("all");

    	if($report->hasCombos()){
    		$report->setComboValuesDefault();
    	}else{
    		$report->loadDataReport();
    	}
    	
    	/*
    	 * Si la forma es cronEmailPerRow
    	 *  offset = 1
    	 * 	Para cada línea de datos
    	 * 		Si el  valor de la primera columna es diferente al valor de la anterior fila
    	 * 			Añadimos combos de columna
    	 * 			Enviamos nuevo email(1,resultado,
    	 *		sino
    	 *			Acumulamos línea de datos
    	 *		fin si
    	 *	fin para
    	 * sino
    	 * 	 offset = 0
    	 * 	 Enviamos nuevo email(offset,resultado,
    	 * fin si
    	 * 	
    	 */ 
    	if(!($report->getSize()==0 && (""==$report->getAtribute('cronNoRow')||"false"==$report->getAtribute('cronNoRow')))){
	    	if("true"==$report->getAtribute('cronEmailPerRow')){
	    		$lastValue="";
	    		$resultSet=array();
	    		
	    		$firstColumnName = $report->getColumnsName();
	    		$firstColumnName = $firstColumnName[0];
	    		
	    		$isFirts = true;
	    		$lastResult = "";
	    		
	    		foreach ($report->getResults() as $result){
	    			if($isFirts){
	    				$lastValue = $result[$firstColumnName];
	    				$isFirts = false;
	    			}
	    			if($result[$firstColumnName]!=$lastValue){
	    				
	    				$this->prepareAndSendEmail($report,$lastResult,$resultSet);
	    				
	    				// Iniciamos el resultSet
	    				$resultSet=array();
	    				$resultSet=array_merge($resultSet,array(sizeof($resultSet)=>$result));
	    				$lastValue = $result[$firstColumnName];
	    				
	    			}else{
	    				$resultSet=array_merge($resultSet,array(sizeof($resultSet)=>$result));
	    			}
	    			$lastResult = $result;
	    		}
	    		
	    		// Enviamos el último no enviado en el bucle
	    		$this->prepareAndSendEmail($report,$lastResult,$resultSet);
	    	}else{
	    		$this->prepareEmail(0,$report,$report->getResults());
	    	}
    	}
    	
    	if("true"!=$report->getAtribute('cronFileSave')&&""!=$fileName){
    		unlink($path . DS . $fileName);
    	}
    }
    
    private function prepareAndSendEmail($report,$result,$resultSet){
    	$columna=0;
    	// Insertados cada columna como nuevo combo simple de texto
    	foreach ($report->getColumnsName() as $_column){
    		$columna++;
    		$combo = Mage::getModel('sqlreport/combo');
    		$combo->loadComboText("C".$columna."C",$result[$_column]);
    		$report->addCombo($combo);
    	}
    	// Enviamos el correo
    	$this->prepareEmail(1,$report,$resultSet);
    	 
    	// Eliminamos todos los nuevos combos añadidos
    	$columna=0;
    	foreach ($report->getColumnsName() as $_column){
    		$columna++;
    		$report->removeCombo("C".$columna."C");
    	}
    }
    
    private function prepareEmail($offset,$report,$resultSet){
    	$utils= Mage::helper('sqlreport/Utils');
    	$logger=Mage::getModel('sqlreport/logger');
    	$setupmanager= Mage::getModel('sqlreport/setup');
    	$path = $report->getAtribute('cronFilePath');
    	
    	
    	// Attach
    	if($report->isLoaded()){
    		$result="";
    		$fileName="";
    		$filePath="";
    		$fileType="";
    		$method = $report->getAtribute('cronEmailAttach');
    		$offset = ($report->getAtribute('cronOffset')=="")?1:$report->getAtribute('cronOffset');
    		switch ($method) {
    			case "CSV": $result=$report->exportCsvResult($resultSet);
    						$fileName = 'sql2report_'.strtolower($report->getId())."_".date("YmdHi").'.csv';
    						$fileType = 'application/csv';
    			break;
    			case "XML": $result=$report->exportXmlResult(strtolower($report->getId()),$resultSet);
    						$fileName = 'sql2report_'.strtolower($report->getId())."_".date("YmdHi").'.xls';
    						$fileType = 'application/xls';
    			break;
    			case "PDF": if(""!=$report->getAtribute("cronPdfTitle")) $report->setTitle($report->getAtribute("cronPdfTitle"));
    						if(""!=$report->getAtribute("cronPdfDescription")) $report->setAtribute("reportDescription",$report->getAtribute("cronPdfDescription"));
    						if(""!=$report->getAtribute("cronPdfFooter")) $report->setAtribute("pdfFooterString",$report->getAtribute("cronPdfFooter"));
    						$fileName = 'sql2report_'.strtolower($report->getId())."_".date("YmdHi").rand(0,100).'.pdf';
    						$result=$report->exportPDFResult($fileName,$path,$resultSet,$offset);
    						$fileType = 'application/pdf';
    			break;
    		}
    		if("NO"!=$method){
    			$filePath = $path.DS.$fileName;
    			if('application/pdf'!=$fileType){
    				Mage::log(get_class($this).'.'.__FUNCTION__.' contentType:'.$fileType, Zend_Log::INFO,'sqlreport.log');
    				$filePath = $utils->createFile($path,$fileName,$result);
    			}
    			Mage::log(get_class($this).'.'.__FUNCTION__.' execute cron file generated:'.$filePath, Zend_Log::INFO,'sqlreport.log');
    		}
    	
	    	$to = $report->getAtribute('cronEmailToEmail');
	    	$to = $report->setParamsTitle($to);
	    				
	    	$cc = $report->getAtribute('cronEmailCCEmail');
	    	$cc = $report->setParamsTitle($cc);
	    				
	    	$bcc = $report->getAtribute('cronEmailBCCEmail');
	    	$bcc = $report->setParamsTitle($bcc);
	    				
	    	$subject = $report->getAtribute('cronEmailSubject');
	    	$subject = $report->setParamsTitle($subject);
	    	
	    	$text = $report->getAtribute('cronEmailText');
	    	$text = $report->setParamsTitle($text);
	    	
	    	$offset = $this->getOffset($text,"TABLE_RESULT_DATA");
	    	
	    	if($offset==""){
	    		$offset=1;
	    		$text = str_replace ($setupmanager->getValue('prefix_parameter').'TABLE_RESULT_DATA',
	    							 $setupmanager->getValue('prefix_parameter').'TABLE_RESULT_DATA[1]',$text);
	    	}
	    	$valor = $report->getResultAsHTML($resultSet,$offset);

	    	$text = str_replace ($setupmanager->getValue('prefix_parameter').'TABLE_RESULT_DATA['.$offset."]",$valor,$text);
	
	    	$error = $utils->sendMail($text,$to,$subject,$cc,$bcc,$fileName,$filePath,$fileType,$contentType);
	    	 
	    	$logger->logReportEmail($report,$text,$to,$subject,$cc,$bcc,$error);
    	}
    }
    
    private function getOffset($texto,$parameter){
    	$aux = substr($texto,strpos($texto,$parameter)+strlen($parameter)+1);
    	$aux = substr($aux,0,strpos($aux,"]"));
    	return $aux;
    }
    
 
}