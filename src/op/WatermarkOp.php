<?php

namespace bbit\image\op;

use bbit\image\Canvas;
use bbit\image\util\Point2D;
use bbit\image\util\Size;

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
		return (bool) ($this->position & intval($position));
	}

	public function setPosition($position) {
		$this->position = intval($position) & self::POSITION_ALL;
		return $this;
	}

	public function addPosition($position) {
		$this->position |= intval($position) & POSITION_ALL;
		return $this;
	}

	public function removePosition($position) {
		$this->position &= ~intval($position);
		return $this;
	}

	/**
	 * @return \bbit\image\util\Size
	 */
	public function getRefSize() {
		return $this->refSize;
	}

	public function setRefSize(Size $refSize) {
		$this->refSize = $refSize;
		return $this;
	}

	public function execute() {
		$subject = parent::prepareSubject();
		$watermark = $this->getWatermark();
		if(!$this->getPosition() || !$watermark) {
			return $subject;
		}

		$subjectSize = $subject->getSize();
		$watermarkSize = $watermark->getSize();
		$refSize = $this->getRefSize() ? $this->getRefSize() : $watermarkSize;
		$refSize->scaleToFit($subjectSize, true, $scale);
		$targetSize = $watermarkSize->scale($scale);

		$op = new ResampleOp();
		$op->setSubject($watermark);
		$op->setAlphaBlending(true);
		$op->setTarget($subject);
		$op->setTargetSize($targetSize);

		for($i = 0; $i < 12; $i++) {
			$position = 1 << $i;
			if(!$this->hasPosition($position)) {
				continue;
			}

			$targetOffset = Point2D::zero();

			if($position & 0x0111) { // LEFT ALIGN
				$targetOffset = $targetOffset->setX(0);

			} elseif($position & 0x0222) { // CENTER ALIGN
				$targetOffset = $targetOffset->setX(round(($subjectSize->getWidth() - $targetSize->getWidth()) / 2));

			} else { // RIGHT ALIGN
				$targetOffset = $targetOffset->setX($subjectSize->getWidth() - $targetSize->getWidth());
			}

			if($position & 0x0007) { // TOP ALIGN
				$targetOffset = $targetOffset->setY(0);

			} elseif($position & 0x0070) { // MIDDLE ALIGN
				$targetOffset = $targetOffset->setY(round(($subjectSize->getHeight() - $targetSize->getHeight()) / 2));

			} else { // BOTTOM ALIGN
				$targetOffset = $targetOffset->setY($subjectSize->getHeight() - $targetSize->getHeight());
			}

			$op->setTargetOffset($targetOffset);
			$op->execute();
		}

		return $subject;
	}

}
