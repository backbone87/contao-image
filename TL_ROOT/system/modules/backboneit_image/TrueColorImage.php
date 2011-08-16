<?php

class TrueColorImage extends Image {

	/**
	 * @see Image::createEmpty()
	 */
	public static function createEmpty(Size $objSize) {
		GdLib::checkLoaded();
		GdLib::checkSizeAllowed($objSize);
		$objSize->checkNonNullArea();
		
		$resImage = @imagecreatetruecolor($objSize->getWidth(), $objSize->getHeight());
		
		if(!$resImage) {
			throw new RuntimeException(sprintf(
				'Image::createEmpty(): Failed to create empty image. Original message [%s].',
				$php_errormsg
			));
		}
		
		return new self($resImage);
	}
	
	public function __construct($resImage) {
		parent::__construct($resImage);
		if(!imageistruecolor($resImage))
			throw new InvalidArgumentException('TrueColorImage::__construct(): #1 $resImage is not a gdlib true color image');
	}
	
	public function getColorIndex(Color $objColor) {
		return imagecolorallocate($this->resImage, $objColor->getRed(), $objColor->getGreen(), $objColor->getBlue(), round($objColor->getAlpha() / 2));
	}
	
	public function toPaletteImage($blnDither = false, $intNumColors = 256) {
		$intNumColors = max(min(intval($intNumColors), 256), 1);
		return new PaletteImage(imagetruecolortopalette($this->resImage, !!$blnDither, $intNumColors));
	}
	
	public function toTrueColorImage() {
		return $this;
	}
	
	public final function isPaletteImage() {
		return false;
	}
	
	public final function isTrueColorImage() {
		return true;
	}
	
}
