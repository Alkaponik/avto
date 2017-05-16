<?php
class Serpini_Sqlreport_Model_PdfTable_HeaderRow extends Serpini_Sqlreport_Model_PdfTable_Row {
	
	private $_align;
	private $_vAlign;
	private $_color;
	
	
	public function setAlignment($align){
		$this->_align=$align;
	}
	public function setVAlignment($align){
		$this->_vAlign=$align;
	}
	
	public function setColor(Zend_Pdf_Color $color){
		$this->_color=$color;
	}
	
	public function __construct($labels=array()){
		
		$cols=null;
		foreach ($labels as $label) {
			$col=new Serpini_Sqlreport_Model_PdfTable_Column();
			$col->setText($label);
			// set default background color
			$col->setBackgroundColor(new Zend_Pdf_Color_Html(Serpini_Sqlreport_Model_PdfTable_Pdf::DEF_TABLE_HEADER_BACKGROUNDCOLOR));
			$cols[]=$col;
		}
		if($cols)
			$this->setColumns($cols);
		
		//set default alignment
		$this->_align=Serpini_Sqlreport_Model_PdfTable_Pdf::CENTER;
		
		//set default borders
		$this->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::TOP, new Zend_Pdf_Style());
		$this->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::BOTTOM, new Zend_Pdf_Style());
		$this->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::LEFT, new Zend_Pdf_Style());
		$this->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::RIGHT, new Zend_Pdf_Style());
		$this->setCellPaddings(array(2,2,2,2));
		
		//set default font
		$this->_font=Zend_Pdf_Font::fontWithName ( ZEND_Pdf_Font::FONT_HELVETICA_BOLD); 
		$this->_fontSize=6;
	}
	
	public function preRender(Serpini_Sqlreport_Model_PdfTable_Page $page,$posX,$posY){
		
		foreach ($this->_cols AS $col){
			//set default font
			if(!$col->getFont())
				$col->setFont($this->_font,$this->_fontSize);
			//set default borders if not set
			foreach ($this->_border as $pos=>$style) {
				if(!$col->getBorder($pos))
					$col->setBorder($pos,$style);
			}
			
			if(!$col->getAlignment())
				$col->setAlignment($this->_align);
			
			$col->setColor($this->color);
		}
		
		parent::preRender($page,$posX,$posY);
	}
}
?>