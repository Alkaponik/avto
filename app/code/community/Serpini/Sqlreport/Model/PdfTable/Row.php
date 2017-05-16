<?php

class Serpini_Sqlreport_Model_PdfTable_Row {
	
	protected $_font;
	protected $_fontSize=10;
	protected $_cols;
	protected $_autoHeight=true;
	protected $_width;
	protected $_height;
	protected $_border=array();
	protected $_padding=array();
	protected $_cellPadding=array(0,0,0,0);
	
	private $_hasPageBreak;
	private $_forceUniformColumnWidth=false;
	
	/**
	 * Number of Columns in this Row
	 *
	 * @var int
	 */
	public $NumColumns;
	
	/**
	 * Add Cell Padding
	 *
	 * @param array[top,right,bottom,left] $values
	 */
	function setCellPaddings( array $values){
		$this->_cellPadding=$values;
	}
	
	/**
	 * Add Cell Padding
	 *
	 * @param My_Pdf $position
	 * @param int $value
	 */
	public function setCellPadding($position,$value){
		$this->_cellPadding[$position]=$value;
	}
	
	/**
	 * Get Row Columns
	 *
	 * @return array(My_Pdf_Table_Column)
	 */
	public function getColumns(){
		return $this->_cols;
	}
	
	/**
	 * Check if Row has Page-Break
	 *
	 * @return bool
	 */
	public function hasPageBreak(){
		return $this->_hasPageBreak;
	}
	
	/**
	 * Set Page-Break (Before this Row)
	 *
	 * @param bool $val
	 */
	public function setPageBreak($val=true){
		$this->_hasPageBreak=$val;
	}
	
	/**
	 * Set Row Height
	 *
	 * @param int $val
	 */
	public function setHeight($val){
		$this->_autoHeight=false;
		$this->_height=$val;
	}
	
	/**
	 * Get Row Height
	 *
	 * @return int
	 */
	public function getHeight(){
		return $this->_height;
	}
	
	/**
	 * Set Row Width
	 *
	 * @param int $val
	 */
	public function setWidth($val){
		$this->_width=$val;
	}

	/**
	 * Force equal column-with for all columns in this row
	 *
	 * @param bool $val
	 */
	public function forceUniformColumWidth($val=true){
		$this->_forceUniformColumnWidth=$val;
	}
	
	/**
	 * Set row border properties, default properties for all cells in this row
	 *
	 * @param My_Pdf $position
	 * @param Zend_Pdf_Style $style
	 */
	public function setBorder($position,Zend_Pdf_Style $style){
		$this->_border[$position]=$style;
	}
	
	public function getBorder($position){
	if(isset($this->_border[$position])){
			return $this->_border[$position];
		}
		else{
			return false;
		}
	}
	
	/**
	 * Remove Row Border
	 *
	 * @param My_Pdf $position
	 */
	public function removeBorder($position){
		unset($this->_border[$position]);
	}
	
	/**
	 * Set Row Font, default value for all Columns in this Row
	 *
	 * @param Zend_Pdf_Resource_Font $font
	 * @param int $fontSize
	 */
	public function setFont(Zend_Pdf_Resource_Font $font, $fontSize=10){
		$this->_font=$font;
		$this->_fontSize=$fontSize;
	}
	
	/**
	 * Set Row Columns
	 *
	 * @param array(My_Pdf_Table_Column,...) $columns
	 */
	public function setColumns($columns){
		$this->_cols=$columns;

		$this->NumColumns=count($columns);
	}
	
	/**
	 * Delete specified Column in this Row
	 *
	 * @param int $index
	 */
	public function deleteColumn($index){
		unset($this->_cols[$index]);
	}
	
	/**
	 * Insert a new column between existing columns
	 *
	 * @param My_Pdf_Table_Column $col
	 * @param int index of new Coumn $index
	 */
	public function insertColumn(My_Pdf_Table_Column $col,$index){
		$begin=array_slice($this->_cols,0,$index);
		$end=null;
		if(isset($this->_cols[$index]))
			$end=array_slice($this->_cols,$index);
			
		if($end){	
			$end=array_merge(array($col),$end);
		}
		else{
			$end=array($col);
		}
			
		//reset cols
		$this->_cols=array_merge($begin,$end);
		$this->NumColumns=count($this->_cols);
	}
	
	public function __construct(){
		//set default font
		$this->_font=Zend_Pdf_Font::fontWithName ( ZEND_Pdf_Font::FONT_COURIER);
	}
	
	/**
	 * Pre-Render Row
	 * Set Column Widths
	 * Get Column Height
	 * 
	 * @param My_Pdf_Page $page
	 * @param int $posX
	 * @param int $posY
	 * @param int $inContentArea
	 */
	public function preRender(My_Pdf_Page $page,$posX,$posY,$inContentArea=true) {
		//pre-render each cell in row and get height
		$max_height=0;
		
		//get width -> auto column width
		if($this->_width){
			//set given row width
			$max_row_width=$this->_width;
		}
		else{
			//no width given, use available page width
			if($inContentArea){
				$max_row_width=$page->getWidth()-$page->getMargin(Serpini_Sqlreport_Model_PdfTable_Pdf::LEFT)-$page->getMargin(Serpini_Sqlreport_Model_PdfTable_Pdf::RIGHT);
			}
			else{
				$max_row_width=$page->getWidth();
			}
		}
		
		if($this->_forceUniformColumnWidth){
			$uniform_width=$max_row_width/$this->NumColumns;
		}
		else{
			//check if some colums have specific widths
			$fixed_row_width=0;
			$dynamic_columns=0;
			$dynamic_row_width=0;
			foreach($this->_cols as $col) {
				//set font if no font set
				if(!$col->getFont())
					$col->setFont($this->_font,$this->_fontSize);
				$w=$col->getWidth();
				if($w){
					//column with specified width
					$fixed_row_width+=$w;
				}
				else{
					//column with no width specified
					//pre-render to get a first estimation of width
					$col->preRender($page,$posX,$posY,$inContentArea);
					$dynamic_row_width+=$col->getRecommendedWidth();
					$dynamic_columns++;
				}
			}
			
	
			$free_width=$max_row_width-$dynamic_row_width-$fixed_row_width;
			
			if($dynamic_columns>0){
				$uniform_width=($max_row_width-$fixed_row_width)/$dynamic_columns;
				$free_width=$free_width/$dynamic_columns;
			}
			else{
				//nothing to distribute (all fixed colum widths)
				$free_width=-1;
			}
			
			if($free_width<0){
				//force text line break for dynamic rows
				$force_line_breaking=true;
				$free_width=0;
			}
			else{
				$force_line_breaking=false;
			}
		}
		
		//get max column height
		foreach($this->_cols as $col) {
			//set width ->auto-width=true
			if(!$col->getWidth()){
				//calc width for colums without given width /aproximation
				if($this->_forceUniformColumnWidth){
					$width=$uniform_width;
				}
				else{
					if($col->hasImage()){
						//has priority
						$width=$col->getRecommendedWidth()+$free_width;
					}
					elseif ($col->hasText()){
						if($force_line_breaking && $col->getRecommendedWidth()>$uniform_width){
							$width=$uniform_width;
						}
						else{
							$width=$col->getRecommendedWidth()+$free_width;
						}
					}
				}
				$col->setWidth($width);
			}
			if(!$col->getFont())
				$col->setFont($this->_font,$this->_fontSize);
				
			foreach ($this->_cellPadding as $pos=>$val) {
				if(!$col->getPadding($pos))
					$col->setPadding($pos,$val);
			}
			$col->preRender($page,$posX,$posY);
			$height=$col->getRecommendedHeight();
			if($height > $max_height) {
				$max_height = $height;
			}
		}
		
		//get border thickness of top&bottom row
		$this->_height = $max_height;		
	}
	
	/**
	 * Render Row
	 * Set Column- Width for Columns with Colspan>1
	 * 
	 * @param My_Pdf_Page $page
	 * @param int $posX
	 * @param int $posY
	 */
	public function render(My_Pdf_Page $page,$posX,$posY) {
		//get height
		if($this->_autoHeight)
			$this->preRender($page,$posX,$posY);
		
		//render cell (background, border, content)	
		$x = $posX;
		foreach($this->_cols as $key=>$col) {		
			//check colspan;
			if($col->getColspan()>1){
				$width=$col->getWidth();
				//add with of following cell (if cell exists)
				if(isset($this->_cols[($key+1)])){
					$width+=$this->_cols[($key+1)]->getWidth();
					$this->deleteColumn(($key+1));
				}
				$col->setWidth($width);
			}
		}

		//render cols sepeately (without dummy cells)
		foreach($this->_cols as $key=>$col) {
			//set uniform height
			$col->setHeight($this->_height);
			
			//set default borders if not set
			foreach ($this->_border as $pos=>$style) {
				if(!$col->getBorder($pos))
					$col->setBorder($pos,$style);
			}
			
			$col->render($page, $x, $posY);
			$x += $col->getWidth();
		}
	}
	

	/**
	 * Returns the width of a specific border
	 *
	 * @param My_Pdf $position
	 * @return int width
	 */
	public function getBorderLineWidth($position){
		if(isset($this->_border[$position])){
			$style=$this->_border[$position];
			$width=$style->getLineWidth();
		}
		else{
			$width= 0;
		}
		return $width;
	}
	
	public function setBackgroundColor($color){
		foreach ($this->_cols as $col){
			$col->setBackgroundColor(new Zend_Pdf_Color_Html($color));
		}
	}

}

?>
