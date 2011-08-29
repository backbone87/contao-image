<?php

class JPEGFormat extends ImageFormat {

	protected $intQuality;
	
	public function __construct($intQuality = 80) {
		parent::__construct();
		$this->setQuality($intQuality);
	}
	
	public function getQuality() {
		return $this->intQuality;
	}
	
	public function setQuality($intQuality) {
		$this->intQuality = min(max(intval($intQuality), 0), 100);
	}
	
	public function getBinary(Image $objImage) {
		return parent::getBinary($objImage, $this->intQuality);
	}
	
	public function getStoreFunction() {
		return 'imagejpeg';
	}
	
	public function getLoadFunction() {
		return 'imagecreatefromjpeg';
	}
	
}
