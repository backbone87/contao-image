<?php

namespace bbit\image;

use bbit\image\util\Color;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class TrueColorCanvas extends Canvas {

	public function __construct($resource) {
		parent::__construct($resource);
		if(!imageistruecolor($resource)) {
			throw new \InvalidArgumentException('#1 $resource is not a gdlib true color image');
		}
	}

	public function __clone() {
		$size = $this->getSize();
		$target = CanvasFactory::createTrueColorCanvas($size);

		imagecopy($target->getResource(), $this->getResource(),
			0, 0,
			0, 0,
			$size->getWidth(), $size->getHeight()
		);

		return $target;
	}

	public function getColorIndex(Color $color, $allocate = true, $exact = true) {
		$index = 0;
		$index |= $color->getRed() << 16;
		$index |= $color->getGreen() << 8;
		$index |= $color->getBlue();
		$index |= min(127, round($color->getAlpha() / 2)) << 24;
		return $index;
	}

	public function getColor($index) {
		return Color::create(
			($index >> 16) & 0xFF,
			($index >> 8) & 0xFF,
			$index & 0xFF,
			(($index >> 24) & 0x7F) * 2
		);
	}

	public function getTransparentColor() {
		return Color::create(0, 0, 0, 255);
	}

	public function toPaletteCanvas($dither = false, $numColors = 255) {
		$target = clone $this;
		$numColors = max(1, min(256, intval($numColors)));

		if(!imagetruecolortopalette($target->getResource(), (bool) $dither, $numColors)) {
			throw new \RuntimeException(); // TODO exception text
		}
		imagecolormatch($this->getResource(), $target->getResource());

		return new PaletteCanvas($target->invalidate());
	}

	public function toTrueColorCanvas() {
		return $this;
	}

	public function isPaletteCanvas() {
		return false;
	}

	public function isTrueColorCanvas() {
		return true;
	}

}
