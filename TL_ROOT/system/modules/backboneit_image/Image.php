<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

//namespace backboneit\image;

/**
 * <p>
 * This class encapsulates an in-memory representation of an image. There are
 * 3 static factory methods to create an <tt>Image</tt> object, useful for the
 * most common cases.
 * </p>
 * <p>
 * Additionally this class offers a static mirror of the Controller->getImage()
 * method.
 * </p>
 * <p>
 * Any method, that require dimension or position arguments, will accept any
 * numeric value (numeric strings, float, integer) as input, unless explicitly
 * stated otherwise. Non-integer numbers are floored.
 * </p>
 *
 * PHP version 5
 * @copyright backboneIT | Oliver Hoff 2010 - Alle Rechte vorbehalten. All rights reserved.
 * @author Oliver Hoff <oliver@hofff.com>
 */
abstract class Image {
	
	/**
	 * Image storage format types.
	 */
	const PNG	= 'png';
	const JPEG	= 'jpg';
	const GIF	= 'gif';
	const WBMP	= 'wbmp';
	
	private $resImage;
	
	private $objSize;
	
	private $blnAlphaBlending = false;
	
	private $blnSaveAlpha = true;
	
	protected function __construct($resImage) {
		if(!@imagesx($resImage))
			throw new InvalidArgumentException('Image::__construct(): #1 $resImage is not a valid gdlib image resource.');
		
		$this->resImage = $resImage;
		$this->objSize = new Size(imagesx($resImage), imagesy($resImage));
		$this->setAlphaBlending(false);
		$this->setSaveAlpha(true);
	}
	
	public function __destruct() {
		@imagedestroy($this->resImage);
	}
	
	public function __toString() {
		return '[Object: Image (' . $this->width . 'x' . $this->height . ')]';
	}
	
	public abstract function __clone();
	
	public function getSize() {
		return $this->objSize;
	}
	
	public function getResource() {
		return $this->resImage;
	}
	
	public function getAlphaBlending() {
		return $this->blnAlphaBlending;
	}
	
	public function setAlphaBlending($blnAlphaBlending) {
		$blnSuccess = imagealphablending($this->resImage, $blnAlphaBlending);
		$blnSuccess && $this->blnAlphaBlending = $blnAlphaBlending;
		return $blnSuccess;
	}
	
	public function getSaveAlpha() {
		return $this->blnSaveAlpha;
	}
	
	public function setSaveAlpha($blnSaveAlpha) {
		$blnSuccess = imagesavealpha($this->resImage, $blnSaveAlpha);
		$blnSuccess && $this->blnSaveAlpha = $blnSaveAlpha;
		return $blnSuccess;
	}
	
	public abstract function getColorIndex(Color $objColor, $blnAllocate = true, $blnExact = true);
	
	public abstract function getColor($intIndex);
	
	public abstract function getTransparentColor();
	
	public abstract function toPaletteImage($blnDither = false, $intNumColors = 255);
	
	public abstract function toTrueColorImage();
	
	public abstract function isPaletteImage();
	
	public abstract function isTrueColorImage();
	
	protected function invalidate() {
		$resImage = $this->resImage;
		$this->resImage = null;
		return $resImage;
	}
	

	public function checkResource() {
		if(!is_resource($this->resImage))
			throw new RuntimeException('Image::checkResource(): Underlying image resource has become invalid. This happens e.g. after imagedestroy is called on the resource.');
	}
	
}
