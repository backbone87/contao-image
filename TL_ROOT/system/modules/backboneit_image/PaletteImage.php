<?php

class PaletteImage extends Image {
	
	public function __construct($resImage) {
		parent::__construct($resImage);
		if(imageistruecolor($resImage))
			throw new InvalidArgumentException('TrueColorImage::__construct(): #1 $resImage is not a gdlib palette image');
	}
	
	public function __clone() {
		
	}
	
	public function getColorIndex(Color $objColor, $blnAllocate = true, $blnExact = true) {
		$blnAlpha = $objColor->getAlpha();
		$intColor = call_user_func($blnAlpha ? 'imagecolorexactalpha' : 'imagecolorexact',
			$this->resImage, $objColor->getRed(), $objColor->getGreen(), $objColor->getBlue(), round($objColor->getAlpha() / 2));

		if($intColor !== false)
			return $intColor;

		if($blnAllocate && imagecolorstotal($this->resImage) < 255)
			return call_user_func($blnAlpha ? 'imagecolorallocatealpha' : 'imagecolorallocate',
				$this->resImage, $objColor->getRed(), $objColor->getGreen(), $objColor->getBlue(), round($objColor->getAlpha() / 2));
			
		if(!$blnExact) // TODO use lab and delta e
			return call_user_func($blnAlpha ? 'imagecolorclosestalpha' : 'imagecolorclosest',
				$this->resImage, $objColor->getRed(), $objColor->getGreen(), $objColor->getBlue(), round($objColor->getAlpha() / 2));
			
		return $intColor;
	}
	
	public function getTransparentColor() {
		$intTranspIndex = imagecolortransparent($this->resImage);
		
		if($intTranspIndex < 0 && $intTranspIndex >= imagecolorstotal($this->resImage))
			return null;
		
		return Color::createFromAssoc(imagecolorsforindex($this->resImage, $intTranspIndex));
	}
	
	public function toPaletteImage() {
		return $this;
	}
	
	public function toTrueColorImage() {
		$objTarget = TrueColorImage::createEmpty($this->getSize());
		
		return $objTarget;
	}
	
	public final function isPaletteImage() {
		return true;
	}
	
	public final function isTrueColorImage() {
		return false;
	}
	
}
