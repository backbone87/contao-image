<?php

class TrueColorImage extends Image {
	
	public function __construct($resImage) {
		parent::__construct($resImage);
		if(!imageistruecolor($resImage))
			throw new InvalidArgumentException('TrueColorImage::__construct(): #1 $resImage is not a gdlib true color image');
	}
	
	public function __clone() {
		$objTarget = ImageFactory::createTrueColorImage($this->getSize());
		
        imagecopy($objTarget->getResource(), $this->getResource(),
        	0, 0,
        	0, 0,
        	$this->objSize->getWidth(), $this->objSize->getHeight()
        );
        
		return $objTarget;
	}
	
	public function getColorIndex(Color $objColor, $blnAllocate = true, $blnExact = true) {
		return ($objColor->getRed() << 16)
			| ($objColor->getGreen() << 8)
			| $objColor->getBlue()
			| (round($objColor->getAlpha() / 2) << 24);
			
//		return imagecolorallocatealpha($this->resImage, $objColor->getRed(), $objColor->getGreen(), $objColor->getBlue(), round($objColor->getAlpha() / 2));
	}
	
	public function getColor($intIndex) {
		return new Color(
			($intIndex >> 16) & 0xFF,
			($intIndex >> 8) & 0xFF,
			$intIndex & 0xFF,
			(($intIndex >> 24) & 0x7F) * 2
		);
	}
	
	public function getTransparentColor() {
		return new Color(0, 0, 0, 255);
	}
	
	public function toPaletteImage($blnDither = false, $intNumColors = 255) {
		$objTarget = clone $this;
		$intNumColors = max(min(intval($intNumColors), 256), 1);
		
		if(!imagetruecolortopalette($objTarget->getResource(), !!$blnDither, $intNumColors)) {
			throw new RuntimeException(); // TODO exception text
		}
		imagecolormatch($this->getResource(), $objTarget->getResource());
		
		return new PaletteImage($objTarget->invalidate());
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
