<?php

namespace bbit\image\format;

use bbit\image\Canvas;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class PNGFormat extends ImageFormat {

	const FILTER_NONE	= PNG_NO_FILTER;	// = 0   = 0x00
//	const FILTER_NONE	= PNG_FILTER_NONE;	// = 8   = 0x08, weird?
	const FILTER_SUB	= PNG_FILTER_SUB;	// = 16  = 0x10
	const FILTER_UP		= PNG_FILTER_UP;	// = 32  = 0x20
	const FILTER_AVG	= PNG_FILTER_AVG;	// = 64  = 0x40
	const FILTER_PAETH	= PNG_FILTER_PAETH;	// = 128 = 0x80
	const FILTER_ALL	= 0xF0;				// = 240 = 0xF0
//	const FILTER_ALL	= PNG_ALL_FILTERS;	// = 248 = 0xF8, weird?

	private $compression;

	private $filter;

	public function __construct($compression = 5, $filter = self::FILTER_PAETH) {
		parent::__construct();
		$this->setCompression($compression);
		$this->setFilter($filter);
	}

	public function getCompression() {
		return $this->compression;
	}

	public function setCompression($compression) {
		$this->compression = min(max(intval($compression), 0), 9);
		return $this;
	}

	public function getFilter() {
		return $this->filter;
	}

	public function hasFilter($filter) {
		$filter &= self::FILTER_ALL;
		return ($this->filter & $filter) == $filter;
	}

	public function setFilter($filter) {
		$this->filter = $filter & self::FILTER_ALL;
		return $this;
	}

	public function addFilter($filter) {
		$this->filter |= $filter & self::FILTER_ALL;
		return $this;
	}

	public function removeFilter($filter) {
		$this->filter &= ~($filter & self::FILTER_ALL);
		return $this;
	}

	public function optimizeFor(Canvas $canvas, $allowedFilter = self::FILTER_ALL) {
		$this->setCompression(9);

		$combos = array(0);
		foreach(array(self::FILTER_SUB, self::FILTER_UP, self::FILTER_AVG, self::FILTER_PAETH) as $filter) {
			if($filter & $allowedFilter) foreach($combos as $combo) {
				$combos[] = $combo | $filter;
			}
		}
		$bestCombo = 0;
		$bestLength = PHP_INT_MAX;
		foreach($combos as $combo) {
			$this->setFilter($combo);
			$length = strlen($this->getBinary($canvas));
			if($length < $bestLength) {
				$bestCombo = $combo;
				$bestLength = $length;
			}
		}
		$this->setFilter($bestCombo);

		return $this;
	}

	public function getBinary(Canvas $canvas, $optimize = false, $allowedFilter = self::FILTER_ALL) {
		$canvas->requireValid();
		$optimize && $this->optimizeFor($canvas, $allowedFilter);
		$self = $this;
		return $this->createBinary(function() use($self, $canvas) {
			return imagepng($canvas->getResource(), null, $self->getCompression(), $self->getFilter());
		});
	}

}
