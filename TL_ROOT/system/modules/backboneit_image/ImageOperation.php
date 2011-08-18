<?php

abstract class ImageOperation {
	
	private $objTarget;
	
	private $objSource;
	
	private $objResult;
	
	protected function __construct() {
	}
	
	public function setSourceImage(Image $objSource) {
		$this->objSource = $objSource;
	}
	
	public function getSourceImage() {
		return $this->objSource;
	}
	
	public function setTargetImage(Image $objTarget = null) {
		$this->objTarget = $objTarget;
	}
	
	public function getTargetImage() {
		return $this->objTarget;
	}
	
	public function setTargetSize(Size $objSize) {
		$this->objTargetSize = $objSize;
	}
	
	public function setTargetSize() {
		return $this->objTargetSize;
	}
	
	public function hasResult() {
		return $this->objResult && $this->objResult->getRessource();
	}
	
	public function getResult() {
		return $this->hasResult() ? $this->objResult : null;
	}
	
	public function execute() {
		$this->prepare();
		$this->perform();
	}
	
	protected function prepare() {
		if(!$this->objSource || $this->objSource->getRessource()) {
			throw new Exception('ImageOperation->prepare(): Invalid source image.');
		}
		
		unset($this->objResult);
		
		if($this->objTarget && $this->objTarget->getRessource()) {
			//nothing todo, all fine
			
		} elseif($this->objTargetSize) {
			$this->objTarget = $this->createEmpty($this->objTargetSize);
			
		} else {
			throw new Exception('ImageOperation->prepare(): No target image or size given.');
			
		}
	}
	
	protected abstract function perform();
	
	protected abstract function createEmpty(Size $objSize);
	
}