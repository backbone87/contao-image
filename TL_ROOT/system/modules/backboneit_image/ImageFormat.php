<?php

abstract class ImageFormat {
	
	const DEFAULT_FORMAT_CLASS = 'PNGFormat';
	
	public static function autoload(File $objFile) {
		return ImageFactory::createFromResource(imagecreatefromstring($objFile->getContent()));
	}
	
	public static function autostore(Image $objImage, File $objFile, $strFormatClass = null) {
		if($strFormatClass && is_subclass_of($strFormatClass, __CLASS__)) {
			$strClass = $strFormatClass;
		} elseif(self::$arrFormatClassesByExtension[$objFile->extension]) {
			$strClass = self::$arrFormatClassesByExtension[$objFile->extension];
		} else {
			$strClass = self::DEFAULT_FORMAT_CLASS;
		}
		
		$objFormat = new $strClass();
		return $objFormat->store($objImage, $objFile);
	}
	
	private static $arrFormatClassesByExtension = array(
		'jpg'	=> 'JPEGFormat',
		'jpeg'	=> 'JPEGFormat',
		'gif'	=> 'GIFFormat',
		'png'	=> 'PNGFormat',
		'wbmp'	=> 'WBMPFormat'
	);
	
	public static function registerFormatClass($strClass, $varExtensions) {
		if(!is_subclass_of($strClass, __CLASS__))
			return false;
			
		foreach((array) $varExtensions as $strExtension)
			self::$arrFormatClassesByExtension[$strExtension] = $strClass;
			
		return true;
	}
	
	
	
	protected function __construct() {
	}
	
	public function store(Image $objImage, File $objFile) {
		$binImage = $this->getBinary($objImage);
		
		if(!$objFile->write($binImage)) {
			throw new Exception(sprintf(
				'ImageFormat::store(): Failed to write image data to file, given file [%s], format class [%s].',
				$objFile->value,
				get_class($this)
			));
		}
		
		return strlen($binImage);
	}
	
	public function load(File $objFile) {
		return ImageFactory::createFromResource(call_user_func($this->getLoadFunction(), TL_ROOT . '/' . $objFile->value));
	}
	
	public function getBinary(Image $objImage) {
		$objImage->checkResource();
		
		if(func_num_args() > 1) {
			$arrArgs = func_get_args();
			$arrArgs[0] = $objImage->getResource();
			array_splice($arrArgs, 1, 0, array(null));
		} else {
			$arrArgs = array($objImage->getResource());
		}
		
		ob_start();
		if(!@call_user_func_array($this->getStoreFunction(), $arrArgs)) {
			ob_end_clean();
			throw new Exception(sprintf(
				'ImageFormat::getBinary(): Failed to create image data. Original message [%s].',
				$php_errormsg
			));
		}
		return ob_get_clean();
	}
	
	public abstract function getStoreFunction();
	
	public abstract function getLoadFunction();
	
}
