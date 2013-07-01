<?php

namespace bbit\image\format;

use bbit\image\Canvas;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class JPEGFormat extends ImageFormat {

	private $quality;

	public function __construct($quality = 80) {
		parent::__construct();
		$this->setQuality($quality);
	}

	public function getQuality() {
		return $this->quality;
	}

	public function setQuality($quality) {
		$this->quality = min(max(intval($quality), 0), 100);
		return $this;
	}

	public function getBinary(Canvas $canvas) {
		$self = $this;
		return $this->createBinary(function() use($self, $canvas) {
			return imagejpeg($canvas->getResource(), null, $self->getQuality());
		});
	}

	public function getMIMEType() {
		return 'image/jpeg';
	}

}
