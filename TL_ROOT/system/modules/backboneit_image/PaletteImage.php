<?php

class PaletteImage extends Image {
	
	public function __construct($resImage) {
		parent::__construct($resImage);
		if(imageistruecolor($resImage))
			throw new InvalidArgumentException('TrueColorImage::__construct(): #1 $resImage is not a gdlib palette image');
	}
	
	public function __clone() {
		$objSize = $this->getSize();
		$objTarget = ImageFactory::createPaletteImage($objSize);
		
		imagepalettecopy($objTarget->getResource(), $this->getResource());
		
        imagecopy($objTarget->getResource(), $this->getResource(),
        	0, 0,
        	0, 0,
        	$objSize->getWidth(), $objSize->getHeight()
        );
        
		return $objTarget;
	}
	
	public function getColorIndex(Color $objColor, $blnAllocate = true, $blnExact = true) {
		$blnAlpha = $objColor->getAlpha();
		$intColor = call_user_func($blnAlpha ? 'imagecolorexactalpha' : 'imagecolorexact',
			$this->getResource(), $objColor->getRed(), $objColor->getGreen(), $objColor->getBlue(), round($objColor->getAlpha() / 2));

		if($intColor !== false)
			return $intColor;

		if($blnAllocate && imagecolorstotal($this->getResource()) < 255)
			return call_user_func($blnAlpha ? 'imagecolorallocatealpha' : 'imagecolorallocate',
				$this->getResource(), $objColor->getRed(), $objColor->getGreen(), $objColor->getBlue(), round($objColor->getAlpha() / 2));
			
		if(!$blnExact) // TODO use lab and delta e
			return call_user_func($blnAlpha ? 'imagecolorclosestalpha' : 'imagecolorclosest',
				$this->getResource(), $objColor->getRed(), $objColor->getGreen(), $objColor->getBlue(), round($objColor->getAlpha() / 2));
				
		throw new Exception('PaletteImage::getColorIndex(): Failed to allocate color.'); // TODO
	}
	
	public function getColor($intIndex) {
		if(is_int($intIndex) && $intIndex >= 0 && $intIndex < imagecolorstotal($this->getResource()))
			return Color::createFromAssoc(imagecolorsforindex($this->getResource(), $intIndex));
	}
	
	public function getTransparentColor() {
		return $this->getColor(imagecolortransparent($this->getResource()));
	}
	
	public function toPaletteImage($blnDither = false, $intNumColors = 255) {
		return $this;
	}
	
	public function toTrueColorImage() {
		$objSize = $this->getSize();
		$objTarget = ImageFactory::createTrueColorImage($objSize);
		
        imagecopy($objTarget->getResource(), $this->getResource(),
        	0, 0,
        	0, 0,
        	$objSize->getWidth(), $objSize->getHeight()
        );
        	
		return $objTarget;
	}
	
	public final function isPaletteImage() {
		return true;
	}
	
	public final function isTrueColorImage() {
		return false;
	}
	
}
