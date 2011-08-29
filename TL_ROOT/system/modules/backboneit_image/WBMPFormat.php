<?php

class WBMPFormat extends ImageFormat {

	public function __construct() {
		parent::__construct();
	}
	
	public function getStoreFunction() {
		return 'imagewbmp';
	}
	
	public function getLoadFunction() {
		return 'imagecreatefromwbmp';
	}
	
}
