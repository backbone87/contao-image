<?php

namespace bbit\image\format;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class PNG8Format extends PNGFormat {

	public function __construct($compression = 5, $filter = self::FILTER_PAETH) {
		parent::__construct($compression, $filter);
	}

	public function optimizeFor(Canvas $canvas, $allowedFilter = self::FILTER_ALL) {
		return parent::optimizeFor($canvas->toPaletteCanvas(), $allowedFilter);
	}

	public function getBinary(Canvas $canvas, $optimize = false, $allowedFilter = self::FILTER_ALL) {
		return parent::getBinary($canvas->toPaletteCanvas(), $optimize, $allowedFilter);
	}

}
