<?php

abstract class TrueColorImageOperation extends ImageOperation {
	
	protected function __construct(Image $objOriginal = null, $blnOriginalImmutable = true) {
		parent::__construct($objOriginal, $blnOriginalImmutable);
	}
	
	protected function prepare($blnModifiesOriginal = true) {
		$objOriginal = parent::prepare(false);
		
		if(!$objOriginal->isTrueColorImage()) {
			return $objOriginal->toTrueColorImage();
			
		} elseif($blnModifiesOriginal && $this->isOriginalImmutable()) {
			return clone $objOriginal;
			
		} else {
			return $objOriginal;
		}
	}
	
}