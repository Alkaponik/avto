<?php
class Serpini_Sqlreport_Model_PdfTable_Column extends Serpini_Sqlreport_Model_PdfTable_Cell {
	
	private $_colspan=1;
	
	public function setColspan($value){
		$this->_colspan=$value;
	}
	
	public function getColspan(){
		return $this->_colspan;
	}
}

?>