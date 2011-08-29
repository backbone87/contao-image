<?php

//namespace backboneit\image;

class Size {
	
	public static function createFromRatioWidth($fltRatio, $intWidth) {
		$intWidth = max(intval($intWidth), 0);
		return new self($intWidth, round(floatval($fltRatio) / $intWidth));
	}
	
	public static function createFromRatioHeight($fltRatio, $intHeight) {
		$intHeight = max(intval($intHeight), 0);
		return new self(round(floatval($fltRatio) * $intHeight), $intHeight);
	}
	
	public static function createFromPoint(Point2D $objPoint) {
		return new self($objPoint->getX() + 1, $objPoint->getY() + 1);
	}
	
	public static function createFromPoints(Point2D $objUL, Point2D $objBR) {
		return new self($objUL->getX() - $objBR->getX(), $objUL->getY() - $objBR->getY());
	}
	
	public static function createFromFile(File $objFile) {
		if(!$objFile->width) {
			throw new Exception(sprintf(
				'Size::createFromFile(): Data in file [%s] is no valid or supported image or maybe damaged.',
				$objFile->value
			));
		}
		return new self($objFile->width, $objFile->height);
	}
	
	public static function compareByArea(self $objFirst, self $objSecond) {
		return $objFirst->getArea() > $objSecond->getArea();
	}
	
	private $intWidth;
	private $intHeight;
	private $fltRatio;
	
	public function __construct($intWidth, $intHeight) {
		$this->intWidth = max(intval($intWidth), 0);
		$this->intHeight = max(intval($intHeight), 0);
		$this->fltRatio = $this->intHeight == 0 ? INF : $this->intWidth / $this->intHeight;
	}
	
	public function __toString() {
		return sprintf('[Object: Size (Width %s, Height %s)]',
			$this->intWidth,
			$this->intHeight
		);
	}
	
	public function getWidth() {
		return $this->intWidth;
	}
	
	public function getHeight() {
		return $this->intHeight;
	}
	
	public function getRatio() {
		return $this->fltRatio;
	}
	
	public function getArea() {
		return $this->intWidth * $this->intHeight;
	}
	
	/**
	 * Returns a new Size object, with the same height and a modified width to
	 * match the given ratio.
	 * 
	 * @param float $fltRatio
	 * 			The ratio the new size should match.
	 * @return Size
	 * 			The new Size object.
	 */
	public function ratiofyWidth($fltRatio) {
		return self::createFromRatioHeight($fltRatio, $this->intHeight);
	}
	
	/**
	 * Returns a new Size object, with the same width and a modified height to
	 * match the given ratio.
	 * 
	 * @param float $fltRatio
	 * 			The ratio the new size should match.
	 * @return Size
	 * 			The new Size object.
	 */
	public function ratiofyHeight($fltRatio) {
		return self::createFromRatioWidth($fltRatio, $this->intWidth);
	}
	
	/**
	 * Returns a new Size object, with either the width or height increased to
	 * match the given ratio.
	 * 
	 * @param float $fltRatio
	 * 			The ratio the new size should match.
	 * @return Size
	 * 			The new Size object.
	 */
	public function ratiofyUp($fltRatio) {
		return floatval($fltRatio) < $this->fltRatio
			? $this->ratiofyHeight($fltRatio)
			: $this->ratiofyWidth($fltRatio);
	}
	
	/**
	 * Returns a new Size object, with either the width or height decreased to
	 * match the given ratio.
	 * 
	 * @param float $fltRatio
	 * 			The ratio the new size should match.
	 * @return Size
	 * 			The new Size object.
	 */
	public function ratiofyDown($fltRatio) {
		return floatval($fltRatio) < $this->fltRatio
			? $this->ratiofyWidth($fltRatio)
			: $this->ratiofyHeight($fltRatio);
	}
	
	/**
	 * Test whether this size's width and height are smaller than or equal to
	 * the given size's width and height. 
	 * 
	 * @param Size $objOuter
	 * 			The size where this size should fit in.
	 * @return boolean
	 * 			True, if this Size fits into $objOuter, otherwise false.
	 */
	public function fits(self $objOuter) {
		return $objOuter->getWidth() >= $this->intWidth && $objOuter->getHeight() >= $this->intHeight;
	}
	
	/**
	 * Test whether this size's width and height are larger than or equal to
	 * the given size's width and height. 
	 * 
	 * @param Size $objInner
	 * 			The size which should be wrapped by this size.
	 * @return boolean
	 * 			True, if this Size can wrap $objInner, otherwise false.
	 */
	public function wraps(self $objInner) {
		return $objInner->getWidth() <= $this->intWidth && $objInner->getHeight() <= $this->intHeight;
	}
	
	/**
	 * Returns the point which centers this size within the given size. The
	 * resulting Point2D object is always a valid point within the given size:
	 * If the given size is smaller than this size, the x and/or y value of the
	 * resulting point is set to 0.
	 * 
	 * @param Size $objOuter
	 * 			The outer size.
	 * @return Point
	 * 			The point which centers this size within $objOuter.
	 */
	public function centerize(self $objOuter) {
		return new Point2D(
			round(($objOuter->getWidth() - $this->intWidth) / 2),
			round(($objOuter->getHeight() - $this->intHeight) / 2)
		);
	}
	
	/**
	 * Returns a new Size object scaled by the given scale.
	 *  
	 * @param float $fltScale
	 * 			The scale multiplier
	 * @return Size
	 * 			The new size scaled by $fltScale.
	 */
	public function scale($fltScale) {
		$fltScale = floatval($fltScale);
		return new self(
			round($this->intWidth * $fltScale),
			round($this->intHeight * $fltScale)
		);
	}
	
	public function equals(Size $objOther, $fltTolerance = 0) {
		$fltTolerance = floatval($fltTolerance);
		return $fltTolerance == 0
			? $this->intWidth == $objOther->intWidth && $this->intHeight == $objOther->intHeight
			: $this->scale(1 + $fltTolerance)->wraps($objOther) && $this->scale(1 - $fltTolerance)->fits($objOther);
	}
	
	public function toPoint() {
		return new Point2D($this->intWidth - 1, $this->intHeight - 1);
	}
	
	public function isValidSubArea(Size $objSize, Point2D $objPoint) {
		$objSize = self::createFromPoint($objSize->toPoint()->add($objPoint));
		return $this->intWidth <= $objSize->intWidth && $this->intHeight <= $objSize->intHeight;
	}
	
	/**
	 * Throws an exception, if $this->getArea() == 0.
	 * 
	 * @return null
	 */
	public function checkNonNullArea() {
		if(!$this->getArea()) {
			throw new Exception(sprintf(
				'Size::checkNonNullArea(): Area of size must be positive. Size: %s.',
				$this
			));
		}
	}
	
	public function checkValidSubArea(Size $objSize, Point2D $objPoint) {
		if(!$this->isValidSubArea($objSize, $objPoint)) {
			throw new Exception(sprintf(
				'Size::checkValidSubArea(): Given arguments does not describe a valid sub area. Size: %s. Sub area size: %s. Point: %s',
				$this,
				$objSize,
				$objPoint
			));
		}
	}
	
}