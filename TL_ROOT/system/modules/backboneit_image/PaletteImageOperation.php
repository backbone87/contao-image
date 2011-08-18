<?php

abstract class PaletteImageOperation extends ImageOperation {

	protected function __construct() {
		parent::__construct();
	}
	
	protected function createEmpty(Size $objSize) {
		return PaletteImage::createEmpty($objSize);
	}
	
}