<?php

namespace bbit\image\format;

use bbit\image\Canvas;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class WBMPFormat extends ImageFormat {

	public function __construct() {
		parent::__construct();
	}

	public function getBinary(Canvas $canvas) {
		$self = $this;
		return $this->createBinary(function() use($self, $canvas) {
			return imagewbmp($canvas->getResource(), null);
		});
	}

}
