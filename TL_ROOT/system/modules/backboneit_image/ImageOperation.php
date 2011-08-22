<?php

abstract class ImageOperation {
	
	/**
	 * the original image
	 */
	private $objOriginal;
	
	/**
	 * whether or not, the original image is allowed to be modified
	 */
	private $blnOriginalImmutable = true;
	
	protected $objIntermediate;
	
	private $objResult;
	
	protected function __construct() {
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
		if(!$this->objOriginal || $this->objOriginal->getRessource()) {
			throw new Exception('ImageOperation->prepare(): Invalid source image.');
		}
		
		unset($this->objResult);
		
//		if($this->objTarget && $this->objTarget->getRessource()) {
//			//nothing todo, all fine
//			
//		} elseif($this->objTargetSize) {
//			$this->objTarget = $this->createEmpty($this->objTargetSize);
//			
//		} else {
//			throw new Exception('ImageOperation->prepare(): No target image or size given.');
//			
//		}
	}
	
	protected abstract function perform();
	
//	protected abstract function createEmpty(Size $objSize);

	
//	private $objTarget;
//	public function setTargetImage(Image $objTarget = null) {
//		$this->objTarget = $objTarget;
//	}
//	
//	public function getTargetImage() {
//		return $this->objTarget;
//	}
//	
//	public function setTargetSize(Size $objSize) {
//		$this->objTargetSize = $objSize;
//	}
//	
//	public function getTargetSize() {
//		return $this->objTargetSize;
//	}

}