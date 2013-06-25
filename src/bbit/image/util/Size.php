<?php

namespace bbit\image\util;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class Size {

	/**
	 * @param integer $width
	 * @param integer $height
	 * @return \bbit\image\util\Size
	 */
	public static function create($width, $height) {
		$size = new self();
		$size->set($width, $height);
		return $size;
	}

	/**
	 * @param float $ratio
	 * @param integer $width
	 * @return \bbit\image\util\Size
	 */
	public static function createFromRatioWidth($ratio, $width) {
		$ratio = max(0, $ratio);
		return self::create($width, $ratio == 0 ? 0 : round($width / $ratio));
	}

	/**
	 * @param float $ratio
	 * @param integer $height
	 * @return \bbit\image\util\Size
	 */
	public static function createFromRatioHeight($ratio, $height) {
		return self::create(round(max(0, $ratio) * $height), $height);
	}

	/**
	 * @param array $size
	 * @param boolean $assoc
	 * @return \bbit\image\util\Size
	 */
	public static function createFromArray(array $size, $assoc = false) {
		if($assoc) {
			$width = isset($size['width']) ? $size['width'] : $size[0];
			$height = isset($size['height']) ? $size['height'] : $size[1];
		} else {
			$width = isset($size[0]) ? $size[0] : $size['width'];
			$height = isset($size[1]) ? $size[1] : $size['height'];
		}
		return self::create($width, $height);
	}

	/**
	 * @param \bbit\image\util\Point2D $point
	 * @return \bbit\image\util\Size
	 */
	public static function createFromPoint2D(Point2D $point) {
		$point = $point->abs();
		return self::create($point->getX(), $point->getY());
	}

	/**
	 * @param \bbit\image\util\Point2D $a
	 * @param \bbit\image\util\Point2D $b
	 * @return \bbit\image\util\Size
	 */
	public static function createFromPoints2D(Point2D $a, Point2D $b) {
		return self::createFromPoint2D($a->sub($b));
	}

	/**
	 * @param \bbit\image\util\Size $a
	 * @param \bbit\image\util\Size $b
	 * @return integer
	 */
	public static function compareByWidth(self $a, self $b) {
		return $a->getWidth() - $b->getWidth();
	}

	/**
	 * @param \bbit\image\util\Size $a
	 * @param \bbit\image\util\Size $b
	 * @return integer
	 */
	public static function compareByHeight(self $a, self $b) {
		return $a->getHeight() - $b->getHeight();
	}

	/**
	 * @param \bbit\image\util\Size $a
	 * @param \bbit\image\util\Size $b
	 * @return integer
	 */
	public static function compareByRatio(self $a, self $b) {
		return $a->getRatio() - $b->getRatio();
	}

	/**
	 * @param \bbit\image\util\Size $a
	 * @param \bbit\image\util\Size $b
	 * @return integer
	 */
	public static function compareByArea(self $a, self $b) {
		return $a->getArea() - $b->getArea();
	}

	/** @var integer */
	private $width = 0;
	/** @var integer */
	private $height = 0;
	/** @var float */
	private $ratio = 0;

	/**
	 * Creates a new Size object instance, with the given width and height.
	 * @return void
	 */
	protected function __construct() {
	}

	/**
	 * @param integer $width
	 * @param integer $height
	 * @return \bbit\image\util\Size
	 */
	private function set($width, $height) {
		$this->width = $width = max(0, intval($width));
		$this->height = $height = max(0, intval($height));
		$this->ratio = $height == 0 ? $width == 0 ? 0 : INF : $width / $height;
		return $this;
	}

	/**
	 * Returns a human readable string representation of this size.
	 *
	 * @return string This size' string representation
	 */
	public function __toString() {
		return sprintf('[Object: Size (Width %s, Height %s, Ratio %s, Area %s)]',
			$this->getWidth(),
			$this->getHeight(),
			$this->getRatio(),
			$this->getArea()
		);
	}

	/**
	 * @return integer This size' width
	 */
	public function getWidth() {
		return $this->width;
	}

	/**
	 * @param integer $width
	 * @return \bbit\image\util\Size
	 */
	public function setWidth($width) {
		return self::create($width, $this->getHeight());
	}

	/**
	 * @return integer This size' height
	 */
	public function getHeight() {
		return $this->height;
	}

	/**
	 * @param integer $height
	 * @return \bbit\image\util\Size
	 */
	public function setHeight($height) {
		return self::create($this->getWidth(), $height);
	}

	/**
	 * @return float This size' aspect ratio
	 */
	public function getRatio() {
		return $this->ratio;
	}

	/**
	 * @return integer This size' area
	 */
	public function getArea() {
		return $this->getWidth() * $this->getHeight();
	}

	/**
	 * @return \bbit\image\util\Point2D
	 */
	public function getBottomRight() {
		return $this->toPoint2D()->sub(Point2D::one());
	}

	/**
	 * Returns a new size object, with the same height and a modified width to
	 * match the given ratio.
	 *
	 * @param float $ratio The ratio the new size should match
	 * @return \bbit\image\util\Size
	 */
	public function ratiofyWidth($ratio) {
		return self::createFromRatioHeight($ratio, $this->getHeight());
	}

	/**
	 * Returns a new size object, with the same width and a modified height to
	 * match the given ratio.
	 *
	 * @param float $ratio The ratio the new size should match
	 * @return \bbit\image\util\Size
	 */
	public function ratiofyHeight($ratio) {
		return self::createFromRatioWidth($ratio, $this->getWidth());
	}

	/**
	 * Returns a new size object, with either the width or height increased to
	 * match the given ratio.
	 *
	 * @param float $ratio The ratio the new size should match
	 * @return \bbit\image\util\Size
	 */
	public function ratiofyUp($ratio) {
		return $ratio < $this->getRatio() ? $this->ratiofyHeight($ratio) : $this->ratiofyWidth($ratio);
	}

	/**
	 * Returns a new size object, with either the width or height decreased to
	 * match the given ratio.
	 *
	 * @param float $ratio The ratio the new size should match
	 * @return \bbit\image\util\Size
	 */
	public function ratiofyDown($ratio) {
		return $ratio < $this->getRatio() ? $this->ratiofyWidth($ratio) : $this->ratiofyHeight($ratio);
	}

	/**
	 * Test whether this size' width and height are smaller than or equal to
	 * the given size' width and height.
	 *
	 * @param \bbit\image\util\Size $outer The size where this size should fit in
	 * @return boolean True, if this size fits into the given one; otherwise false
	 */
	public function fits(self $outer) {
		return $outer->getWidth() >= $this->getWidth() && $outer->getHeight() >= $this->getHeight();
	}

	/**
	 * Test whether this size' width and height are larger than or equal to
	 * the given size' width and height.
	 *
	 * @param \bbit\image\util\Size $inner The size which should be wrapped by this size
	 * @return boolean True, if this Size can wrap the given one; otherwise false
	 */
	public function wraps(self $inner) {
		return $inner->fits($this);
	}

	/**
	 * Returns the point which centers this size within the given size. The
	 * resulting Point2D object is always a valid point within the given size:
	 * If the given size is smaller than this size, the x and/or y value of the
	 * resulting point is set to 0.
	 *
	 * @param \bbit\image\util\Size $outer The outer size
	 * @return \bbit\image\util\Point2D The point which centers this size within $outer
	 */
	public function centerize(self $outer) {
		return Point2D::create(
			max(0, round(($outer->getWidth() - $this->getWidth()) / 2)),
			max(0, round(($outer->getHeight() - $this->getHeight()) / 2))
		);
	}

	/**
	 * Returns a new size object scaled by the given scale.
	 *
	 * @param float $scale The scale multiplier
	 * @return \bbit\image\util\Size
	 */
	public function scale($scale) {
		return self::create(
			round($this->getWidth() * $scale),
			round($this->getHeight() * $scale)
		);
	}

	/**
	 * Returns a size object with a maximum possible area that fits into the
	 * given outer size, while maintaining the aspect ratio of this size.
	 *
	 * @param \bbit\image\util\Size $outer The outer size
	 * @param boolean $upscale
	 * @return \bbit\image\util\Size A size, which fits into the given one
	 */
	public function scaleToFit(self $outer, $upscale = false, &$scale = null) {
		$scale = 1 / max($this->getWidth() / $outer->getWidth(), $this->getHeight() / $outer->getHeight());
		$upscale || $scale = min(1, $scale);
		return $scale != 1 ? $this->scale($scale) : $this;
	}


	/**
	 * Returns a size object with a minimal possible area that wraps the given
	 * inner size, while maintaining the aspect ratio of this size.
	 *
	 * @param \bbit\image\util\Size $inner The inner size
	 * @param boolean $upscale
	 * @return \bbit\image\util\Size A size, which warps $inner
	 */
	public function scaleToWrap(self $inner, $upscale = false, &$scale = null) {
		$scale = 1 / min($this->getWidth() / $inner->getWidth(), $this->getHeight() / $inner->getHeight());
		$upscale || $scale = min(1, $scale);
		return $scale != 1 ? $this->scale($scale) : $this;
	}

	/**
	 * Tells whether this size equals the given size, with the given tolerance.
	 *
	 * @param \bbit\image\util\Size $other The size to compare against
	 * @param float $tolerance The percentual margin within the two sizes are considered equal
	 * @return boolean True, if this size equals the given one; otherwise false
	 */
	public function equals($other, $tolerance = 0) {
		if(!is_a($other, __CLASS__)) {
			return false;
		}
		if($tolerance == 0) {
			return $this->getWidth() == $other->getWidth() && $this->getHeight() == $other->getHeight();
		}
		return $this->scale(1 + $tolerance)->wraps($other) && $this->scale(1 - $tolerance)->fits($other);
	}

	/**
	 * Returns a Point2D object, which denotes the lower right corner of this
	 * size. The upper left corner is treated as the origin.
	 *
	 * @return \bbit\image\util\Point2D This size corresponding point
	 */
	public function toPoint2D() {
		return Point2D::create($this->getWidth(), $this->getHeight());
	}

	/**
	 * Returns an associative or indexed array containing this size' width and
	 * height.
	 *
	 * @param boolean $assoc Whether to return an associative or indexed array
	 * @return array The size array
	 */
	public function toArray($assoc = false) {
		$array = array($this->getWidth(), $this->getHeight());
		$assoc && $array = array_combine(array('width', 'height'), $array);
		return $array;
	}

	/**
	 * @param \bbit\image\util\Size $size
	 * @param \bbit\image\util\Point2D $offset
	 * @return boolean
	 */
	public function isIntersectedByArea(self $size, Point2D $offset = null) {
		$offset || $offset = Point2D::zero();
		return $offset->getX() < $this->getWidth()
			&& $offset->getY() < $this->getHeight()
			&& $offset->getX() > -$size->getWidth()
			&& $offset->getY() > -$size->getHeight();
	}

	/**
	 * @param \bbit\image\util\Point2D $point
	 * @return boolean
	 */
	public function isValidPoint(Point2D $point) {
		return $point->toSize()->fits($this);
	}

	/**
	 * @param \bbit\image\util\Size $size
	 * @param \bbit\image\util\Point2D $point
	 * @return boolean
	 */
	public function isValidSubArea(self $size, Point2D $point) {
		return $size->toPoint2D()->add($point)->toSize()->fits($this);
	}

	/**
	 * Throws an exception, if $this->getArea() == 0.
	 *
	 * @throws \Exception
	 * @return \bbit\image\util\Size
	 */
	public function requireNonNullArea() {
		if(!$this->getArea()) {
			throw new \Exception(sprintf('Area of size [%s] must be greather than zero', $this));
		}
		return $this;
	}

	/**
	 * @param \bbit\image\util\Size $size
	 * @param \bbit\image\util\Point2D $point
	 * @throws \Exception
	 * @return \bbit\image\util\Size
	 */
	public function requireValidSubArea(self $size, Point2D $point) {
		if(!$this->isValidSubArea($size, $point)) {
			throw new \Exception(sprintf('Size [%s] at offset [%s] does not describe a valid sub area of size [%s]', $this, $size, $point));
		}
		return $this;
	}

}