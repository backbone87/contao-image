<?php

class PNGFormat extends ImageFormat {

	const FILTER_NONE	= PNG_NO_FILTER;	// = 0   = 0x00
//	const FILTER_NONE	= PNG_FILTER_NONE;	// = 8   = 0x08, weird?
	const FILTER_SUB	= PNG_FILTER_SUB;	// = 16  = 0x10
	const FILTER_UP		= PNG_FILTER_UP;	// = 32  = 0x20
	const FILTER_AVG	= PNG_FILTER_AVG;	// = 64  = 0x40
	const FILTER_PAETH	= PNG_FILTER_PAETH;	// = 128 = 0x80
	const FILTER_ALL	= 0xF0;				// = 240 = 0xF0
//	const FILTER_ALL	= PNG_ALL_FILTERS;	// = 248 = 0xF8, weird?
	
	protected $intCompression;
	
	protected $intFilter;
	
	public function __construct($intCompression = 5, $intFilter = self::FILTER_PAETH) {
		parent::__construct();
		$this->setCompression($intCompression);
		$this->setFilter($intFilter);
	}
	
	public function getCompression() {
		return $this->intCompression;
	}
	
	public function setCompression($intCompression) {
		$this->intCompression = min(max(intval($intCompression), 0), 9);
	}
	
	public function getFilter() {
		return $this->intFilter;
	}
	
	public function hasFilter($intFilter) {
		$intFilter &= self::FILTER_ALL;
		return ($this->intFilter & $intFilter) == $intFilter;
	}
	
	public function setFilter($intFilter) {
		$this->intFilter = $intFilter & self::FILTER_ALL;
	}
	
	public function addFilter($intFilter) {
		$this->intFilter |= $intFilter & self::FILTER_ALL;
	}
	
	public function removeFilter($intFilter) {
		$this->intFilter &= ~($intFilter & self::FILTER_ALL);
	}
	
	public function optimizeFor(Image $objImage, $intAllowedFilter = self::FILTER_ALL) {
		
		$arrCombinations = array(0);
		foreach(array(self::FILTER_SUB, self::FILTER_UP, self::FILTER_AVG, self::FILTER_PAETH) as $intFilter)
			if($intFilter & $intAllowedFilter)
				foreach($arrCombinations as $intCombination)
					$arrCombinations[] = $intCombination | $intFilter;
		
		$this->setCompression(9);
		$intBest = 0;
		$intBestLength = PHP_INT_MAX;
		foreach($arrCombinations as $intCombination) {
			$this->setFilter($intCombination);
			$intLength = strlen($this->getBinary($objImage));
			if($intLength < $intBestLength) {
				$intBest = $intCombination;
				$intBestLength = $intLength;
			}
		}
		
		$this->setFilter($intBest);
		return $intBest;
	}
	
	public function getBinary(Image $objImage, $blnOptimize = false, $intAllowedFilter = self::FILTER_ALL) {
		$objImage->checkResource();
		
		$blnOptimize && $this->optimizeFor($objImage, $intAllowedFilter);
		
		return parent::getBinary($objImage, $this->intCompression, $this->intFilter);
	}
	
	public function getStoreFunction() {
		return 'imagepng';
	}
	
	public function getLoadFunction() {
		return 'imagecreatefrompng';
	}
	
}
