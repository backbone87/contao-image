<?php

namespace bbit\image\op;

use bbit\image\Canvas;
use bbit\image\CanvasFactory;
use bbit\image\util\Color;
use bbit\image\util\Point2D;
use bbit\image\util\Size;

class ResampleOp extends CanvasOp {

	protected $dst;

	protected $dstSize;

	protected $dstPoint;

	protected $srcSize;

	protected $srcPoint;

	protected $alphaBlending = false;

	public function __construct(Canvas $objOriginal = null, $blnOriginalImmutable = true) {
		parent::__construct($objOriginal, $blnOriginalImmutable);
	}

	public function setDst(Canvas $dst = null) {
		$this->dst = $dst;
		return $this;
	}

	public function setDstArea(Size $dstSize = null, Point2D $dstPoint = null) {
		$this->dstSize = $dstSize;
		$this->dstPoint = $dstPoint;
		return $this;
	}

	public function setSrcArea(Size $srcSize = null, Point2D $srcPoint = null) {
		$this->srcSize = $srcSize;
		$this->srcPoint = $srcPoint;
		return $this;
	}

	public function setAlphaBlending($alphaBlending = false) {
		$this->alphaBlending = (bool) $alphaBlending;
		return $this;
	}

	public function isAlphaBlending() {
		return $this->alphaBlending;
	}

	protected function perform(Canvas $src) {
		$srcPoint = $this->srcPoint ? $this->srcPoint : Point2D::zero();
		$srcSize = $this->srcSize ? $this->srcSize : $src->getSize()->toPoint2D()->sub($srcPoint)->toSize();

		$srcSize->requireNonNullArea();
		$src->getSize()->requireValidSubArea($srcSize, $srcPoint);

		$dstPoint = $this->dstPoint ? $this->dstPoint : Point2D::zero();
		$dstSize = $this->dstSize ? $this->dstSize : $srcSize;

		$dstSize->requireNonNullArea();

		$dst = $this->dst
			? $this->dst->toTrueColorCanvas()
			: CanvasFactory::createTrueColorCanvas($dstSize->toPoint2D()->add($dstPoint)->toSize());

		$dst->getSize()->requireValidSubArea($dstSize, $dstPoint);

		$dstResource = $dst->getResource();
		$alphaBlending = $dst->getAlphaBlending();


		$dst->setAlphaBlending(true);
		$br = $dst->getSize()->getBottomRight();
		imagefilledrectangle(
			$dstResource,
			0, 0,
			$br->getX(), $br->getY(),
			$dst->getColorIndex(Color::create(0, 0, 0, 255))
		);
		$dst->setAlphaBlending($this->alphaBlending);

		imagecopyresampled(
			$dstResource, $src->getResource(),
			$dstPoint->getX(), $dstPoint->getY(),
			$srcPoint->getX(), $srcPoint->getY(),
			$dstSize->getWidth(), $dstSize->getHeight(),
			$srcSize->getWidth(), $srcSize->getHeight()
		);

		$dst->setAlphaBlending($alphaBlending);

		return $dst;
	}

	public function modifiesOriginal($src) {
		return $this->dst && $this->dst->getResource() === $src->getResource();
	}

}
