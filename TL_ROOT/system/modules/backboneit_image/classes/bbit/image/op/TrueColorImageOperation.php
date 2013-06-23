<?php

abstract class TrueColorCanvasOp extends CanvasOp {
	
	protected function __construct(Canvas $objOriginal = null, $blnOriginalImmutable = true) {
		parent::__construct($objOriginal, $blnOriginalImmutable);
	}
	
	protected function prepare($blnModifiesOriginal = true) {
		$objOriginal = parent::prepare(false);
		
		if(!$objOriginal->isTrueColorCanvas()) {
			return $objOriginal->toTrueColorCanvas();
			
		} elseif($blnModifiesOriginal && $this->isOriginalImmutable()) {
			return clone $objOriginal;
			
		} else {
			return $objOriginal;
		}
	}
	
}