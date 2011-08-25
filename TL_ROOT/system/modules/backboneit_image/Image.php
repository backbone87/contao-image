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
	
	private $strStorageFormat;
		
	protected function __construct($resImage, $strStorageFormat = self::PNG) {
		if(!@imagesx($resImage))
			throw new InvalidArgumentException('Image::__construct(): #1 $resImage is not a valid gdlib image ressource.');
		
		$this->resImage = $resImage;
		$this->objSize = new Size(imagesx($resImage), imagesy($resImage));
		$this->setStorageFormat($strStorageFormat);
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
	
	public function getRessource() {
		return is_ressource($this->resImage) ? $this->resImage : null;
	}
	
	public function getStorageFormat() {
		return $this->strStorageFormat;
	}
	
	public function setStorageFormat($strStorageFormat = self::PNG) {
		
	}
	
	/**
	 * <p>
	 * Stores this image as a file in filesystem. If <tt>$strType</tt> is
	 * <tt>null</tt>, the image format is derived from the file-extension.
	 * If the resulting image format is not supported, falls back to PNG format.
	 * In this case, the resulting file is properly renamed.
	 * </p>
	 * 
	 * @param mixed $objFile
	 * 			A path-<tt>string</tt> (relative to <tt>TL_ROOT</tt> or
	 * 			absolute) or <tt>File</tt> object denoting an image file in
	 * 			filesystem.
	 * @param number $numQuality
	 * 			Optional. Defaults to <tt>90</tt>.
	 * 			A number specifying the quality of the stored image, if
	 * 			JPG-format is used, or the compression level, if PNG-format is
	 * 			used. (Higher is better quality / less compression.) Any number
	 * 			less than 1 will cause the default value to be used. Any number
	 * 			greater than 100 is treated like the value 100.
	 * @param boolean $blnForce
	 * 			Optional. Defaults to <tt>true</tt>.
	 * 			If <tt>$objFile</tt> is a path-string and <tt>$blnForce</tt> is
	 * 			<tt>true</tt>, the file denoted will be deleted, if it exists,
	 * 			and a new empty file will be created to store the image to.
	 * 			If <tt>$objFile</tt> is a path-string and <tt>$blnForce</tt> is
	 * 			<tt>false</tt>, the image bytes will be appended to the denoted
	 * 			file, if it exists, or an exception is thrown.
	 * 			This argument has no effect, if a <tt>File</tt> object is
	 * 			supplied via <tt>$objFile</tt>.
	 * @param string $strType
	 * 			Optional. Defaults to <tt>null</tt>.
	 * 			One of <tt>'jpg'</tt>, <tt>'gif'</tt>, <tt>'png'</tt> or
	 * 			<tt>'wbmp'</tt>.
	 * @return File
	 * 			The <tt>File</tt> object representing the newly created
	 * 			imagefile.
	 * @throws Exception
	 * 			If given file could not be opened to write to.
	 * 			If image-bytes could not be created.
	 * 			If image-bytes could not be written.
	 */
	public function store($varFile = null, $numQuality = 90, $blnOverwrite = true, $strType = null) {
		if($varFile) {
			$objFile = self::getFile($varFile, $blnOverwrite);
			if(!$strType) { $strType = $objFile->extension; }
		}
		
		self::checkType($strType); 
		
		$numQuality = $numQuality > 1 ? $numQuality > 100 ? 100 : $numQuality : 90;
		if($strType == self::PNG) { $numQuality = 10 - ceil($numQuality / 10); }
		
		$funStore = self::$funStore[$strType];
		
		ob_start();
		if(!@call_user_func(GdLib::getStoreFunByType($strType), $this->resImage, null, $strType == self::WBMP ? null : intval($numQuality))) {
			ob_end_clean();
			throw new Exception(sprintf(
				'Image->store(): Failed to create image data, given format [%s]. Original message [%s].',
				$strType,
				$php_errormsg
			));
		}
		$binImage = ob_get_clean();
		
		if($objFile) {
			try {
				$objFile->write($binImage);
			} catch(Exception $e) {
				throw new Exception(sprintf(
					'Image->store(): Failed to store image data to file, given file [%s], format [%s]. Original message [%s].',
					$objFile->value,
					$strType,
					$e->getMessage()
				));
			}
			return $objFile;
		}
		
		return $binImage;
	}
	
	public abstract function getColorIndex(Color $objColor, $blnAllocate = true, $blnExact = true);
	
	public abstract function toPaletteImage();
	
	public abstract function toTrueColorImage();
	
	public abstract function isPaletteImage();
	
	public abstract function isTrueColorImage();
	
}
