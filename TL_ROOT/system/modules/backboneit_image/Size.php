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
	
	/**
	 * Creates a new Size object instance, with the given width and height.
	 * 
	 * @param integer $intWidth
	 * 			The width of this new instance.
	 * @param integer $intHeight
	 * 			The height of this new instance.
	 */
	public function __construct($intWidth, $intHeight) {
		$this->intWidth = max(intval($intWidth), 0);
		$this->intHeight = max(intval($intHeight), 0);
		$this->fltRatio = $this->intHeight == 0 ? INF : $this->intWidth / $this->intHeight;
	}
	
	/**
	 * Returns a human readable string representation of this Size.
	 * 
	 * @return string
	 * 			The size's string representation.
	 */
	public function __toString() {
		return sprintf('[Object: Size (Width %s, Height %s)]',
			$this->intWidth,
			$this->intHeight
		);
	}
	
	/**
	 * Returns the width of this Size.
	 * 
	 * @return integer
	 * 			This size's width.
	 */
	public function getWidth() {
		return $this->intWidth;
	}
	
	/**
	 * Returns the height of this Size.
	 * 
	 * @return integer
	 * 			This size's height.
	 */
	public function getHeight() {
		return $this->intHeight;
	}
	
	/**
	 * Returns the aspect ratio (width / height) of this Size.
	 * 
	 * @return float
	 * 			This size's aspect ratio.
	 */
	public function getRatio() {
		return $this->fltRatio;
	}
	
	/**
	 * Returns the area of this Size.
	 * 
	 * @return integer
	 * 			This size's area.
	 */
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
	 * 			The scale multiplier.
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
	
	/**
	 * Returns a Size object with a maximum possible area that fits into the
	 * given outer size, while maintaining the aspect ratio of this size.
	 * 
	 * @param Size $objOuter
	 * 			The outer size.
	 * @return Size
	 * 			A size, which fits into $objOuter.
	 */
	public function scaleToFit(self $objOuter) {
		$fltScale = min(1, $objOuter->getWidth() / $this->intWidth, $objOuter->getHeight() / $this->intHeight);
		return $fltScale < 1 ? $this->scale($fltScale) : $this;
	}
	
	
	/**
	 * Returns a Size object with a minimal possible area that wraps the given
	 * inner size, while maintaining the aspect ratio of this size.
	 * 
	 * @param Size $objInner
	 * 			The inner size.
	 * @return Size
	 * 			A size, which warps $objInner.
	 */
	public function scaleToWrap(self $objInner) {
		$fltScale = max(1, $objInner->getWidth() / $this->intWidth, $objInner->getHeight() / $this->intHeight);
		return $fltScale > 1 ? $this->scale($fltScale) : $this;
	}
	
	/**
	 * Tells whether this Size equals the given Size, with the given tolerance.
	 * 
	 * @param Size $objOther
	 * 			The size to compare against.
	 * @param float $fltTolerance
	 * 			The percentual margin within the two sizes are considered equal.
	 * 
	 * @return boolean
	 * 			If this size equals $objOther true, otherwise false.
	 */
	public function equals(Size $objOther, $fltTolerance = 0) {
		$fltTolerance = floatval($fltTolerance);
		return $fltTolerance == 0
			? $this->intWidth == $objOther->intWidth && $this->intHeight == $objOther->intHeight
			: $this->scale(1 + $fltTolerance)->wraps($objOther) && $this->scale(1 - $fltTolerance)->fits($objOther);
	}
	
	/**
	 * Returns a Point2D object, which denotes the lower right corner of this
	 * size. The upper left corner is treated as the origin.
	 * 
	 * @return Point2D
	 * 			This size corresponding point.
	 */
	public function toPoint() {
		return new Point2D($this->intWidth - 1, $this->intHeight - 1);
	}
	
	/**
	 * Returns an associative or indexed array containing this size's width and
	 * height.
	 * 
	 * @param boolean $blnAssoc
	 * 			Whether to return an associative or indexed array.
	 * 
	 * @return array
	 * 			The size array.
	 */
	public function toArray($blnAssoc = false) {
		return $blnAssoc
			? array('width' => $this->intWidth, 'height' => $this->intHeight)
			: array($this->intWidth, $this->intHeight);
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