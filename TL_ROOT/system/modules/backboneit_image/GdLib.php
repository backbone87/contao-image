<?php

//namespace backboneit\image;

abstract class GdLib {

	public static function isLoaded() {
		return extension_loaded('gd');
	}
	
	public static function checkLoaded() {
		if(!self::isLoaded()) {
			throw new RuntimeException('Image::checkGdLib(): gdlib not loaded.');
		}
	}
	
	
	
	private static $arrSupported; 
	
	public static function getSupportedTypes() {
		if(isset(self::$arrSupported))
			return self::$arrSupported;
			
		$intSupported = imagetypes();
		
		return self::$arrSupported = array_filter(array(
	    	'png'	=> $intSupported & IMG_PNG,
	    	'jpg'	=> $intSupported & IMG_JPG,
	    	'gif'	=> $intSupported & IMG_GIF,
	    	'wbmp'	=> $intSupported & IMG_WBMP
		));
	}
	
	public static function isTypeSupported($strType) {
		$arrSupported = self::getSupportedTypes();
		$strType == 'jpeg' && $strType = 'jpg';
		return $arrSupported[$strType];
	}
	
	public static function checkTypeSupported($strType) {
		if(!self::isTypeSupported($strType)) {
			throw new RuntimeException(sprintf(
				'Image::checkType(): Type [%s] not supported.',
				$strType
			));
		}
	}
	
	
	
	public static function getMaxAreaAllowed() {
		return max(4000000, $GLOBALS['TL_CONFIG']['backboneit_image_maxsize']);
	}

	public static function isSizeAllowed(Size $objSize) {
		return $objSize->getArea() <= self::getMaxAreaAllowed();
	}
	
	public static function checkSizeAllowed(Size $objSize) {
		if(!self::isSizeAllowed($objSize)) {
			throw new Exception(sprintf(
				'Size::checkAllowed(): Area of size [%s] exceeds max allowed value of [%s]. Width [%s], height [%s].',
				$objSize->getArea(),
				max(4000000, $GLOBALS['TL_CONFIG']['backboneit_image_maxsize']),
				$objSize->getWidth(),
				$objSize->getHeight()
			));
		}
	}
	
}
