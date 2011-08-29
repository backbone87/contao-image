<?php

class PNG24Format extends PNGFormat {

	public function __construct($intCompression = 5, $intFilter = self::FILTER_PAETH) {
		parent::__construct($intCompression, $intFilter);
	}
	
	public function optimizeFor(Image $objImage, $intAllowedFilter = self::FILTER_ALL) {
		return parent::optimizeFor($objImage->toTrueColorImage(), $intAllowedFilter);
	}
	
	public function getBinary(Image $objImage, $blnOptimize = false, $intAllowedFilter = self::FILTER_ALL) {
		return parent::getBinary($objImage->toTrueColorImage(), $blnOptimize, $intAllowedFilter);
	}
	
}
