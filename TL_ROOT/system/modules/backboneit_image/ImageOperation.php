<?php

//namespace backboneit\image\operations;

//use backboneit\image\Image as Image;

abstract class ImageOperation {
	
	protected $objOriginal;
	
	protected $blnOriginalImmutable = true;
	
	protected $objResult;
	
	protected function __construct(Image $objOriginal = null, $blnOriginalImmutable = true) {
		$this->objOriginal = $objOriginal;
		$this->blnOriginalImmutable = $blnOriginalImmutable;
	}
	
	public function setOriginalImage(Image $objOriginal, $blnOriginalImmutable = true) {
		$this->objOriginal = $objOriginal;
		$this->blnOriginalImmutable = $blnOriginalImmutable;
	}
	
	public function getOriginalImage() {
		return $this->objOriginal;
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
	
	public function execute() {
		$this->objResult = $this->perform($this->prepare($this->modifiesOriginal()));
	}
	
	protected function prepare($blnModifiesOriginal = true) {
		$this->objOriginal->checkResource();
		
		unset($this->objResult);
		
		if($blnModifiesOriginal && $this->blnOriginalImmutable) {
			return clone $this->objOriginal;
			
		} else {
			return $this->objOriginal;
		}
	}
	
	protected function modifiesOriginal() {
		return true;
	}
	
	protected abstract function perform(Image $objSource);
	
}