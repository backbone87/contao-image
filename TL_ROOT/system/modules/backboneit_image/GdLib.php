<?php

abstract class GdLib {

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
	
	public static function getCreateFunByType() {
		return 
	}
	
	/**
	 * @var array
	 * 			Maps file-extensions to image-creation functions.
	 */
	private static $funCreateFrom = array(
		'jpg'	=> 'imagecreatefromjpeg',
		'jpeg'	=> 'imagecreatefromjpeg',
		'gif'	=> 'imagecreatefromgif',
		'png'	=> 'imagecreatefrompng',
		'wbmp'	=> 'imagecreatefromwbmp'
	);
	
    /**
     * @var array
     * 			Maps file-extensions to image-storage functions.
     */
    protected static $funStore = array(
		'jpg'	=> 'imagejpeg',
		'jpeg'	=> 'imagejpeg',
		'gif'	=> 'imagegif',
		'png'	=> 'imagepng',
		'wbmp'	=> 'imagewbmp'
	);
	
}
