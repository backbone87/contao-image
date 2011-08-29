<?php

class GIFFormat extends ImageFormat {

	public function __construct() {
		parent::__construct();
	}

	public function getStoreFunction() {
		return 'imagegif';
	}
	
	public function getLoadFunction() {
		return 'imagecreatefromgif';
	}
	
}
