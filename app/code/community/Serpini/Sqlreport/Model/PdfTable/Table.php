<?php
class Serpini_Sqlreport_Model_PdfTable_Table {
	private $_width;
	private $_autoWidth=true;
	private $_font;
	private $_fontSize;
	private $_headerFont;
	private $_headerFontSize;

	private $_rows;
	private $_headerRow;
	private $_footerRow;
	private $_numColumns;
	private $_pages; 		//spanning pages or this table
	private $_repeatHeader=true;

	/**
	 * Set Table Width
	 *
	 * @param int $val
	 */
	public function setWidth($val){
		$this->_autoWidth=false;
		$this->_width=$val;
	}

	/**
	 * Get Table Width
	 *
	 * @return int
	 */
	public function getWidth(){
		return $this->_width;
	}
	


	public function __construct($numColumns) {
		$this->_numColumns=$numColumns;

		//set fonts
		$this->_font=Zend_Pdf_Font::FONT_COURIER;
		$this->_fontSize=10;
		$this->_headerFont=Zend_Pdf_Font::FONT_COURIER;
		$this->_headerFontSize=12;
	}


	/**
	 * Render Table
	 *
	 * @param Serpini_Sqlreport_Model_PdfTable_Page $page
	 * @param int $posX
	 * @param int $posY
	 * @param int $inContentArea
	 * @return array(Serpini_Sqlreport_Model_PdfTable_Page)
	 */
	public function render(Serpini_Sqlreport_Model_PdfTable_Page $page, $posX,$posY,$inContentArea=true){

		if($this->_headerRow && $this->_rows){
			//set header in front of rows
			$this->_rows=array_merge($this->_headerRow,$this->_rows);
		}elseif ($this->_headerRow){
			//no rows in this table, just the header
			$this->_rows=$this->_headerRow;
		}
		
		if($this->_footerRow && $this->_rows){
			//set header in front of rows
			$this->_rows=array_merge($this->_rows,$this->_footerRow);
		}elseif ($this->_footerRow){
			//no rows in this table, just the header
			$this->_rows=$this->_footerRow;
		}

		if($inContentArea){
			$start_y=$posY + $page->getMargin(Serpini_Sqlreport_Model_PdfTable_Pdf::TOP);
			$max_y=$page->getHeight()- $page->getMargin(Serpini_Sqlreport_Model_PdfTable_Pdf::BOTTOM)- $page->getMargin(Serpini_Sqlreport_Model_PdfTable_Pdf::TOP);
			if($page->hasHeader()){
				$start_y = $start_y+$page->getHeaderHeight($page);
			}
		}
		else{
			$start_y=  $posY;
			$max_y  =  $page->getHeight();
			$posX  -=  $page->getMargin(Serpini_Sqlreport_Model_PdfTable_Pdf::LEFT);
		}
		


		$y=$start_y;
		//prerender
		$this->_preRender($page, $posX,$posY,$inContentArea);
		$lastRow=null;
		foreach($this->_rows as $row) {
			//check current position (height)
			$test=($y+$row->getHeight());
			if(null!=$lastRow){
				if($test>$max_y || $row->hasPageBreak()){
					// add border bottom like last row
					$lastRowTable = $this->_rows[count($this->_rows)-1];
					$primeraCelda = $lastRowTable->getColumns();
					$primeraCelda = $primeraCelda[0];
					$styleBorderBottom = $primeraCelda->getBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::BOTTOM);
					if(false!=$styleBorderBottom){
						$lastRow->setBorder(Serpini_Sqlreport_Model_PdfTable_Pdf::BOTTOM, $styleBorderBottom);
					}
					
				}
				$lastRow->render($page, $posX, $y);
				$y += $lastRow->getHeight()+$lastRow->getBorderLineWidth(Serpini_Sqlreport_Model_PdfTable_Pdf::BOTTOM);
			}
			

			if($test>$max_y || $row->hasPageBreak()){
				//page-break
				$nPage = new Serpini_Sqlreport_Model_PdfTable_Page($page);

				//copy previouse page-settings
				$nPage->setFont($page->getFont(),$page->getFontSize());
				$nPage->setMargins($page->getMargins());

				$page=$nPage;
				$this->_pages[] = $page;
				$y=$page->getMargin(Serpini_Sqlreport_Model_PdfTable_Pdf::TOP);

				if($this->_headerRow && $this->_repeatHeader){
					$header=$this->_rows[0];//pre-rendered header row (is first row)
					$header->render($page, $posX, $y);
					$y += $header->getHeight()+$header->getBorderLineWidth(Serpini_Sqlreport_Model_PdfTable_Pdf::BOTTOM);
				}
			}
			$lastRow = $row;
			//$row->render($page, $posX, $y);
			//$y += $row->getHeight()+$row->getBorderLineWidth(Serpini_Sqlreport_Model_PdfTable_Pdf::BOTTOM);
		}
		
		if(null!=$lastRow){
			$lastRow->render($page, $posX, $y);
		}

		return $this->_pages;
	}

	/**
	 * Add Header Row
	 *
	 * @param Serpini_Sqlreport_Model_PdfTable_Row $row
	 */
	public function  setHeader(Serpini_Sqlreport_Model_PdfTable_Row $row){
		if(!$this->_autoWidth)
			$row->setWidth($this->_width);

		$this->_headerRow[]=$row;
	}
	
	/**
	 * Add Footer Row
	 *
	 * @param Serpini_Sqlreport_Model_PdfTable_Row $row
	 */
	public function  setFooter(Serpini_Sqlreport_Model_PdfTable_Row $row){
		if(!$this->_autoWidth)
			$row->setWidth($this->_width);
	
		$this->_footerRow[]=$row;
	}

	/**
	 * Add Row
	 *
	 * @param Serpini_Sqlreport_Model_PdfTable_Row $row
	 */
	public function addRow(Serpini_Sqlreport_Model_PdfTable_Row $row){
		//add default row properites if non are set (font/color/size,...)
		//set width
		if(!$this->_autoWidth)
			$row->setWidth($this->_width);

		$this->_rows[]=$row;
	}

	/**
	 * Replace specific Row in Table
	 *
	 * @param Serpini_Sqlreport_Model_PdfTable_Row $row
	 * @param int $index
	 */
	public function replaceRow(Serpini_Sqlreport_Model_PdfTable_Row $row,$index){
		if(!$this->_autoWidth)
			$row->setWidth($this->_width);

		$this->_rows[$index]=$row;
	}

	/**
	 * Get all Rows in this Table
	 *
	 * @return array(Serpini_Sqlreport_Model_PdfTable_Rows)
	 */
	public function getRows(){
		return $this->_rows;
	}

	public function __clone(){
		foreach ($this as $key => $val) {
            if (is_object($val) || (is_array($val))) {
                $this->{$key} = unserialize(serialize($val));
            }
        }
	}

	/**
	 * Pre-Render Table
	 *
	 * @param Serpini_Sqlreport_Model_PdfTable_Page $page
	 * @param int $posX
	 * @param int $posY
	 * @param bool $inContentArea
	 */
	private function _preRender(Serpini_Sqlreport_Model_PdfTable_Page $page, $posX,$posY,$inContentArea=true){
		//get auto-colum widths
		$col_widths=array();
		foreach($this->_rows as $row) {
			//check for colspan's
			$new_dummy_cells=array();
			foreach ($row->getColumns() as $idx=>$col) {
				$col_widths[$idx]=$col->getWidth(); //store widht ->for dummy cells
				if($col->getColspan()>1){
					//insert new cell, for each spanning column
					$new_dummy_cells[$idx]=$col;
				}
			}

			//insert dummy cells
			foreach ($new_dummy_cells as $idx=>$col) {
				for($i=1;$i<$col->getColspan();$i++){
					//new col
					$nCol=new Serpini_Sqlreport_Model_PdfTable_Column();
					$nCol->setText('');
					if(isset($col_widths[$idx+1]))
						$nCol->setWidth($col_widths[$idx+1]);

					$row->insertColumn($nCol,$idx+1);
				}
			}

			//pre-render row
			$row->preRender($page, $posX, $posY,$inContentArea);
			$posY += $row->getHeight()+$row->getBorderLineWidth(Serpini_Sqlreport_Model_PdfTable_Pdf::BOTTOM);
		}

		//set max col width
		$max_col_width=array();
		foreach($this->_rows as $row) {
			//get columns max width
			$max_col_width=array();
			foreach ($row->getColumns() as $idx=>$col) {
				$width=$col->getWidth();
				if(!isset($max_col_width[$idx]) || $width>$max_col_width[$idx])
					$max_col_width[$idx]=$width;
			}
		}

		//set uniform column widht for all rows
		foreach($this->_rows as $row) {
			foreach ($row->getColumns() as $idx=>$col) {
				$col->setWidth($max_col_width[$idx]);
			}
		}
	}
	
	public function getHeight($page){
		$height = 0;
		$this->_preRender($page, 0,0,false);
		foreach($this->_rows as $row) {
			//check current position (height)
			$height+=$row->getHeight();
		}
		return $height+10;
	}	
}

?>
