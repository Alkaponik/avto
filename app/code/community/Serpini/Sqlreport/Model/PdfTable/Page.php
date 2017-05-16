<?php
class Serpini_Sqlreport_Model_PdfTable_Page extends Zend_Pdf_Page {
	
	/*
	 * If page contains pagebreaks, pages are stored here
	 */
	private $_pages=array();
	private $_margin;
	private $_defaultStyle;
	private $_header;

	/**
	 * Get Default Page Style
	 *
	 * @return Zend_Pdf_Style
	 */
	public function getDefaultStyle(){
		return $this->_defaultStyle;
	}
	
	/**
	 * Get all pages for this page (page overflows)
	 *
	 * @return array pages
	 */
	public function getPages(){
		if(count($this->_pages)>0){
			return array_merge(array($this),$this->_pages);
		}
		else{
			return false;
		}
	}
	
	/**
	 * Set page margins 
	 * 
	 * @param array(TOP,RIGHT,BOTTOM,LEFT)
	 */
	public function setMargins($margin=array()){
		$this->_margin=$margin;
	}
	
	public function setHeader(Serpini_Sqlreport_Model_PdfTable_Table $_header){
		$this->_header=$_header;
	}
	
	public function hasHeader(){
		if($this->_header){
			return true;
		}else{
			return false;
		}
	}
	
	public function getHeaderHeight($page){
		if(!$this->_header){
			return 0;
		}else{
			return $this->_header->getHeight($page);
		}
	}
	
	/**
	 * Get Page Width
	 *
	 * @param bool $intContentArea
	 * @return int
	 */
	public function getWidth($intContentArea=false){
		$width=parent::getWidth();
		if($intContentArea){
			$width-=$this->_margin[Serpini_Sqlreport_Model_PdfTable_Pdf::LEFT];
			$width-=$this->_margin[Serpini_Sqlreport_Model_PdfTable_Pdf::RIGHT];
		}
		
		return $width;
	}
	
	/**
	 * Get a Page margin
	 *
	 * @param Serpini_Sqlreport_Model_PdfTable_Pdf::Position $position
	 * @return int margin
	 */
	public function getMargin($position){
		return $this->_margin[$position];
	}
	
	/**
	 * Get Page Margins
	 *
	 * @return array(TOP,RIGHT,BOTTOM,LEFT)
	 */
	public function getMargins(){
		return $this->_margin;
	}
	
	/**
	 * Set Page Font
	 *
	 * @param Zend_Pdf_Resource_Font $font
	 * @param int $fontSize
	 */
	public function setFont(Zend_Pdf_Resource_Font $font, $fontSize=10){
		$this->_font=$font;
		$this->_fontSize=$fontSize;
		parent::setFont($font,$fontSize);
	}
	
	public function __construct($param1, $param2 = null, $param3 = null){
		parent::__construct ( $param1, $param2, $param3 );
		
		$style=new Zend_Pdf_Style();
		$style->setLineColor(new Zend_Pdf_Color_Html("#000000"));
		$style->setFillColor(new Zend_Pdf_Color_Html("#000000"));
		$style->setLineWidth(0.5);
		
		$font = Zend_Pdf_Font::fontWithName ( Zend_Pdf_Font::FONT_COURIER ); 		
		$style->setFont($font,10);
		
		$style->setLineDashingPattern(Zend_Pdf_Page::LINE_DASHING_SOLID);
		
		$this->_defaultStyle=$style;
		$this->setStyle($style);
	}
	
	/**
	 * Add a table to a page
	 *
	 * @param Serpini_Sqlreport_Model_PdfTable_Pdf_Table $table
	 * @param int $posX
	 * @param int $posY
	 */
	public function addTable(Serpini_Sqlreport_Model_PdfTable_Pdf_Table $table,$posX,$posY,$inContentArea=true){
		//render table --> check for new pages
		$pages=$table->render($this,$posX,$posY,$inContentArea);
		if(is_array($pages))
			$this->_pages+=$pages;
	}
	
	/**
	 * Get text properties (width, height, [#lines using $max Width]), and warps lines
	 *
	 * @param string $text
	 * @param int $posX
	 * @param int $posY
	 * @param int $maxWidth
	 */
	public function getTextProperties($text, $maxWidth=null) {
		
		$lines=$this->_textLines($text, $maxWidth);
		
		return array (
			'text_width' => $lines['text_width'],
			'max_width'=> $lines['max_width'],
			'height'=>($this->getFontHeight()*count ( $lines['lines'] )), 
			'lines' => $lines['lines'] 
		);
	}
	
	/**
	 * Draw Line
	 *
	 * @param int $x1
	 * @param int $y1
	 * @param int $x2
	 * @param int $y2
	 * @param bool $inContentArea
	 */
	public function drawLine($x1,$y1,$x2,$y2,$inContentArea=true){
		//move origin
		if($inContentArea){
			$y1 = $this->getHeight()- $y1  - $this->getMargin(Serpini_Sqlreport_Model_PdfTable_Pdf::TOP);
			$y2 = $this->getHeight()- $y2  - $this->getMargin(Serpini_Sqlreport_Model_PdfTable_Pdf::TOP);
			$x1 = $x1  + $this->getMargin(Serpini_Sqlreport_Model_PdfTable_Pdf::LEFT);
			$x2 = $x2  + $this->getMargin(Serpini_Sqlreport_Model_PdfTable_Pdf::LEFT);
		}
		
		parent::drawLine($x1,$y1,$x2,$y2);
	}
	
	/**
	 * Draw Text
	 *
	 * @param string $text
	 * @param int $x1
	 * @param int $y1
	 * @param string $charEncoding
	 * @param bool $inContentArea
	 */
	public function drawText($text,$x1,$y1,$charEncoding="",$inContentArea=true){
		//move origin
		if($inContentArea){
			$y1 = $this->getHeight()- $y1  - $this->getMargin(Serpini_Sqlreport_Model_PdfTable_Pdf::TOP);
			$x1 = $x1  + $this->getMargin(Serpini_Sqlreport_Model_PdfTable_Pdf::LEFT);
		}else{
			$y1 = $this->getHeight()- $y1;
		}
		
		parent::drawText($text,$x1,$y1,$charEncoding);
	}
	
	
	/**
	 * Draw Rectangle
	 *
	 * @param int $x1
	 * @param int $y1
	 * @param int $x2
	 * @param int $y2
	 * @param string $filltype
	 * @param bool $inContentArea
	 */
	public function drawRectangle($x1,$y1,$x2,$y2,$filltype=null,$inContentArea=true){
		//move origin
		if($inContentArea){
			$y1 = $this->getHeight()- $y1  - $this->getMargin(Serpini_Sqlreport_Model_PdfTable_Pdf::TOP);
			$y2 = $this->getHeight()- $y2  - $this->getMargin(Serpini_Sqlreport_Model_PdfTable_Pdf::TOP);
			$x1 = $x1  + $this->getMargin(Serpini_Sqlreport_Model_PdfTable_Pdf::LEFT);
			$x2 = $x2  + $this->getMargin(Serpini_Sqlreport_Model_PdfTable_Pdf::LEFT);
		}
		
		parent::drawRectangle($x1,$y1,$x2,$y2,$filltype);
	}
	
	public function drawImage( Zend_Pdf_Resource_Image $image,$x1,$y1,$width,$height,$inContentArea=true){
		$y1 = $this->getHeight()- $y1; 
		$x2=$x1+$width;
		$y2=$y1-$height;
		if($inContentArea){
			$y1 = $this->getHeight()- $y1  - $this->getMargin(Serpini_Sqlreport_Model_PdfTable_Pdf::TOP)-$height;
			$x1 = $x1  + $this->getMargin(Serpini_Sqlreport_Model_PdfTable_Pdf::LEFT);
			
			$y2=$y1+$height;
			$x2=$x1+$width;
		}
		parent::drawImage($image,$x1,$y2,$x2,$y1);
	}
	
	/**
	 * Get height of one or more line(s) in with current font and font size.
	 *
	 * @param int $lines number of lines
	 * @param int $extraSpacing spaceing between lines
	 * @return int line height
	 */
	public function getLineHeight($lines = 1, $extraSpacing = 1) {
		return $lines * $this->_fontSize * $this->_font->getLineHeight() / $this->_font->getUnitsPerEm() + $extraSpacing;
	}
	
	
	public function drawTextBlock($text, $x, $y = null, $width = null, $height = null, $align = Serpini_Sqlreport_Model_PdfTable_Pdf::LEFT) {
		if ($width === null) {
			$widht = $x;
			$this->textCursorNewline();
			$x = $this->_cursor_x;
			$y = $this->_cursor_y;
		}
	
		$lines = $this->_wrapTextExtend($text, $width);
	
		if ($height !== null && $this->getLineHeight(count($lines)) > $height) {
			throw new Zend_Pdf_Exception('height overflow');
		}
		$line_height = $this->getLineHeight();
		foreach ($lines as $k => $line) {
			switch($align) {
				case Serpini_Sqlreport_Model_PdfTable_Pdf::JUSTIFY:
					if (count($line['words']) < 2 || $k == count($lines) - 1) {
						$this->drawText(implode(' ', $line['words']), $x, $y);
						break;
					}
					$space_width = ($width - array_sum($line['word_lengths'])) / (count($line['words']) - 1);
					$pos = $x;
					foreach ($line['words'] as $k => $word) {
						$this->drawText($word, $pos, $y);
						$pos += $line['word_lengths'][$k] + $space_width;
					}
					break;
				case Serpini_Sqlreport_Model_PdfTable_Pdf::CENTER:
					$this->drawText(implode(' ', $line['words']), $x + ($width - $line['total_length']) / 2, $y);
					break;
				case Serpini_Sqlreport_Model_PdfTable_Pdf::RIGHT:
					$this->drawText(implode(' ', $line['words']), $x + $width - $line['total_length'], $y);
					break;
				case Serpini_Sqlreport_Model_PdfTable_Pdf::LEFT:
				default:
					$this->drawText(implode(' ', $line['words']), $x, $y);
					break;
			}
			$y -= $line_height;
		}
	}
	
	/**
	 * Start a newline. The x position is reset and line height is added to the y position
	 *
	 * @return Zend_Pdf_Page fluid interface
	 */
	public function textCursorNewline() {
		$this->setTextCursor($this->_cursor_original_x);
		$this->textCursorMove(null, -$this->getLineHeight());
		return $this;
	}
	
	/**
	 * Get Font Height
	 *
	 * @return int
	 */
	public function getFontHeight(){
		$line_height=$this->getFont()->getLineHeight();
		$line_gap=$this->getFont()->getLineGap();
		$em=$this->getFont()->getUnitsPerEm();
		$size=$this->getFontSize();
		return ($line_height-$line_gap)/$em*$size;
	}
	
		/**
	 * Returns the with of the text
	 *
	 * @param string $text
	 * @return int $width
	 */
	private function _getTextWidth($text) {
		
		$glyphs = array ();
		$em = $this->_font->getUnitsPerEm ();
		
		//get glyph for each character
		foreach ( range ( 0, strlen ( $text ) - 1 ) as $i ) {
			$glyphs [] = @ord ( $text [$i] );
		}
		
		$width = array_sum ( $this->_font->widthsForGlyphs ( $glyphs ) ) / $em * $this->_fontSize;
		
		return $width;
	}
	
	/**
	 * Wrap text according to max width
	 *
	 * @param string $text
	 * @param int $maxWidth
	 * @return array lines
	 */
	private function _wrapText($text,$maxWidth){
		$x_inc = 0;
		$curr_line = '';
		$words = explode ( ' ', trim ( $text ) );
		$space_width = $this->_getTextWidth ( ' ' );
		foreach ( $words as $word ) {
			//no new line found
			$width = $this->_getTextWidth ( $word );
			
			if (isset ( $maxWidth ) && ($x_inc + $width) <= $maxWidth) {
				//add word to current line
				$curr_line .= ' '.$word;
				$x_inc += $width + $space_width;
			} else {
				//store current line
				if (strlen( trim($curr_line,"\n") )>0)
					$lines [] = trim($curr_line);

				//new line
				$x_inc = 0; //reset position
				$curr_line = array (); //reset curr line
				//add word
				$curr_line = $word;
				$x_inc += $width + $space_width;
			}
		}
		
		//last line
		if (strlen( trim($curr_line,"\n") )>0) {
			$lines [] = trim($curr_line);	
		}
		
		return $lines;
	}
	
	/**
	 * Helper method to wrap text to lines. The wrapping is done at whitespace if the text gets longer
	 * as $width.
	 *
	 * @param string $text the text to wrap
	 * @param int $width
	 * @param int $initial_line_offset x offset for start position in first line
	 * @return array array with lines as array('words' => array(...), 'word_lengths' => array(...), 'total_length' => <int>)
	 */
	protected function _wrapTextExtend($text, $width, $initial_line_offset = 0) {
		$lines = array();
		$line_init = array(
				'words'        => array(),
				'word_lengths' => array(),
				'total_length' => 0
		);
		$line = $line_init;
		$line['total_length'] = $initial_line_offset;
	
		$text = preg_split('%[\n\r ]+%', $text, -1, PREG_SPLIT_NO_EMPTY);
		$space_length = $this->widthForString(' ');
		foreach ($text as $word) {
			$word_length = $this->widthForString($word);
			if ($word_length > $width) {
				if ($line['words']) {
					$lines[] = $line;
				}
				$lines[] = array(
						'words'        => array($word),
						'word_lengths' => array($word_length),
						'total_length' => array($word_length)
				);
				$line = $line_init;
				continue;
			}
			if ($line['total_length'] + $word_length > $width) {
				$line['total_length'] -= $space_length;
				$lines[] = $line;
				$line = $line_init;
			}
			$line['words'][]        = $word;
			$line['word_lengths'][] = $word_length;
			$line['total_length']  += $word_length + $space_length;
		}
		if ($line) {
			$line['total_length'] -= $space_length;
			$lines[] = $line;
		}
	
		return $lines;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $text
	 * @param int $maxWidth (optional, if not set (auto width) the max width is set by reference)
	 * @return array line(text);
	 */
	private function _textLines($text,$maxWidth=null){
		$trimmed_lines=array();
		$textWidth=0;
		$line_width=0;
		
		$lines=explode("\n",$text);
		$max_line_width=0;
		foreach ( $lines as $line ) {
			if(strlen($line)<=0) continue;
			$line_width=$this->_getTextWidth($line);
			if($maxWidth>0 && $line_width>$maxWidth){
				$new_lines=$this->_wrapText($line,$maxWidth);
				$trimmed_lines+=$new_lines;
				
				foreach ($new_lines as $nline) {
					$line_width=$this->_getTextWidth($nline);
					if($line_width>$max_line_width)
						$max_line_width=$line_width;
				}
			}
			else{
				$trimmed_lines[]=$line;
			}
			if($line_width>$max_line_width)
				$max_line_width=$line_width;
		}
		
		//set actual width of line
		if(is_null($maxWidth))
			$maxWidth=$max_line_width;
		
		$textWidth=$max_line_width;
		
		
		
		return array('lines'=>$trimmed_lines,'text_width'=>$textWidth,'max_width'=>$maxWidth);
	}
	
	
//	
//	private function getWordWidth($word) {
//		$font = $this->getFont ();
//		$font_size = $this->getFontSize ();
//		$em = $font->getUnitsPerEm ();
//		
//		$glyphs = array ();
//		//get glyph for each character
//		
//		foreach ( range ( 0, strlen ( $word ) - 1 ) as $i ) {
//			$glyphs [] = @ord ( $word [$i] );
//		}
//		
//		$width = array_sum ( $font->widthsForGlyphs ( $glyphs ) ) / $em * $font_size;
//		return $width;
//	}
//	
//	
//	
//	public function getFontHeightInPixel() {
//		$font = $this->getFont ();
//		$lineheight = ($font->getLineHeight ()) / $font->getUnitsPerEm () * $this->getFontSize ();
//		return $lineheight;
//	}
//	
//	public function getFontLineGapInPixel() {
//		$font = $this->getFont ();
//		$linegap = ($font->getLineGap ()) / $font->getUnitsPerEm () * $this->getFontSize ();
//		return $linegap;
//	}
//	
//	}
}

?>
