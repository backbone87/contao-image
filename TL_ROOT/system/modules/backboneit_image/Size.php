<?php

class Size {
	
	public static function createFromRatioWidth($fltRatio, $intWidth) {
		$intWidth = max(intval($intWidth), 0);
		return new self($intWidth, round(floatval($fltRatio) / $intWidth));
	}
	
	public static function createFromRatioHeight($fltRatio, $intHeight) {
		$intHeight = max(intval($intHeight), 0);
		return new self(round(floatval($fltRatio) * $intHeight), $intHeight);
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
		$this->fltRatio = $this->intWidth / $this->intHeight;
	}
	
	public function getWidth() {
		return $this->intWidth;
	}
	
	public function getHeight() {
		return $this->intHeight;
	}
	
	public function getArea() {
		return $this->intWidht * $this->intHeight;
	}
	
	public function getRatio() {
		return $this->fltRatio;
	}
	
	public function ratiofyWidth($fltRatio) {
		return self::createFromRatioHeight($fltRatio, $this->intHeight);
	}
	
	public function ratiofyHeight($fltRatio) {
		return self::createFromRatioWidth($fltRatio, $this->intWidth);
	}
	
	public function ratiofyDown($fltRatio) {
		return floatval($fltRatio) < $this->fltRatio
			? $this->ratiofyWidth($fltRatio)
			: $this->ratiofyHeight($fltRatio);
	}
	
	public function ratiofyUp($fltRatio) {
		return floatval($fltRatio) < $this->fltRatio
			? $this->ratiofyHeight($fltRatio)
			: $this->ratiofyWidth($fltRatio);
	}
	
	public function fitsInto(self $objOuter) {
		return $objOuter->getWidth() >= $this->intWidth && $objOuter->getHeight() >= $this->intHeight;
	}
	
	public function centerize(self $objOuter) {
		return new Point2D(
			round(($objOuter->getWidth() - $this->intWidth) / 2),
			round(($objOuter->getHeight() - $this->intHeight) / 2)
		);
	}
	
	public function scale($fltScale) {
		$fltScale = floatval($fltScale);
		return new self(
			round($this->intWidth * $fltScale),
			round($this->intHeight * $fltScale)
		);
	}

	public function isAllowed() {
		return $this->getArea() <= max(4000000, $GLOBALS['TL_CONFIG']['backboneit_image_maxsize']);
	}
	
	public function checkAllowed() {
		if(!$this->isAllowed()) {
			throw new Exception(sprintf(
				'Size::checkAllowed(): Area of size [%s] exceeds max allowed value of [%s]. Width [%s], height [%s].',
				$this->getArea(),
				max(4000000, $GLOBALS['TL_CONFIG']['backboneit_image_maxsize']),
				$this->intWidth,
				$this->intHeight
			));
		}
	}

	public function checkNonNullArea() {
		if(!$this->getArea()) {
			throw new Exception(sprintf(
				'Size::checkNonNullArea(): Area of size must be positive. Width [%s], height [%s].',
				$this->intWidth,
				$this->intHeight
			));
		}
	}
	
}