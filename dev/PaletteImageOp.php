<?php

//namespace backboneit\image\operations;

//use backboneit\image\Canvas as Canvas;

abstract class PaletteCanvasOp extends CanvasOp {

	protected function __construct(Canvas $objOriginal = null, $blnOriginalImmutable = true) {
		parent::__construct($objOriginal, $blnOriginalImmutable);
	}
	
	protected function prepare($blnModifiesOriginal = true) {
		$objOriginal = parent::prepare(false);
		
		if(!$objOriginal->isPaletteCanvas()) {
			return $objOriginal->toPaletteCanvas();
			
		} elseif($blnModifiesOriginal && $this->isOriginalImmutable()) {
			return clone $objOriginal;
			
		} else {
			return $objOriginal;
		}
	}
	
}