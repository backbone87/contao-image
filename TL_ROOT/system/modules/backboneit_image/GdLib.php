<?php

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
		
		return self::$arrSupported = array(
	    	'jpg'	=> $intSupported & IMG_JPG,
	    	'jpeg'	=> $intSupported & IMG_JPG,
	    	'gif'	=> $intSupported & IMG_GIF,
	    	'png'	=> $intSupported & IMG_PNG,
	    	'wbmp'	=> $intSupported & IMG_WBMP
		);
	}
	
	public static function isTypeSupported($strType) {
		$arrSupported = self::getSupportedTypes();
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
	
	
	
	/**
	 * @var array
	 * 			Maps file-extensions to image-creation functions.
	 */
	private static $arrCreateFunByType = array(
		'jpg'	=> 'imagecreatefromjpeg',
		'jpeg'	=> 'imagecreatefromjpeg',
		'gif'	=> 'imagecreatefromgif',
		'png'	=> 'imagecreatefrompng',
		'wbmp'	=> 'imagecreatefromwbmp'
	);
	
	public static function getCreateFunByType($strType) {
		return self::$arrCreateFunByType[$strType];
	}
	
    /**
     * @var array
     * 			Maps file-extensions to image-storage functions.
     */
    protected static $arrStoreFunByType = array(
		'jpg'	=> 'imagejpeg',
		'jpeg'	=> 'imagejpeg',
		'gif'	=> 'imagegif',
		'png'	=> 'imagepng',
		'wbmp'	=> 'imagewbmp'
	);
	
	public static function getStoreFunByType($strType) {
		return self::$arrStoreFunByType[$strType];
	}
	
	public static function getMaxAreaAllowed() {
		return max(4000000, $GLOBALS['TL_CONFIG']['backboneit_image_maxsize']);
	}

	public static function isSizeAllowed(Size $objSize) {
		return $objSize->getArea() <= self::getMaxAllowedArea();
	}
	
	public static function checkSizeAllowed(Size $objSize) {
		if(!$objSize->isAllowed()) {
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
