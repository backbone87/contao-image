<?php

namespace bbit\image\op;

use bbit\image\Canvas;
use bbit\image\util\Size;
use bbit\image\util\Point2D;

/**
 * <p>
 * Returns an <tt>Canvas</tt> object containing a scaled version of the image
 * denoted by <tt>$objSource</tt>.
 * </p>
 * <p>
 * If no width or height is given this method behaves exactly like
 * <tt>Canvas::createFromFile()</tt> with argument <tt>$objSource</tt>
 * supplied. The <tt>$strMode</tt> argument is ignored.
 * </p>
 * <p>
 * If either <tt>$numWidth</tt> or <tt>$numHeight</tt> is given, the
 * particular value will be the used for the thumb image and the other is
 * calculated to match the ratio of the original image. The
 * <tt>$strMode</tt> argument is ignored.
 * </p>
 * <p>
 * If both values are given and <tt>$strMode</tt> is <tt>''</tt>, the image
 * is proportionally scaled down as long as one value will match the target
 * size and the other is greater or equal than the target size. After that
 * the image is cropped to match the target size.
 * </p>
 * <p>
 * If both values are given and <tt>$strMode</tt> is <tt>'box'</tt>, the
 * image is proportionally scaled down as long as both values are less or
 * equal than the target size.
 * </p>
 * <p>
 * If both values are given and <tt>$strMode</tt> is <tt>'none'</tt>, the
 * image is scaled down to match the given width and height, without respect
 * to the original aspect ratio of the image.
 * </p>
 * <p>
 * The size of the source image must not exceed MAX_SIZE.<br />
 * The given target size must not exceed MAX_SIZE.
 * </p>
 *
 * @param mixed $objSource
 * 			An image object, a path-<tt>string</tt> (relative to
 * 			<tt>TL_ROOT</tt> or absolute) or <tt>File</tt> object denoting
 * 			an image file in filesystem.
 * @param number $numWidth
 * 			Optional. Defaults to null.
 * 			The width with <tt>$numWidth >= 1</tt>, that the thumb should
 * 			fit.
 * @param number $numHeight
 * 			Optional. Defaults to null.
 * 			The height with <tt>$numHeight >= 1</tt>, that the thumb should
 * 			fit.
 * @param string $strMode
 * 			Optional. Defaults to <tt>''</tt> (the empty string).
 * 			One of <tt>'box'</tt>, <tt>'none'</tt> or <tt>''</tt>.
 * @return Canvas
 * 			The <tt>Canvas</tt> object of the thumb-image.
 * @throws Exception
 * 			If the gd-library is not loaded.
 * 			If arg-check fails.
 * 			If image-format is not supported.
 * 			If size of the given image is larger than MAX_SIZE.
 * 			If target size is larger than MAX_SIZE.
 * 			If image-creation fails.
 *
 */
class ThumbnailOp extends CanvasOp {

	/**
	 * Thumbnail generation modes.
	 *
	 * "CROP"
	 * Scales down the original until it matches at least one side of the
	 * requested size and finally crops the image centrally, if needed.
	 *
	 * "FILL"
	 * Same as "CROP" but does not crops the image. Can result in a larger area
	 * as the requested.
	 *
	 * "FIT"
	 * Scales down the original until both sides are smaller than or equal to
	 * the requested size. Can result in a smaller area as the requested.
	 *
	 */
	const MODE_CROP	= 'crop';
	const MODE_FILL	= 'fill';
	const MODE_FIT	= 'fit';
	const MODE_FIT_UPSCALE = 'fitUpscale';

	/** @var boolean */
	private $upscale = false;
	/** @var string */
	private $mode = self::MODE_CROP;
	/** @var \bbit\image\util\Size */
	private $targetSize;
	/** @var \bbit\image\util\Size */
	private $sourceSize;
	/** @var \bbit\image\util\Point2D */
	private $sourceOffset;

	public function __construct() {
		parent::__construct();
	}

	public function getUpscale() {
		return $this->upscale;
	}

	public function setUpscale($upscale) {
		$this->upscale = (bool) $upscale;
		return $this;
	}

	public function getMode() {
		return $this->mode;
	}

	public function setMode($mode) {
		$this->mode = $mode;
		return $this;
	}

	public function getTargetSize() {
		return $this->targetSize;
	}

	public function setTargetSize(Size $targetSize) {
		$this->targetSize = $targetSize;
		return $this;
	}

	public function getSourceSize() {
		return $this->sourceSize;
	}

	public function setSourceSize(Size $sourceSize) {
		$this->sourceSize = $sourceSize;
		return $this;
	}

	public function getSourceOffset() {
		return $this->sourceOffset;
	}

	public function setSourceOffset(Size $sourceOffset) {
		$this->sourceOffset = $sourceOffset;
		return $this;
	}

	public function execute() {
		$subject = $this->prepareSubject();

		$sourceSize = $this->getSourceSize();
		$sourceSize || $sourceSize = $subject->getSize();
		$sourceOffset = $this->getSourceOffset();
		$sourceOffset || $sourceOffset = Point2D::zero();

		$targetSize = $this->getTargetSize();
		switch($targetSize->getArea() ? $this->getMode() : self::MODE_FILL) {
			case self::MODE_CROP:
				$origSize = $sourceSize;
				$sourceSize = $origSize->ratiofyDown($targetSize->getRatio());
				$sourceOffset = $origSize->centerize($sourceSize)->add($sourceOffset);
				break;

			case self::MODE_FILL:
				$targetSize = $targetSize->ratiofyUp($sourceSize->getRatio());
				break;

			case self::MODE_FIT:
				$targetSize = $targetSize->ratiofyDown($sourceSize->getRatio())->scaleToWrap($sourceSize);
				break;

			case self::MODE_FIT_UPSCALE:
				$targetSize = $targetSize->ratiofyDown($sourceSize->getRatio());
				break;

			default:
				throw new \LogicException(sprintf('Unknown thumbnail mode [%s]', $this->getMode()));
				break;
		}

		$op = new ResampleOp();
		$op->setTargetSize($targetSize);
		$op->setSubject($subject);
		$op->setSourceSize($sourceSize);
		$op->setSourceOffset($sourceOffset);
		return $op->execute();
	}

	public function isModifyingSubject() {
		return false;
	}

}
