<?php

namespace bbit\image\util;

class Point2D {

	/**
	 * @return \bbit\image\util\Point2D
	 */
	public static function create($x = 0, $y = 0) {
		$point = new self();
		$point->set($x, $y);
		return $point;
	}

	/**
	 * @return \bbit\image\util\Point2D
	 */
	public static function createFromArray(array $point, $assoc = false) {
		if($assoc) {
			$x = isset($point['x']) ? $point['x'] : $point[0];
			$y = isset($point['y']) ? $point['y'] : $point[1];
		} else {
			$x = isset($point[0]) ? $point[0] : $point['x'];
			$y = isset($point[1]) ? $point[1] : $point['y'];
		}
		return self::create($x, $y);
	}

	/**
	 * @return \bbit\image\util\Point2D
	 */
	public static function zero() {
		return self::create();
	}

	/**
	 * @return \bbit\image\util\Point2D
	 */
	public static function one() {
		return self::create(1, 1);
	}

	/** @var integer */
	private $x = 0;
	/** @var integer */
	private $y = 0;

	/**
	 * @return void
	 */
	protected function __construct() {
	}

	/**
	 * @param integer $x
	 * @param integer $y
	 * @return \bbit\image\util\Point2D
	 */
	private function set($x, $y) {
		$this->x = intval($x);
		$this->y = intval($y);
		return $this;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return sprintf('[Object: Point2D (X %s, Y %s)]', $this->getX(), $this->getY());
	}

	/**
	 * @return integer
	 */
	public function getX() {
		return $this->x;
	}

	/**
	 * @return \bbit\image\util\Point2D
	 */
	public function setX($x) {
		return self::create($x, $this->getY());
	}

	/**
	 * @return integer
	 */
	public function getY() {
		return $this->y;
	}

	/**
	 * @return \bbit\image\util\Point2D
	 */
	public function setY($y) {
		return self::create($this->getX(), $y);
	}

	/**
	 * @return \bbit\image\util\Point2D
	 */
	public function add(Point2D $point) {
		return self::create($this->getX() + $point->getX(), $this->getY() + $point->getY());
	}

	/**
	 * @return \bbit\image\util\Point2D
	 */
	public function sub(Point2D $point) {
		return self::create($this->getX() - $point->getX(), $this->getY() - $point->getY());
	}

	/**
	 * @return \bbit\image\util\Point2D
	 */
	public function abs() {
		return self::create(abs($this->getX()), abs($this->getY()));
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
	public function toArray($assoc = false) {
		$array = array($this->getX(), $this->getY());
		$assoc && $array = array_combine(array('x', 'y'), $array);
		return $array;
	}

	/**
	 * @return \bbit\image\util\Size
	 */
	public function toSize() {
		return Size::createFromPoint2D($this);
	}

}
