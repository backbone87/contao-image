<?php

class Point2D {
	
	private $intX;
	private $intY;
	
	public function __construct($intX = 0, $intY = 0) {
		$this->intX = max(intval($intX), 0);
		$this->intY = max(intval($intY), 0);
	}
	
	public function getX() {
		return $this->intX;
	}
	
	public function getY() {
		return $this->intY;
	}
	
}