<?php

class TrueColorImage extends Image {
	
	public function __construct($resImage) {
		parent::__construct($resImage);
		if(!imageistruecolor($resImage))
			throw new InvalidArgumentException('TrueColorImage::__construct(): #1 $resImage is not a gdlib true color image');
	}
	
	public function __clone() {
		
	}
	
	public function getColorIndex(Color $objColor) {
		return imagecolorallocatealpha($this->resImage, $objColor->getRed(), $objColor->getGreen(), $objColor->getBlue(), round($objColor->getAlpha() / 2));
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
