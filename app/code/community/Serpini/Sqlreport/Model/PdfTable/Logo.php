<?php

class Serpini_Sqlreport_Model_PdfTable_Logo {
	
	private $_widthLimit  = 100; //half of the page width
	private $_heightLimit = 100; //assuming the image is not a "skyscraper"
	private $_top = 20;
	private $_left = 20;
	private $_image = null;
	
	public function __construct(Zend_Pdf_Resource_Image $image){
		$this->_image=$image;
	}
	
	public function setWidthLimit($width){
		$this->_widthLimit=$width;
	}
	
	public function setHeightLimit($height){
		$this->_heightLimit=$height;
	}
	
	public function getX(){
		return $this->_top;
	}
	
	public function getY(){
		return $this->_left;
	}
	
	public function getImage(){
		return $this->_image;
	}
	
	public function getWidth(){
		if(null!=$this->_image){
			$width       = $this->_image->getPixelWidth();
			
			$height      = $this->_image->getPixelHeight();
			$ratio = $width / $height;
			if ($ratio > 1 && $width > $this->_widthLimit) {
				$width  = $this->_widthLimit;
				$height = $width / $ratio;
			} elseif ($ratio < 1 && $height > $this->_heightLimit) {
				$height = $this->_heightLimit;
				$width  = $height * $ratio;
			} elseif ($ratio == 1 && $height > $this->_heightLimit) {
				$height = $this->_heightLimit;
				$width  = $this->_widthLimit;
			}
			return $width;
		}else{
			return 0;
		}
	}
	
	public function getHeight(){
		if(null!=$this->_image){
			$width       = $this->_image->getPixelWidth();
			$height      = $this->_image->getPixelHeight();
			$ratio = $width / $height;
		if ($ratio > 1 && $width > $this->_widthLimit) {
				$width  = $this->_widthLimit;
				$height = $width / $ratio;
			} elseif ($ratio < 1 && $height > $this->_heightLimit) {
				$height = $this->_heightLimit;
				$width  = $height * $ratio;
			} elseif ($ratio == 1 && $height > $this->_heightLimit) {
				$height = $this->_heightLimit;
				$width  = $this->_widthLimit;
			}
			return $height;
		}else{
			return 0;
		}
	}
	
}