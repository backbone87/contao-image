<?php

abstract class PaletteImageOperation extends ImageOperation {

	protected function __construct() {
		parent::__construct();
	}
	
	protected function prepare() {
		parent::prepare();
		
		if($this->objSource->isPaletteImage())
			return;
			
		
	}
	
}