<?php

//namespace backboneit\image\operations;

//use backboneit\image\Image as Image;

abstract class PaletteImageOperation extends ImageOperation {

	protected function __construct() {
		parent::__construct();
	}
	
	protected function prepare($blnModifiesOriginal = true) {
		$objOriginal = parent::prepare(false);
		
		if(!$objOriginal->isPaletteImage()) {
			return $objOriginal->toPaletteImage();
			
		} elseif($blnModifiesOriginal && $this->isOriginalImmutable()) {
			return clone $objOriginal;
			
		} else {
			return $objOriginal;
		}
	}
	
}