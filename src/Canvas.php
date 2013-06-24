<?php

namespace bbit\image;

use bbit\image\util\Color;
use bbit\image\util\Size;

/**
 * <p>
 * This class encapsulates an in-memory representation of an image. There are
 * 3 static factory methods to create an <tt>Canvas</tt> object, useful for the
 * most common cases.
 * </p>
 * <p>
 * Additionally this class offers a static mirror of the Controller->getCanvas()
 * method.
 * </p>
 * <p>
 * Any method, that require dimension or position arguments, will accept any
 * numeric value (numeric strings, float, integer) as input, unless explicitly
 * stated otherwise. Non-integer numbers are floored.
 * </p>
 *
 * PHP version 5
 * @copyright backboneIT | Oliver Hoff 2010 - Alle Rechte vorbehalten. All rights reserved.
 * @author Oliver Hoff <oliver@hofff.com>
 */
abstract class Canvas {

	private $resource;

	private $size;

	private $alphaBlending = false;

	private $saveAlpha = true;

	/**
	 * @param resource<gdimage> $resource
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	protected function __construct($resource) {
		if(!@imagesx($resource)) {
			throw new \InvalidArgumentException('#1 $resource is not a valid gdlib image resource.');
		}

		$this->resource = $resource;
		$this->size = Size::create(imagesx($resource), imagesy($resource));
		$this->setAlphaBlending(false);
		$this->setSaveAlpha(true);
	}

	/**
	 * @return void
	 */
	public function __destruct() {
		$this->resource === null || imagedestroy($this->resource);
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return '[Object: Canvas (' . $this->size . ')]';
	}

	/**
	 * @return void
	 */
	public abstract function __clone();

	/**
	 * @return \bbit\image\util\Size
	 */
	public function getSize() {
		return $this->size;
	}

	/**
	 * @return resource<gdimage>
	 */
	public function getResource() {
		return $this->resource;
	}

	/**
	 * @return boolean
	 */
	public function getAlphaBlending() {
		return $this->alphaBlending;
	}

	/**
	 * @param boolean $alphaBlending
	 * @throws \RuntimeException
	 * @return \bbit\image\Canvas
	 */
	public function setAlphaBlending($alphaBlending) {
		if(!imagealphablending($this->resource, $alphaBlending)) {
			throw new \RuntimeException(sprintf('Cannot set alpha blending to [%s]', $alphaBlending));
		}
		$this->alphaBlending = $alphaBlending;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getSaveAlpha() {
		return $this->saveAlpha;
	}

	/**
	 * @param boolean $saveAlpha
	 * @throws \RuntimeException
	 * @return \bbit\image\Canvas
	 */
	public function setSaveAlpha($saveAlpha) {
		if(!imagesavealpha($this->resource, $saveAlpha)) {
			throw new \RuntimeException(sprintf('Cannot set save alpha to [%s]', $saveAlpha));
		}
		$this->saveAlpha = $saveAlpha;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function isValid() {
		return is_resource($this->resource);
	}

	/**
	 * @throws \RuntimeException
	 * @return \bbit\image\Canvas
	 */
	public function requireValid() {
		if(!$this->isValid()) {
			throw new \RuntimeException('Underlying image resource has become invalid. This happens e.g. after imagedestroy is called on the resource.');
		}
		return $this;
	}

	/**
	 * @return \bbit\image\Canvas
	 */
	public function fork() {
		return clone $this;
	}

	/**
	 * @return resource<gdimage>
	 */
	protected function invalidate() {
		$resource = $this->resource;
		$this->resource = null;
		return $resource;
	}

	public abstract function getColorIndex(Color $color, $allocate = true, $exact = true);

	public abstract function getColor($index);

	public abstract function getTransparentColor();

	public abstract function toPaletteCanvas($dither = false, $numColors = 255);

	public abstract function toTrueColorCanvas();

	public abstract function isPaletteCanvas();

	public abstract function isTrueColorCanvas();

}
