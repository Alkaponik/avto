<?php
/**
 * NOTICE OF LICENSE
 *
 */

class Serpini_Sqlreport_Helper_InstallManager extends Mage_Core_Helper_Abstract
{
	protected $versionSql2Report = "0.4.2"; 
	
	public function checkInstall(){
		$utils = Mage::helper('sqlreport/Utils');
		$currentConfigVersion = $this->getActualVersion();
		$importPath = Mage::getBaseDir().DS."app".DS."code".DS."community".DS."Serpini".DS."Sqlreport".DS."sql".DS."sqlreport_setup";	
		Mage::log(get_class($this).'.'.__FUNCTION__.' currentConfigVersion:'.$currentConfigVersion.' versionSql2Report:'.$this->versionSql2Report, Zend_Log::INFO,'sqlreport.log');
		if(version_compare($this->versionSql2Report,$currentConfigVersion)>0){
			Mage::log(get_class($this).'.'.__FUNCTION__.' upgrade', Zend_Log::INFO,'sqlreport.log');

			if($this->runDbSchemaUpgrade($currentConfigVersion)){
				if($currentConfigVersion=="0.0.0"){
					$importPath = Mage::getBaseDir().DS."app".DS."code".DS."community".DS."Serpini".DS."Sqlreport".DS."sql".DS."sqlreport_setup";
					$this->importXML($importPath.DS."reports.xml");
					$importReports = $utils->getFilesDirectoryByExtension($importPath,"json");
					$impotadoBien = true;
					foreach($importReports as $report ){
						$estadoImportacion = $this->importJSON($importPath.DS.$report);
						foreach ($estadoImportacion as $estado){
							if($estado['type']=="error-msg"){
								$this->printMessage("error",$estado['msg']);
								$impotadoBien=false;
							}
						}
					}
					if($impotadoBien){
						$this->printMessage("success",Mage::helper('catalog')->__('Instalation plugin Sql2Report correctly'));
					}else{
						$this->printMessage("warning",Mage::helper('catalog')->__('Instalation plugin Sql2Report correctly but errors importing reports'));
					}
					
				}else{
					$this->printMessage("success",Mage::helper('catalog')->__('Upgrade plugin Sql2Report from %s to %s correctly',$currentConfigVersion,$this->versionSql2Report));
				}
				
				$mageCore = new Mage_Core_Model_Config();
				$mageCore->saveConfig('serpini/sqlreport/version', $this->versionSql2Report, 'default', 0);
				$this->getActualVersion();
				
			}
		}
	}
	
	public function getActualVersion(){
		//$currentConfigVersion = Mage::getConfig()->getNode('default/serpini/sqlreport/version');
		//if($currentConfigVersion == ""){
			$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
			$select = $connection->select()
			->from($this->gtn('core_config_data'), array('value'))
			->where('path = ?','serpini/sqlreport/version');
			try{
				$readresult=$connection->fetchAll($select);
				if(sizeof($readresult)>0){
					foreach ($readresult as $fila){
						$currentConfigVersion = $fila['value'];
					}
				}else{
					$select = $connection->select()
					->from($this->gtn('sqlrpt_combo'), array('combo_id','description','type','parameter'))
					->order('combo_id');
					try{
						$readresult=$connection->fetchAll($select);
						$currentConfigVersion="0.1.0";
					}catch (Exception  $err){
						$currentConfigVersion="0.0.0";
					}
				}
	
			}catch (Exception  $err){
				$currentConfigVersion="0.0.0";
			}	
		//}
		return $currentConfigVersion;
	}
	
	public function getDbSchema ($fromVersion, $returnComplete=false)
	{
	    $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		$instructions = array();
		$actualVersion = $fromVersion;
		if(version_compare("0.1.0",$actualVersion)>0){
			$actualVersion = "0.1.0";
			$instructions = array_merge(
					$instructions,
						array(array("type" => "table", "name" => "sqlrpt_combo", "items" =>
								array(
										array("sql-column", "combo_id", "varchar(32) NOT NULL"),
										array("sql-column", "description", "varchar(32) NOT NULL"),
										array("sql-column", "type", "varchar(10) NOT NULL"),
										array("sql-column", "parameter", "varchar(32) NOT NULL"),
										array("key", "PRIMARY KEY", "combo_id")
								)
						)),array(array("type" => "table", "name" => "sqlrpt_combo_int", "items" =>
									array(
										array("sql-column", "value_id", "int(10) unsigned NOT NULL AUTO_INCREMENT"),
										array("sql-column", "type_id", "int(10) NOT NULL"),
										array("sql-column", "combo_id", "varchar(32) NOT NULL"),
										array("sql-column", "value", "int(10) NOT NULL"),
										array("key", "PRIMARY KEY", "value_id")
									)
						)),array(array("type" => "table", "name" => "sqlrpt_combo_text", "items" =>
									array(
										array("sql-column", "value_id", "int(10) unsigned NOT NULL AUTO_INCREMENT"),
										array("sql-column", "type_id", "int(10) NOT NULL"),
										array("sql-column", "combo_id", "varchar(32) NOT NULL"),
										array("sql-column", "value", "text NOT NULL"),
										array("key", "PRIMARY KEY", "value_id")
									)
						)),array(array("type" => "table", "name" => "sqlrpt_combo_type", "items" =>
									array(
										array("sql-column", "type_id", "int(10) unsigned NOT NULL AUTO_INCREMENT"),
										array("sql-column", "type_code", "varchar(32) NOT NULL"),
										array("sql-column", "type", "varchar(10) NOT NULL"),
										array("key", "PRIMARY KEY", "type_id")
									)
						)),array(array("type" => "table", "name" => "sqlrpt_combo_varchar", "items" =>
									array(
										array("sql-column", "value_id", "int(10) unsigned NOT NULL AUTO_INCREMENT"),
										array("sql-column", "type_id", "int(10) NOT NULL"),
										array("sql-column", "combo_id", "varchar(32) NOT NULL"),
										array("sql-column", "value", "varchar(32) NOT NULL"),
										array("key", "PRIMARY KEY", "value_id")
									)
						)),array(array("type" => "table", "name" => "sqlrpt_group", "items" =>
									array(
										array("sql-column", "group_id", "varchar(32) NOT NULL"),
										array("sql-column", "description", "varchar(32) NOT NULL"),
										array("sql-column", "orden", "int(10) NOT NULL"),
										array("key", "PRIMARY KEY", "group_id")
									)
						)),array(array("type" => "table", "name" => "sqlrpt_report", "items" =>
									array(
										array("sql-column", "report_id", "varchar(32) NOT NULL"),
										array("sql-column", "description", "varchar(32) NOT NULL"),
										array("sql-column", "report_sql", "text NOT NULL"),
										array("sql-column", "group_id", "varchar(32) NOT NULL"),
										array("key", "PRIMARY KEY", "report_id"),
										array("constraint", "sqlrpt_report_group_fk", "group_id","sqlrpt_group","group_id")
									)
						)),array(array("type" => "table", "name" => "sqlrpt_report_combo", "items" =>
									array(
										array("sql-column", "id", "int(10) unsigned NOT NULL AUTO_INCREMENT"),
										array("sql-column", "report_id", "varchar(32) NOT NULL"),
										array("sql-column", "combo_id", "varchar(32) NOT NULL"),
										array("sql-column", "order_n", "int(10) NOT NULL"),
										array("key", "PRIMARY KEY", "id")
									)
						)),array(array("type" => "table", "name" => "sqlrpt_report_int", "items" =>
									array(
										array("sql-column", "value_id", "int(10) unsigned NOT NULL AUTO_INCREMENT"),
										array("sql-column", "type_id", "int(10) NOT NULL"),
										array("sql-column", "report_id", "varchar(32) NOT NULL"),
										array("sql-column", "value", "int(10) NOT NULL"),
										array("key", "PRIMARY KEY", "value_id")
									)
						)),array(array("type" => "table", "name" => "sqlrpt_report_text", "items" =>
									array(
										array("sql-column", "value_id", "int(10) unsigned NOT NULL AUTO_INCREMENT"),
										array("sql-column", "type_id", "int(10) NOT NULL"),
										array("sql-column", "report_id", "varchar(32) NOT NULL"),
										array("sql-column", "value", "text NOT NULL"),
										array("key", "PRIMARY KEY", "value_id")
									)
						)),array(array("type" => "table", "name" => "sqlrpt_report_type", "items" =>
									array(
										array("sql-column", "type_id", "int(10) unsigned NOT NULL AUTO_INCREMENT"),
										array("sql-column", "type_code", "varchar(32) NOT NULL"),
										array("sql-column", "type", "varchar(10) NOT NULL"),
										array("key", "PRIMARY KEY", "type_id")
									)
						)),array(array("type" => "table", "name" => "sqlrpt_report_varchar", "items" =>
									array(
										array("sql-column", "value_id", "int(10) unsigned NOT NULL AUTO_INCREMENT"),
										array("sql-column", "type_id", "int(10) NOT NULL"),
										array("sql-column", "report_id", "varchar(32) NOT NULL"),
										array("sql-column", "value", "varchar(32) NOT NULL"),
										array("key", "PRIMARY KEY", "value_id")
									)
						)),array(array("type" => "table", "name" => "sqlrpt_setup", "items" => 
								array(
										array("sql-column", "value_id", "int(10) unsigned NOT NULL AUTO_INCREMENT"),
										array("sql-column", "name", "varchar(32) NOT NULL"),
										array("sql-column", "value", "text NOT NULL"),
										array("key", "PRIMARY KEY", "value_id")
									)
						)),array(array("type" => "insert", "name" => "sqlrpt_setup", "items" => array(array("sql-column", "name", "prefix_parameter"),array("sql-column", "value", ":")))),
						array(array("type" => "insert", "name" => "sqlrpt_setup", "items" => array(array("sql-column", "name", "prefix_table"),array("sql-column", "value", "@")))),
						array(array("type" => "insert", "name" => "sqlrpt_setup", "items" => array(array("sql-column", "name", "date_mask"),array("sql-column", "value", "%m/%e/%y")))),
						array(array("type" => "insert", "name" => "sqlrpt_setup", "items" => array(array("sql-column", "name", "exp_col_delimiter"),array("sql-column", "value", ";")))),
						array(array("type" => "insert", "name" => "sqlrpt_setup", "items" => array(array("sql-column", "name", "prefix_parameter"),array("sql-column", "value", ":")))),
						array(array("type" => "insert", "name" => "sqlrpt_setup", "items" => array(array("sql-column", "name", "exp_dec_separator"),array("sql-column", "value", ".")))),
						array(array("type" => "insert", "name" => "sqlrpt_setup", "items" => array(array("sql-column", "name", "svn_print_header"),array("sql-column", "value", "true")))),
						array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" => array(array("sql-column", "type_code", "order"),array("sql-column", "type", "int")))),
						array(array("type" => "insert", "name" => "sqlrpt_combo_type", "items" => array(array("sql-column", "type_code", "sql"),array("sql-column", "type", "text")))),
						array(array("type" => "insert", "name" => "sqlrpt_combo_type", "items" => array(array("sql-column", "type_code", "set"),array("sql-column", "type", "text"))))
			);
		}
		
		if( version_compare("0.2.0",$actualVersion)>0){
			$actualVersion = "0.2.0";
			$instructions = array_merge(
					$instructions,
						array(array("type" => "table", "name" => "sqlrpt_chart_series", "items" =>
							array(
									array("sql-column", "id", "int(11) NOT NULL AUTO_INCREMENT"),
									array("sql-column", "report_id", "varchar(32) NOT NULL"),
									array("sql-column", "column_num", "int(11) NOT NULL"),
									array("sql-column", "serie_num", "int(11) NOT NULL"),
									array("key", "PRIMARY KEY", "id")
							)
						)),array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>
							array(
									array("sql-column", "name", "chart_height_default"),
									array("sql-column", "value", "400")
							)
						)),array(array("type" => "sql-column-change", "table" => "sqlrpt_report", 
								"oldname" => "description",
								"newname" => "title",
								"newtype" => "varchar(32)"
						)),array(array("type" => "sql-column-change", "table" => "sqlrpt_combo", 
								"oldname" => "description",
								"newname" => "title",
								"newtype" => "varchar(32)"
						)),array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" => array(
								array("sql-column", "type_code", "chart_type"),
								array("sql-column", "type", "varchar")
									)
						)),array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" => array(
								array("sql-column", "type_code", "chartXValue"),
								array("sql-column", "type", "int")
									)
						)),array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" => array(
								array("sql-column", "type_code", "chart_height"),
								array("sql-column", "type", "int")
									)
						)),array(array("type" => "table", "name" => "sqlrpt_report_role", "items" =>
							array(
									array("sql-column", "id", "int(11) NOT NULL AUTO_INCREMENT"),
									array("sql-column", "report_id", "varchar(32) NOT NULL"),
									array("sql-column", "role", "int(10) unsigned NOT NULL"),
									array("sql-column", "read", "tinyint(1) NOT NULL"),
									array("sql-column", "edit", "tinyint(1) NOT NULL"),
									array("key", "PRIMARY KEY", "id")
							)
						))
					
			);
		}
		
		if( version_compare("0.3.0",$actualVersion)>0){
			$actualVersion = "0.3.0";
			$instructions = array_merge(
					$instructions,
						array(array("type" => "table", "name" => "sqlrpt_link", "items" =>array(
									array("sql-column", "link_id", "varchar(32) NOT NULL"),
									array("sql-column", "description", "varchar(1024) NOT NULL"),
									array("sql-column", "url", "varchar(1024) NOT NULL"),
									array("sql-column", "type", "varchar(32) NOT NULL"),
									array("key", "PRIMARY KEY", "link_id")
							)
						)),array(array("type" => "table", "name" => "sqlrpt_report_link", "items" =>
							array(
									array("sql-column", "id", "int(11) NOT NULL AUTO_INCREMENT"),
									array("sql-column", "report_id", "varchar(32) NOT NULL"),
									array("sql-column", "link_id", "varchar(32) NOT NULL"),
									array("sql-column", "column", "int(11) NULL"),
									array("key", "PRIMARY KEY", "id")
							)
						)),array(array("type" => "table", "name" => "sqlrpt_report_link_value", "items" =>
							array(
									array("sql-column", "id", "int(11) NOT NULL AUTO_INCREMENT"),
									array("sql-column", "report_link_id", "int(11) NOT NULL"),
									array("sql-column", "variable", "varchar(32) NOT NULL"),
									array("sql-column", "column_num", "int(11) NOT NULL"),
									array("key", "PRIMARY KEY", "id")
							)
						)),
						array(array("type" => "insert", "name" => "sqlrpt_combo_type", "items" => array(array("sql-column", "type_code", "selectType"),array("sql-column", "type", "text")))),
						array(array("type" => "insert", "name" => "sqlrpt_setup", "items" => array(array("sql-column", "name", "svn_text_qualifier"),array("sql-column", "value", '"'))))
						
						);
		}
		
		if( version_compare("0.4.0",$actualVersion)>0){
			$actualVersion = "0.4.0";
			$instructions = array_merge(
					$instructions,
						 array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsHeaderBackgroundColor"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsHeaderColor"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsOddRowBackgroundColor"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsOddRowColor"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsEvenRowBackgroundColor"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsEvenRowColor"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsHeaderFont"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsOddRowFont"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsEvenRowFont"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsHeaderAlignLeft"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsHeaderAlignCenter"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsHeaderAlignRight"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsHeaderBold"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsHeaderItalic"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsHeaderBorderAll"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsHeaderBorderTop"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsHeaderBorderRight"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsHeaderBorderBottom"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsHeaderBorderLeft"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsOddRowAlignLeft"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsOddRowAlignCenter"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsOddRowAlignRight"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsOddRowBold"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsOddRowItalic"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsOddRowBorderAll"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsOddRowBorderTop"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsOddRowBorderRight"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsOddRowBorderBottom"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsOddRowBorderLeft"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsEvenRowAlignLeft"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsEvenRowAlignCenter"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsEvenRowAlignRight"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsEvenRowBold"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsEvenRowItalic"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsEvenRowBorderAll"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsEvenRowBorderTop"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsEvenRowBorderRight"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsEvenRowBorderBottom"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsEvenRowBorderLeft"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsHeaderSize"),array("sql-column", "type", "int"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsOddRowSize"),array("sql-column", "type", "int"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsEvenRowSize"),array("sql-column", "type", "int"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "xlsDefault"),array("sql-column", "type", "varchar"))))
					
					 	,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "cronActive"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "cronString"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "cronEmailSubject"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "cronEmailToEmail"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "cronEmailText"),array("sql-column", "type", "text"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "cronEmailAttach"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "cronFilePath"),array("sql-column", "type", "text"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "cronFileSave"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "cronNoRow"),array("sql-column", "type", "varchar"))))
						,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "version"),array("sql-column", "type", "int"))))
					
						,array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "xlsDefHeaderColor"),array("sql-column", "value", "000000"))))
						,array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "xlsDefEvenRowColor"),array("sql-column", "value", "000000"))))
						,array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "xlsDefOddRowColor"),array("sql-column", "value", "000000"))))
						,array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "xlsDefHeaderBackgroundColor"),array("sql-column", "value", "D4D4D4"))))
						,array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "xlsDefEvenRowBackgroundColor"),array("sql-column", "value", "FFFFFF"))))
						,array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "xlsDefOddRowBackgroundColor"),array("sql-column", "value", "F6F6F6"))))
						,array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "xlsDefHeaderAlignCenter"),array("sql-column", "value", "true"))))
						,array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "xlsDefHeaderSize"),array("sql-column", "value", "10"))))
						,array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "xlsDefEvenRowSize"),array("sql-column", "value", "8"))))
						,array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "xlsDefOddRowSize"),array("sql-column", "value", "8"))))
						,array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "xlsDefHeaderBold"),array("sql-column", "value", "true"))))
						,array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "cronFilePath"),array("sql-column", "value", $connection->quote(Mage::getBaseDir('media') . DS . 'serpini_sqlreport'. DS . 'emails')))))
						,array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "lastVisitedMarket"),array("sql-column", "value", date("Y-m-d")))))
										
						,array(array("type" => "sql-column-add", "table" => "sqlrpt_report_combo", "name" => "value", "params" => "varchar(1024)"))		
			);
		}
		
		if( version_compare("0.4.1",$actualVersion)>0){
			$actualVersion = "0.4.1";
			$instructions = array_merge(
					$instructions,
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "cronEmailPerRow"),array("sql-column", "type", "varchar"))))
					,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "cronEmailCCEmail"),array("sql-column", "type", "varchar"))))
					,array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "cronEmailBCCEmail"),array("sql-column", "type", "varchar"))))
					
					,array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "db_host"),array("sql-column", "value", ""))))
					,array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "db_name"),array("sql-column", "value", ""))))
					,array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "db_username"),array("sql-column", "value", ""))))
					,array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "db_password"),array("sql-column", "value", ""))))
					
					,array(array("type" => "table", "name" => "sqlrpt_cron_log", "items" =>
							array(
									array("sql-column", "log_id", "int(10) unsigned NOT NULL AUTO_INCREMENT"),
									array("sql-column", "created_at", "timestamp DEFAULT CURRENT_TIMESTAMP"),
									array("sql-column", "report_id", "varchar(32) NOT NULL"),
									array("sql-column", "to", "varchar(500) NOT NULL"),
									array("sql-column", "cc", "varchar(500) NULL"),
									array("sql-column", "bcc", "varchar(500) NULL"),
									array("sql-column", "subject", "varchar(500) NOT NULL"),
									array("sql-column", "text", "text NOT NULL"),
									array("sql-column", "error", "varchar(500) NULL"),
									array("key", "PRIMARY KEY", "log_id")
									
							)
					))
			);
			
		}
		
		if( version_compare("0.4.2",$actualVersion)>0){
			$actualVersion = "0.4.2";
			$instructions = array_merge(
					$instructions,
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "rowPerPage"),array("sql-column", "value", "20")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "reportDescription"),array("sql-column", "type", "text")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "reportFooter"),array("sql-column", "type", "text")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfLogoUrl"),array("sql-column", "type", "text")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfLogoWidth"),array("sql-column", "type", "int")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfLogoHeight"),array("sql-column", "type", "int")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTitleFontName"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTitleFontSize"),array("sql-column", "type", "int")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTitleFontBold"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTitleFontItalic"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTitleFontColor"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfDescriptionFontName"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfDescriptionFontSize"),array("sql-column", "type", "int")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfDescriptionAlignLeft"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfDescriptionAlignCenter"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfDescriptionAlignRight"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfDescriptionFontBold"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfDescriptionFontItalic"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfDescriptionFontColor"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfDescriptionSpace"),array("sql-column", "type", "int")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfFiltersShow"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfFiltersFontName"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfFiltersFontSize"),array("sql-column", "type", "int")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfFilterFontBold"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfFilterFontItalic"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfFilterFontColor"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableHFontName"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableHFontSize"),array("sql-column", "type", "int")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableHAlignLeft"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableHAlignCenter"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableHAlignRight"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableHFontBold"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableHFontItalic"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableHBackgroundColor"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableHFontColor"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableHBorderAll"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableHBorderTop"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableHBorderBottom"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableHBorderRight"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableHBorderLeft"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableHBorderInV"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableHBorderInH"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableDFontName"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableDFontsize"),array("sql-column", "type", "int")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableDAlignLeft"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableDAlignCenter"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableDAlignRight"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableDFontBold"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableDFontItalic"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableDOBackgroundColor"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableDEBackgroundColor"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableDFontColor"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableDBorderAll"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableDBorderTop"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableDBorderBottom"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableDBorderRight"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableDBorderLeft"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableDBorderInV"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableDBorderInH"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableFShow"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableFFontName"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableFFontsize"),array("sql-column", "type", "int")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableFAlignLeft"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableFAlignCenter"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableFAlignRight"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableFFontBold"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableFFontItalic"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableFBackgroundColor"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableFFontColor"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableFBorderAll"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableFBorderTop"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableFBorderBottom"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableFBorderRight"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableFBorderLeft"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfTableFBorderInV"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfDefault"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "cronOffset"),array("sql-column", "type", "int")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "columnGroup"),array("sql-column", "type", "text")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfFooterString"),array("sql-column", "type", "text")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfFooterFontName"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfFooterFontSize"),array("sql-column", "type", "int")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfFooterAlignLeft"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfFooterAlignCenter"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfFooterAlignRight"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfFooterFontBold"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfFooterFontItalic"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "pdfFooterFontColor"),array("sql-column", "type", "varchar")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "cronPdfTitle"),array("sql-column", "type", "text")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "cronPdfDescription"),array("sql-column", "type", "text")))),
					array(array("type" => "insert", "name" => "sqlrpt_report_type", "items" =>array(array("sql-column", "type_code", "cronPdfFooter"),array("sql-column", "type", "text")))),
					
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfLogoUrl"),array("sql-column", "value", "")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfLogoWidth"),array("sql-column", "value", "100")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfLogoHeight"),array("sql-column", "value", "100")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTitleFontName"),array("sql-column", "value", "Helvetica")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTitleFontSize"),array("sql-column", "value", "20")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTitleFontBold"),array("sql-column", "value", "true")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTitleFontItalic"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTitleFontColor"),array("sql-column", "value", "000000")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfDescriptionFontName"),array("sql-column", "value", "Helvetica")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfDescriptionFontSize"),array("sql-column", "value", "6")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfDescriptionAlignLeft"),array("sql-column", "value", "true")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfDescriptionAlignCenter"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfDescriptionAlignRight"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfDescriptionFontBold"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfDescriptionFontItalic"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfDescriptionFontColor"),array("sql-column", "value", "000000")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfDescriptionSpace"),array("sql-column", "value", "40")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfFiltersShow"),array("sql-column", "value", "true")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfFiltersFontName"),array("sql-column", "value", "Helvetica")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfFiltersFontSize"),array("sql-column", "value", "6")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfFilterFontBold"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfFilterFontItalic"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfFilterFontColor"),array("sql-column", "value", "000000")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableHFontName"),array("sql-column", "value", "Helvetica")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableHFontSize"),array("sql-column", "value", "6")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableHAlignLeft"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableHAlignCenter"),array("sql-column", "value", "true")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableHAlignRight"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableHFontBold"),array("sql-column", "value", "true")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableHFontItalic"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableHBackgroundColor"),array("sql-column", "value", "D4D4D4")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableHFontColor"),array("sql-column", "value", "000000")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableHBorderAll"),array("sql-column", "value", "true")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableHBorderTop"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableHBorderBottom"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableHBorderRight"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableHBorderLeft"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableHBorderInV"),array("sql-column", "value", "true")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableHBorderInH"),array("sql-column", "value", "true")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableDFontName"),array("sql-column", "value", "Helvetica")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableDFontsize"),array("sql-column", "value", "6")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableDAlignLeft"),array("sql-column", "value", "true")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableDAlignCenter"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableDAlignRight"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableDFontBold"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableDFontItalic"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableDOBackgroundColor"),array("sql-column", "value", "FFFFFF")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableDEBackgroundColor"),array("sql-column", "value", "F6F6F6")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableDFontColor"),array("sql-column", "value", "000000")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableDBorderAll"),array("sql-column", "value", "true")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableDBorderTop"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableDBorderBottom"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableDBorderRight"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableDBorderLeft"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableDBorderInV"),array("sql-column", "value", "true")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableDBorderInH"),array("sql-column", "value", "true")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableFShow"),array("sql-column", "value", "true")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableFFontName"),array("sql-column", "value", "Helvetica")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableFFontsize"),array("sql-column", "value", "6")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableFAlignLeft"),array("sql-column", "value", "true")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableFAlignCenter"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableFAlignRight"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableFFontBold"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableFFontItalic"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableFBackgroundColor"),array("sql-column", "value", "D4D4D4")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableFFontColor"),array("sql-column", "value", "000000")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableFBorderAll"),array("sql-column", "value", "true")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableFBorderTop"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableFBorderBottom"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableFBorderRight"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableFBorderLeft"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfTableFBorderInV"),array("sql-column", "value", "true")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfFooterFontName"),array("sql-column", "value", "Helvetica")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfFooterFontSize"),array("sql-column", "value", "6")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfFooterAlignLeft"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfFooterAlignCenter"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfFooterAlignRight"),array("sql-column", "value", "true")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfFooterFontBold"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfFooterFontItalic"),array("sql-column", "value", "false")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfFooterFontColor"),array("sql-column", "value", "000000")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "pdfFooterString"),array("sql-column", "value", "@@CURRENT_PAGE / @@TOTAL_PAGES")))),
					array(array("type" => "sql", "sql" => "UPDATE @sqlrpt_setup SET name=REPLACE(name,'xlsDef', 'xls') WHERE name like 'xlsDef%'")),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "xlsOddRowAlignLeft"),array("sql-column", "value", "true")))),
					array(array("type" => "insert", "name" => "sqlrpt_setup", "items" =>array(array("sql-column", "name", "xlsEvenRowAlignLeft"),array("sql-column", "value", "true")))),
					// Eliminación código Link
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_link DROP PRIMARY KEY")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_link ADD entity_id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY  FIRST")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_link CHANGE link_id link_id_old VARCHAR(32) NOT NULL")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_link ADD link_id INT(10) NOT NULL AFTER report_id")),
					array(array("type" => "sql", "sql" => "UPDATE @sqlrpt_report_link SET link_id = (SELECT entity_id FROM @sqlrpt_link WHERE link_id_old = link_id)")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_link DROP link_id_old")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_link DROP link_id")),
					// Eliminación código Group
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report DROP FOREIGN KEY sqlrpt_report_group_fk")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_group DROP PRIMARY KEY")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_group ADD entity_id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY  FIRST")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report CHANGE group_id group_id_old VARCHAR(32) NOT NULL")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report ADD group_id INT(10) NOT NULL AFTER report_sql")),
					array(array("type" => "sql", "sql" => "UPDATE @sqlrpt_report SET group_id = (SELECT entity_id FROM @sqlrpt_group WHERE group_id_old = group_id)")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report DROP group_id_old")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_group DROP group_id")),
					// Eliminación código Filter
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_combo DROP PRIMARY KEY")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_combo ADD entity_id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_combo CHANGE combo_id combo_id_old VARCHAR(32) NOT NULL")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_combo_int CHANGE combo_id combo_id_old VARCHAR(32) NOT NULL")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_combo_text CHANGE combo_id combo_id_old VARCHAR(32) NOT NULL")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_combo_varchar CHANGE combo_id combo_id_old VARCHAR(32) NOT NULL")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_combo ADD combo_id INT(10) NOT NULL AFTER report_id")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_combo_int ADD combo_id INT(10) NOT NULL AFTER type_id")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_combo_text ADD combo_id INT(10) NOT NULL AFTER type_id")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_combo_varchar ADD combo_id INT(10) NOT NULL AFTER type_id")),
					array(array("type" => "sql", "sql" => "UPDATE @sqlrpt_report_combo SET combo_id = (SELECT entity_id FROM @sqlrpt_combo WHERE combo_id_old = combo_id)")),
					array(array("type" => "sql", "sql" => "UPDATE @sqlrpt_combo_int SET combo_id = (SELECT entity_id FROM @sqlrpt_combo WHERE combo_id_old = combo_id)")),
					array(array("type" => "sql", "sql" => "UPDATE @sqlrpt_combo_text SET combo_id = (SELECT entity_id FROM @sqlrpt_combo WHERE combo_id_old = combo_id)")),
					array(array("type" => "sql", "sql" => "UPDATE @sqlrpt_combo_varchar SET combo_id = (SELECT entity_id FROM @sqlrpt_combo WHERE combo_id_old = combo_id)")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_combo DROP combo_id_old")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_combo_int DROP combo_id_old")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_combo_text DROP combo_id_old")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_combo_varchar DROP combo_id_old")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_combo DROP combo_id")),
					// Eliminación código Report 
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_chart_series DROP FOREIGN KEY  sqlrpt_chart_series_fk")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_role DROP FOREIGN KEY  sqlrpt_permsion_reports_fk")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report DROP PRIMARY KEY")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report ADD entity_id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_chart_series CHANGE report_id report_id_old VARCHAR(32) NOT NULL")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_cron_log CHANGE report_id report_id_old VARCHAR(32) NOT NULL")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_int CHANGE report_id report_id_old VARCHAR(32) NOT NULL")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_text CHANGE report_id report_id_old VARCHAR(32) NOT NULL")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_varchar CHANGE report_id report_id_old VARCHAR(32) NOT NULL")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_combo CHANGE report_id report_id_old VARCHAR(32) NOT NULL")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_link CHANGE report_id report_id_old VARCHAR(32) NOT NULL")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_role CHANGE report_id report_id_old VARCHAR(32) NOT NULL")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_chart_series ADD report_id INT(10) NOT NULL AFTER id")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_cron_log ADD report_id INT(10) NOT NULL AFTER created_at")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_int ADD report_id INT(10) NOT NULL AFTER type_id")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_text ADD report_id INT(10) NOT NULL AFTER type_id")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_varchar ADD report_id INT(10) NOT NULL AFTER type_id")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_combo ADD report_id INT(10) NOT NULL AFTER id")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_link ADD report_id INT(10) NOT NULL AFTER id")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_role ADD report_id INT(10) NOT NULL AFTER id")),
					array(array("type" => "sql", "sql" => "UPDATE @sqlrpt_chart_series SET report_id = (SELECT entity_id FROM @sqlrpt_report WHERE report_id_old = report_id)")),
					array(array("type" => "sql", "sql" => "UPDATE @sqlrpt_cron_log SET report_id = (SELECT entity_id FROM @sqlrpt_report WHERE report_id_old = report_id)")),
					array(array("type" => "sql", "sql" => "UPDATE @sqlrpt_report_int SET report_id = (SELECT entity_id FROM @sqlrpt_report WHERE report_id_old = report_id)")),
					array(array("type" => "sql", "sql" => "UPDATE @sqlrpt_report_text SET report_id = (SELECT entity_id FROM @sqlrpt_report WHERE report_id_old = report_id)")),
					array(array("type" => "sql", "sql" => "UPDATE @sqlrpt_report_varchar SET report_id = (SELECT entity_id FROM @sqlrpt_report WHERE report_id_old = report_id)")),
					array(array("type" => "sql", "sql" => "UPDATE @sqlrpt_report_combo SET report_id = (SELECT entity_id FROM @sqlrpt_report WHERE report_id_old = report_id)")),
					array(array("type" => "sql", "sql" => "UPDATE @sqlrpt_report_link SET report_id = (SELECT entity_id FROM @sqlrpt_report WHERE report_id_old = report_id)")),
					array(array("type" => "sql", "sql" => "UPDATE @sqlrpt_report_role SET report_id = (SELECT entity_id FROM @sqlrpt_report WHERE report_id_old = report_id)")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_chart_series DROP report_id_old")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_cron_log DROP report_id_old")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_int DROP report_id_old")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_text DROP report_id_old")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_varchar DROP report_id_old")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_combo DROP report_id_old")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_link DROP report_id_old")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report_role DROP report_id_old")),
					array(array("type" => "sql", "sql" => "ALTER TABLE @sqlrpt_report DROP report_id"))
					
					
					
			);
		}
		 
		return $instructions;
		
	}
    
    public function gtn($tableName){
    	return Mage::getSingleton('core/resource')->getTableName($tableName);
    }
    
    public function run($sql,$printError){
    	$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
    	Mage::log(get_class($this).'.'.__FUNCTION__.' Sql: '.$sql, Zend_Log::INFO,'sqlreport.log');
    	try{
    		$connection->query($sql);
    		return true;
    	}catch (Exception  $err){
    		Mage::log(get_class($this).'.'.__FUNCTION__.' Error: '.$err->getMessage(), Zend_Log::ERR,'sqlreport.log');
    		if($printError) $this->printMessage("error",$err->getMessage()."<br/>".$sql);
			return false;
		}
    }

    public function runDbSchemaUpgrade ($fromVersion)
    {
    	$continue=false;
    	$instructions = $this->getDbSchema($fromVersion);
    	
        foreach ($instructions as $instruction) {
        	try{
        		Mage::log(get_class($this).'.'.__FUNCTION__.' Instruccion: '.implode("; ", $instruction), Zend_Log::INFO,'sqlreport.log');
        	} catch (Exception $e) {
                Mage::logException($e);
            }
            switch ($instruction['type']) {
                case 'table':
                    $keys = array();
                    $columns = array();

                    foreach ($instruction['items'] as $item) {
                        switch ($item[0]) {
                            case 'sql-column':
                                $columns[] = '`'.$item[1].'` '.$item[2];
                                break;
                            case 'key':
                                $keys[] = $item[1] .' (`'.$item[2].'`)';
                                break;
                            case 'constraint':
                            	$keys[] = 'CONSTRAINT `'.$item[1] .'` FOREIGN KEY (`'.$item[2].'`) REFERENCES `'.$this->gtn($item[3]).'` (`'.$item[4].'`)';
                            	break;
                            	
                        }
                    }
                    $tableDetails = implode(",",array_merge($columns,$keys));
                    $sql = "DROP TABLE IF EXISTS `{$this->gtn($instruction['name'])}`;\n";
                    $sql .="CREATE TABLE `{$this->gtn($instruction['name'])}` (".$tableDetails.") ;";
                    try {
                        $continue=$this->run($sql,true);
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                    break;
                case 'sql-column-add':
                    try {
                        $continue = $this->run("
                        ALTER TABLE `{$this->gtn($instruction['table'])}` ADD COLUMN `{$instruction['name']}` {$instruction['params']}",true);
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                    break;
                case 'sql-column-delete':
                    try{
                    	$continue = $this->run("
                        ALTER TABLE `{$this->gtn($instruction['table'])}` DROP COLUMN `{$instruction['name']}`",true);
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                    break;
                case 'index':
                    try {
                        $columns = implode(',',$instruction['on']);
                        $continue = $this->run("
                            {$instruction['name']} ON `{$this->gtn($instruction['table'])}` ({$columns})
                        ",true);
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                    break;
                 case 'insert':
                 	
                    	$keys = array();
                    	$columns = array();
                    	$values = array();
                    
                    	foreach ($instruction['items'] as $item) {
                    		switch ($item[0]) {
                    			case 'sql-column':
                    				$columns[] = '`'.$item[1].'`';
                    				$values[] = (gettype($item[2])!="string")?$item[2]: "'".$item[2]."'";
                    				break;
                    				 
                    		}
                    	}
                    	$tableDetails = implode(",",array_merge($columns,$keys));
                    	$tableValues = implode(",",array_merge($values,$keys));
                    	$sql ="INSERT INTO `{$this->gtn($instruction['name'])}` (".$tableDetails.") VALUES (".$tableValues.");";
                    	try {
                    		$continue=$this->run($sql,true);
                    	} catch (Exception $e) {
                    		Mage::logException($e);
                    	}
                    	break;
                case 'sql-column-change':
                    		try{
                    			$continue = $this->run("
                    					ALTER TABLE `{$this->gtn($instruction['table'])}` CHANGE {$instruction['oldname']} {$instruction['newname']} {$instruction['newtype']}".";",true);
                    			} catch (Exception $e) {
                    			Mage::logException($e);
                    	}
                    	break;
                case 'error':
                	try {
                        $continue = $this->run("sql error",true);
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                    break;
                case 'delete':

                    	$where = array();
                    	foreach ($instruction['items'] as $item) {
                    		switch ($item[0]) {
                    			case 'sql-column':
                    				$value = (gettype($item[2])!="string")?$item[2]: "'".$item[2]."'";
                    				$where[] = $item[1]." = ".$value;
                    				break; 
                    		}
                    	}
                    	$where_clausure = implode(" AND ",$where);
                    	$sql ="DELETE FROM ".$this->gtn($instruction['name'])." WHERE ".$where_clausure;
                    	try {
                    		$continue=$this->run($sql,true);
                    	} catch (Exception $e) {
                    		Mage::logException($e);
                    	}
                    	
                    	
                	break;
                case 'sql':	try {
                				$sql = $instruction['sql'];
                				$sql = str_replace("@",Mage::getConfig()->getTablePrefix() ,$sql);
                				$continue=$this->run($sql,true);
                			} catch (Exception $e) {
                				Mage::logException($e);
                			}
                	break;
            }
            
			/*if(!$continue){
				
				$this->resetDb($fromVersion);
				if($fromVersion=="0.0.0"){
					$this->printMessage("error","Instalation plugin Sql2Report faliled");
				}else{
					$this->printMessage("error","Upgrade plugin Sql2Report from ".$fromVersion." to ".$this->versionSql2Report." faliled");
				}
				return false;
			}*/
        }
        Mage::log(get_class($this).'.'.__FUNCTION__.' Upgrade finish estatus : '.$continue, Zend_Log::INFO,'sqlreport.log');
        return true;
        
        
    }
    
    public function resetDb ($fromVersion)
    {
        foreach ($this->getDbSchema($fromVersion) as $instruction) {
            switch ($instruction['type']) {
                case 'table':
                    $sql = "DROP TABLE IF EXISTS `{$this->gtn($instruction['name'])}`;\n";
                    $return = $this->run($sql,false);
                    break;
                case 'sql-column':
                    try {
                        $return = $this->run("
                            ALTER TABLE `{$this->gtn($instruction['table'])}` DROP COLUMN `{$instruction['name']}`
                        ",false);
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                    break;
            }
        }
    }
    
    public function importXML($filename){
  		$estadoFunction = true;
    	$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
    	$xmlObj = new Varien_Simplexml_Config($filename);
    	$xmlData = $xmlObj->getNode();
    	$connection->beginTransaction();
    	foreach ($xmlData as $tabla=>$columnas){
    		$dataInsert = array();
    		foreach ($columnas as $columnName=>$columnValue){
    			$dataInsert[$columnName]=(string)$columnValue;
    		}
    		try{
    			$connection->insert($this->gtn($tabla),$dataInsert);
    		} catch (Exception $e) {
    			$estadoFunction = false;
    			$this->printMessage("warning","Not import data into table ".$tabla." data:".var_dump($dataInsert)." ERROR:".$e->getMessage());
    		}
    		
    	}
    	$connection->commit();
    	return $estadoFunction;
    }
    
    public function importJSON($filename){
    	$estado = array();

    	$filecontect = file_get_contents ($filename);
    	$data = json_decode($filecontect, TRUE);

    	$groupmanager = Mage::helper('sqlreport/GroupManager');
    	$combomanager = Mage::helper('sqlreport/ComboManager');
    	$reportmanager = Mage::helper('sqlreport/ReportManager');
    	$linkmanager = Mage::helper('sqlreport/LinkManager');
    	$group_description="";
    	$group_id="";
    	$group_order= 0;
    	$group;
    	$links = array();
    	$filtros = array();
    	foreach ($data as $dataImport) {
    		switch ($dataImport['object_type']) {
    			case 'group':
    				try{
	    				$group = $groupmanager->getGroupByDescription($dataImport['description']);
	    				if(false==$group){
	    					$group_order = $groupmanager->getLasOrder() + 1;
	    					$result = $groupmanager->createGroup($dataImport['description'],$group_order);
	    					if($result instanceof Serpini_Sqlreport_Model_Group){
	    						$group = $result;
	    						$estado[] = array("type" => "success-msg",
	    								"msg" => "Group ".$dataImport['description']." imported");
	    					}else{
	    						$estado[] = array("type" => "error-msg",
	    										  "msg" => "Error importing group  ".$dataImport['description'].":".$result,
	    										  "object_type" => $dataImport['object_type']);
	    					}
	    				}
    				}catch(Exception $err){
    					$estado[] = array("type" => "error-msg",
    							"msg" => "Error importing group :".$err->getMessage(),
    							"object_type" => $dataImport['object_type']);
    				}
    				break;
    			case 'combo':
    				$result= $combomanager->comboExists("",$dataImport['parameter']);
    				if(false!=$result){
    					$filtros[$dataImport['combo_id']]=$result;
    					$estado[] = array("type" => "warning-msg",
    								"msg" => "The filter  ".$dataImport['title']." (".$dataImport['parameter'].") already exists, The filter will not be imported",
    								"object_type" => $dataImport['object_type']);

    				}else{
    					$result=$combomanager->addNewCombo($dataImport['title'],$dataImport['parameter'],$dataImport['type'],$dataImport['atributes']);
    					if($result instanceof Serpini_Sqlreport_Model_Combo){
    						$filtros[$dataImport['combo_id']]=$result->getId();
    						$estado[] = array("type" => "success-msg",
    								"msg" => "Filter ".$dataImport['title']." (".$dataImport['parameter'].") imported");
    					}else{
    						$estado[] = array("type" => "error-msg",
    								"msg" => "Error importing filter  ".$dataImport['title']."(".$dataImport['combo_id'].") :".$result,
    								"object_type" => $dataImport['object_type']);
    					}
    				}
    				break;
    			case 'report':
    				// Recorremos el array de resultados, si hay algún error no se inserta
    				$hayerror = false;
    				foreach ($estado as $resu){
    					if($resu['type']=="error-msg"){
    						$hayerror=true;
    					}
    				}
    				if(!$hayerror){
    					if($reportmanager->reportExists($dataImport['title'],'edit')){
    						$estado[] = array("type" => "warning-msg",
    								"msg" => "The report  ".$dataImport['title']." (".$dataImport['report_id'].") already exists, The report will not be imported",
    								"object_type" => $dataImport['object_type']);	  
    					}else{
    						// Reasociamos los combos que vienen con los que tenemos en nuestro sistema
    						$listaFiltros = $dataImport['combos'];
    						foreach ($filtros as $codeImport=>$codeSystem){
    							$listaFiltros = str_replace($codeImport, $codeSystem, $listaFiltros);
    						}
    						$result=$reportmanager->addNewReport($dataImport['report_id'],$dataImport['title'],$dataImport['report_sql'],$group->getId(),$dataImport['atributes'],$listaFiltros,$dataImport['chart_series'],$links);
    						 if($result instanceof Serpini_Sqlreport_Model_Report){
    							$estado[] = array("type" => "success-msg",
    											  "msg" => "Report  ".$dataImport['title']." imported",
    											  "object_type" => $dataImport['object_type'],
    											  "report_id"=>$result->getId(),
    											  "description"=>$dataImport['title'],
    									          "group_description"=>$group->getDescription(),
    											  "group_id"=>$group->getId(),
    											  "group_order" => $group->getOrden()
    							);
    						}else{
    							$estado[] = array("type" => "error-msg",
    									"msg" => "Error importing report  ".$dataImport['title']." (".$dataImport['report_id'].") :".$result,
    									"object_type" => $dataImport['object_type']);
    						}
    						
    					}
    					
    				}else{
    					$estado[] = array("type" => "error-msg",
    							"msg" => "There are some error importing, please solve it to import report ",
    							"object_type" => $dataImport['object_type']);
    				}
    				break;
    			case 'link':
    				if($linkmanager->linkExists($dataImport['description'],$dataImport['url'],$dataImport['type'])){
    					$estado[] = array("type" => "warning-msg",
    								"msg" => "The link  ".$dataImport['description']." (".$dataImport['id'].") already exists, The link will not be imported",
    								"object_type" => $dataImport['object_type']);
						$link = $linkmanager->getLinkByData($dataImport['description'],$dataImport['url'],$dataImport['type']);
    					
    					if("TD"==$dataImport['type']){
    						$link->setColumn($dataImport['column']);
    					}else{
    						foreach ($dataImport['variables'] as $key => $value){
								$link->addVariable($dataImport['variables'][$key][0],$dataImport['variables'][$key][1]);
							}
    					}
    					array_push($links,$link);
    				}else{
    					$result=$linkmanager->createLink($dataImport['description'],$dataImport['url'],$dataImport['type']);
    					if($result instanceof Serpini_Sqlreport_Model_Link){
    						if("TD"==$dataImport['type']){
    							$result->setColumn($dataImport['column']);
    						}else{
	    						foreach ($dataImport['variables'] as $key => $value){
									$result->addVariable($dataImport['variables'][$key][0],$dataImport['variables'][$key][1]);
								}
    						}
    						array_push($links,$result);
    						
    						$estado[] = array("type" => "success-msg",
    								"msg" => "Link ".$dataImport['description']." (".$dataImport['id'].") imported");
    					}else{
    						$estado[] = array("type" => "error-msg",
    								"msg" => "Error importing link  ".$dataImport['description']."(".$dataImport['id'].") :".$result,
    								"object_type" => $dataImport['object_type']);
    					}
    				}
    				break;
    		}
    	}
    	$combomanager->resetComboList();
    	$linkmanager->resetLinkList();
    	return $estado;
    	
    }
    
    public function printMessage($type,$message){
    	echo  "<ul class=\"messages\"><li class=\"".$type."-msg\"><ul><li>".$message."</li></ul></li></ul>";
    }
  

}
