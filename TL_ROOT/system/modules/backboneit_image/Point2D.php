<?php

//namespace backboneit\image;

class Point2D {
	
	private $intX;
	private $intY;
	
	public function __construct($intX = 0, $intY = 0) {
		$this->intX = max(intval($intX), 0);
		$this->intY = max(intval($intY), 0);
	}
	
	public function __toString() {
		return sprintf('[Object: Point2D (X %s, Y %s)]',
			$this->intX,
			$this->intY
		);
	}
	
	public function getX() {
		return $this->intX;
	}
	
	public function getY() {
		return $this->intY;
	}
	
	public function add(Point2D $objPoint) {
		return new self($this->intX + $objPoint->intX, $this->intY + $objPoint->intY);
	}
	
	public function subtract(Point2D $objPoint) {
		return new self($this->intX - $objPoint->intX, $this->intY - $objPoint->intY);
	}
	
}