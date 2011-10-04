<?php

//namespace backboneit\image;

class Point2D {

	public static function createFromArray(array $arrPoint, $blnAssoc = false) {
		return $blnAssoc
			? new self(
				isset($arrSize['x']) ? $arrSize['x'] : $arrSize[0],
				isset($arrSize['y']) ? $arrSize['y'] : $arrSize[1]
			)
			: new self(
				isset($arrSize[0]) ? $arrSize[0] : $arrSize['x'],
				isset($arrSize[1]) ? $arrSize[1] : $arrSize['y']
			);
	}
	
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
	
	/**
	 * Returns an associative or indexed array containing this point's x and y
	 * ordinates.
	 * 
	 * @param boolean $blnAssoc
	 * 			Whether to return an associative or indexed array.
	 * 
	 * @return array
	 * 			The point array.
	 */
	public function toArray($blnAssoc = false) {
		return $blnAssoc
			? array('x' => $this->intX, 'y' => $this->intY)
			: array($this->intX, $this->intY);
	}
	
}