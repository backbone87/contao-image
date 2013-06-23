<?php

namespace bbit\image;

use bbit\image\util\Point2D;

use bbit\image\op\ResampleOp;

use bbit\image\util\Size;

use bbit\image\Canvas;

class WatermarkOp extends CanvasOp {

	const POSITION_TOP_LEFT		= 0x0001;
	const POSITION_TOP			= 0x0002;
	const POSITION_TOP_RIGHT	= 0x0004;
	const POSITION_LEFT			= 0x0010;
	const POSITION_CENTER		= 0x0020;
	const POSITION_RIGHT		= 0x0040;
	const POSITION_BOTTOM_LEFT	= 0x0100;
	const POSITION_BOTTOM		= 0x0200;
	const POSITION_BOTTOM_RIGHT	= 0x0400;
	const POSITION_ALL			= 0x0777;

	private $watermark;

	private $position = self::POSITION_BOTTOM_RIGHT;

	private $refSize;

	public function __construct() {
		parent::__construct();
	}

	public function getWatermark() {
		return $this->watermark;
	}

	public function setWatermark(Canvas $watermark) {
		$this->watermark = $watermark;
		return $this;
	}

	public function getPosition() {
		return $this->position;
	}

	public function hasPosition($position) {
		return (bool) ~($this->position ^ ($position & self::POSITION_ALL));
	}

	public function setPosition($position) {
		$this->position = $position;
		return $this;
	}

	public function addPosition($position) {
		$this->position |= $position & POSITION_ALL;
		return $this;
	}

	public function removePosition($position) {
		$this->position &= ~$position;
		return $this;
	}

	public function getRefSize() {
		return $this->refSize;
	}

	public function setRefSize(Size $size) {
		$this->refSize = $refSize;
		return $this;
	}

	public function getScaleMode() {
		return $this->scaleMode;
	}

	public function setScaleMode($scaleMode) {
		$this->scaleMode = $scaleMode;
		return $this;
	}

	public function perform(Canvas $source) {
		if(!$this->getPosition() || !$this->getWatermark()) {
			return;
		}

		$op = new ResampleOp();
		$op->setAlphaBlending(true);
		$op->setDst($source);

		$target = $source->getSize();
		$watermark = $this->getWatermark();

		$size = $watermark->getSize()->scaleToFit($target);
		$op->setDstArea();

		$offset = Point2D::zero();

		for($i = 0; $i < 24; $i++) {
			$position = 1 << $i;
			if(!$this->hasPosition($position)) {
				continue;
			}

			if($position & 0x0111) { // LEFT ALIGN
				$offset = $offset->setX(0);

			} elseif($position & 0x0222) { // CENTER ALIGN
				$offset = $offset->setX(round(($target->getWidth() - $size->getWidth()) / 2));

			} else { // RIGHT ALIGN
				$offset = $offset->setX($target->getWidth() - $size->getWidth());
			}

			if($position & 0x0007) { // TOP ALIGN
				$offset = $offset->setY(0);

			} elseif($position & 0x0070) { // MIDDLE ALIGN
				$offset = $offset->setY(round(($target->getHeight() - $size->getHeight()) / 2));

			} else { // BOTTOM ALIGN
				$offset = $offset->setY($target->getHeight() - $size->getHeight());
			}

			$op->setDstArea($target, $offset);
			$op->execute($watermark);
		}
	}

}
