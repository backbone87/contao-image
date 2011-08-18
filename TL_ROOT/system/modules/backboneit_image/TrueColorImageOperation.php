<?php

abstract class TrueColorImageOperation extends ImageOperation {
	
	protected function __construct() {
		parent::__construct();
	}
	
	protected function createEmpty(Size $objSize) {
		return TrueColorImage::createEmpty($objSize);
	}
	
}