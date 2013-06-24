<?php

namespace bbit\image;

use bbit\image\util\Color;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class PaletteCanvas extends Canvas {

	/**
	 * @param resource<gdimage> $resource
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	public function __construct($resource) {
		parent::__construct($resource);
		if(imageistruecolor($resource)) {
			throw new \InvalidArgumentException('#1 $resource is not a gdlib palette image');
		}
	}

	/* (non-PHPdoc)
	 * @see \bbit\image\Canvas::__clone()
	 */
	public function __clone() {
		$size = $this->getSize();
		$target = CanvasFactory::createPaletteCanvas($size);

		imagepalettecopy($target->getResource(), $this->getResource());

		imagecopy($target->getResource(), $this->getResource(),
			0, 0,
			0, 0,
			$size->getWidth(), $size->getHeight()
		);

		return $target;
	}

	public function getColorIndex(Color $color, $allocate = true, $exact = true) {
		$alpha = $color->getAlpha();

		$index = call_user_func(
			$alpha ? 'imagecolorexactalpha' : 'imagecolorexact',
			$this->getResource(),
			$color->getRed(),
			$color->getGreen(),
			$color->getBlue(),
			round($color->getAlpha() / 2)
		);
		if($index !== false) {
			return $index;
		}

		if($allocate && imagecolorstotal($this->getResource()) < 255) {
			return call_user_func(
				$alpha ? 'imagecolorallocatealpha' : 'imagecolorallocate',
				$this->getResource(),
				$color->getRed(),
				$color->getGreen(),
				$color->getBlue(),
				round($color->getAlpha() / 2)
			);
		}

		if(!$exact) { // TODO use lab and delta e
			return call_user_func(
				$alpha ? 'imagecolorclosestalpha' : 'imagecolorclosest',
				$this->getResource(),
				$color->getRed(),
				$color->getGreen(),
				$color->getBlue(),
				round($color->getAlpha() / 2)
			);
		}

		throw new \RuntimeException(sprintf('Failed to allocate color [%s].', $color));
	}

	public function getColor($index) {
		if(!is_numeric($index)) {
			throw new \InvalidArgumentException(sprintf('#1 $index must be numeric, given [%s]', $index));
		}
		$index = intval($intval);
		if($index < 0 || $index >= imagecolorstotal($this->getResource())) {
			throw new \RuntimeException(sprintf('Given index [%s] is out of bounds [0,%s].', $index, imagecolorstotal($this->getResource())));
		}

		return Color::createFromAssoc(imagecolorsforindex($this->getResource(), $index));
	}

	public function getTransparentColor() {
		return $this->getColor(imagecolortransparent($this->getResource()));
	}

	public function toPaletteCanvas($dither = false, $numColors = 255) {
		return $this;
	}

	public function toTrueColorCanvas() {
		$size = $this->getSize();
		$target = CanvasFactory::createTrueColorCanvas($size);

		imagecopy($target->getResource(), $this->getResource(),
			0, 0,
			0, 0,
			$size->getWidth(), $size->getHeight()
		);

		return $target;
	}

	public function isPaletteCanvas() {
		return true;
	}

	public function isTrueColorCanvas() {
		return false;
	}

}
