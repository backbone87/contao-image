<?php

//namespace backboneit\image\operations;

//use backboneit\image\Image as Image;

abstract class ImageOperation {
	
	protected $blnOriginalImmutable;
	
	protected $objResult;
	
	
	
	protected function __construct($blnOriginalImmutable = true) {
		$this->setOriginalImmutable($blnOriginalImmutable);
	}
	
	
	
	public function setOriginalImmutable($blnOriginalImmutable = true) {
		$this->blnOriginalImmutable = $blnOriginalImmutable;
	}
	
	public function isOriginalImmutable() {
		return $this->blnSourceImmutable;
	}
	
	public function hasResult() {
		return $this->objResult && is_resource($this->objResult->getResource());
	}
	
	public function getResult() {
		return $this->hasResult() ? $this->objResult : null;
	}
	
	public function execute(Image $objSource = null) {
		unset($this->objResult);
		
		if($objSource !== null) {
			$objSource->checkResource();
			$this->modifiesSource($objSource) && $this->blnOriginalImmutable && $objSource = clone $objSource;
		}
		
		$this->objResult = $this->perform($objSource);
	}
	
	protected function modifiesSource(Image $objSource = null) {
		return true;
	}
	
	protected abstract function perform(Image $objSource);
	
}