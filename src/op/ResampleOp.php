<?php

namespace bbit\image\op;

use bbit\image\Canvas;
use bbit\image\CanvasFactory;
use bbit\image\util\Color;
use bbit\image\util\Point2D;
use bbit\image\util\Size;

class ResampleOp extends CanvasOp {

	private $target;

	private $targetSize;

	private $targetOffset;

	private $sourceSize;

	private $sourceOffset;

	private $alphaBlending = false;

	public function __construct() {
		parent::__construct();
	}

	public function getTarget() {
		return $this->target;
	}

	public function setTarget(Canvas $target = null) {
		$this->target = $target;
		return $this;
	}

	public function getTargetSize() {
		return $this->targetSize;
	}

	public function setTargetSize(Size $targetSize = null) {
		$this->targetSize = $targetSize;
		return $this;
	}

	public function getTargetOffset() {
		return $this->targetOffset;
	}

	public function setTargetOffset(Point2D $targetOffset = null) {
		$this->targetOffset = $targetOffset;
		return $this;
	}

	public function getSourceSize() {
		return $this->sourceSize;
	}

	public function setSourceSize(Size $sourceSize = null) {
		$this->sourceSize = $sourceSize;
		return $this;
	}

	public function getSourceOffset() {
		return $this->sourceOffset;
	}

	public function setSourceOffset(Point2D $sourceOffset = null) {
		$this->sourceOffset = $sourceOffset;
		return $this;
	}

	public function getAlphaBlending() {
		return $this->alphaBlending;
	}

	public function setAlphaBlending($alphaBlending = false) {
		$this->alphaBlending = (bool) $alphaBlending;
		return $this;
	}

	public function execute() {
		$subject = $this->prepareSubject();

		$sourceOffset = $this->getSourceOffset();
		$sourceOffset || $sourceOffset = Point2D::zero();
		$sourceSize = $this->getSourceSize();
		$sourceSize || $sourceSize = $subject->getSize()->toPoint2D()->sub($sourceOffset)->toSize();

		$sourceSize->requireNonNullArea();

		$targetOffset = $this->getTargetOffset();
		$targetOffset || $targetOffset = Point2D::zero();
		$targetSize = $this->getTargetSize();
		$targetSize || $targetSize = $sourceSize;

		$targetSize->requireNonNullArea();

		$target = $this->getTarget();
		$target || $target = CanvasFactory::createTrueColorCanvas($targetSize->toPoint2D()->add($targetOffset)->toSize());
		$target = $target->toTrueColorCanvas();

		if(!$target->getSize()->isIntersectedByArea($targetSize, $targetOffset)) {
			return $target;
		}

		$targetResource = $target->getResource();
		$alphaBlending = $target->getAlphaBlending();

		$target->setAlphaBlending(true);
		$br = $target->getSize()->getBottomRight();
		imagefilledrectangle(
			$targetResource,
			0, 0,
			$br->getX(), $br->getY(),
			$target->getColorIndex(Color::create(0, 0, 0, 255))
		);

		$target->setAlphaBlending($this->getAlphaBlending());
		imagecopyresampled(
			$targetResource, $subject->getResource(),
			$targetOffset->getX(), $targetOffset->getY(),
			$sourceOffset->getX(), $sourceOffset->getY(),
			$targetSize->getWidth(), $targetSize->getHeight(),
			$sourceSize->getWidth(), $sourceSize->getHeight()
		);

		$target->setAlphaBlending($alphaBlending);

		return $target;
	}

	public function isModifyingSubject() {
		return $this->getTarget() && $this->getTarget()->getResource() === $this->getSubject()->getResource();
	}

}
