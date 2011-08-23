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
	
	/**
	 * Thumbnail generation modes.
	 * 
	 * "CROP"
	 * Scales down the original until it matches at least one side of the
	 * requested size and finally crops the image centrally, if needed.
	 * 
	 * "FILL"
	 * Same as "CROP" but does not crops the image. Can result in a smaller area
	 * as the requested.
	 * 
	 * "FIT"
	 * Scales down the original until both sides are smaller than or equal to
	 * the requested size. Can result in a smaller area as the requested.
	 * 
	 */
	const CROP	= 0;
	const FILL	= 1;
	const FIT	= 2;
	
	/**
	 * Positioning identifiers.
	 */
	const CENTER		= 1;
	const TOPLEFT		= 2;
	const TOPRIGHT		= 4;
	const BOTTOMRIGHT	= 8;
	const BOTTOMLEFT	= 16;
	
	/**
	 * <p>
	 * Creates a new empty truecolor <tt>Image</tt> object with given width and
	 * height.
	 * </p>
	 * <p>
	 * The given size must not exceed the maximum of $GLOBALS['TL_CONFIG']['backboneit_image_maxsize'] and 4 000 000 pixels.
	 * </p>
	 * 
	 * @param number $numWidth
	 * 			The width of the image-canvas with <tt>$numWidth >= 1</tt>.
	 * @param number $intHeight
	 * 			The height of the image-canvas with <tt>$intHeight >= 1</tt>.
	 * 
	 * @return Image
	 * 			The new <tt>Image</tt> object.
	 * 
	 * @throws RuntimeException
	 * 			If the gd-library is not loaded.
	 *			If given size is larger than currently allowed max size.
	 * 			If image-creation fails.
	 * @throws InvalidArgumentException
	 * 			If intval of $numWidth or $numHeight is less than 1.
	 */
	public static abstract function createEmpty(Size $objSize);
    
	/**
	 * <p>
	 * Creates a new <tt>Image</tt> object from the given file. The canvas of
	 * the <tt>Image</tt> object will have the width, height and contents of the
	 * image described by the file.
	 * </p>
	 * <p>
	 * The size of the image must not exceed MAX_SIZE.
	 * </p>
	 * 
	 * @param mixed $varFile
	 * 			A path-<tt>string</tt> (relative to <tt>TL_ROOT</tt> or
	 * 			absolute) or <tt>File</tt> object denoting an image file in
	 * 			filesystem.
	 * 
	 * @return Image
	 * 			The <tt>Image</tt> object.
	 * 
	 * @throws RuntimeException
	 * 			If the gd-library is not loaded.
	 * 			If arg-check fails.
	 * 			If image-format is not supported.
	 * 			If size of the given image is larger than MAX_SIZE.
	 * 			If image-creation fails.
	 */
	public static function createFromFile($varFile, $strType = null) {
		GdLib::checkLoaded();
		
		$objFile = self::getFile($varFile);
		$strType || $strType = $objFile->extension;
		
		GdLib::checkTypeSupported($strType);
		
		$objSize = Size::createFromFile($objFile);
		GdLib::checkSizeAllowed($objSize);
		
		$resImage = @call_user_func(GdLib::getCreateFunByType($strType), TL_ROOT . '/' . $objFile->value);
		
		if(!$resImage) {
			throw new RuntimeException(sprintf(
				'Image::createFromFile(): Failed to process supplied imagefile. File is maybe damaged or no valid imagefile. Original message [%s].',
				$php_errormsg
			));
		}
		
		$strClass = imageistruecolor($resImage) ? 'TrueColorImage' : 'PaletteImage';
		
		return new $strClass($resImage);
	}
	
	/**
	 * <p>
	 * Returns an <tt>Image</tt> object containing a scaled version of the image
	 * denoted by <tt>$objSource</tt>.
	 * </p>
	 * <p>
	 * If no width or height is given this method behaves exactly like
	 * <tt>Image::createFromFile()</tt> with argument <tt>$objSource</tt>
	 * supplied. The <tt>$strMode</tt> argument is ignored.
	 * </p>
	 * <p>
	 * If either <tt>$numWidth</tt> or <tt>$numHeight</tt> is given, the
	 * particular value will be the used for the thumb image and the other is
	 * calculated to match the ratio of the original image. The
	 * <tt>$strMode</tt> argument is ignored.
	 * </p>
	 * <p>
	 * If both values are given and <tt>$strMode</tt> is <tt>''</tt>, the image
	 * is proportionally scaled down as long as one value will match the target
	 * size and the other is greater or equal than the target size. After that
	 * the image is cropped to match the target size.
	 * </p>
	 * <p>
	 * If both values are given and <tt>$strMode</tt> is <tt>'box'</tt>, the
	 * image is proportionally scaled down as long as both values are less or
	 * equal than the target size.
	 * </p>
	 * <p>
	 * If both values are given and <tt>$strMode</tt> is <tt>'none'</tt>, the
	 * image is scaled down to match the given width and height, without respect
	 * to the original aspect ratio of the image.
	 * </p>
	 * <p>
	 * The size of the source image must not exceed MAX_SIZE.<br />
	 * The given target size must not exceed MAX_SIZE.
	 * </p>
	 * 
	 * @param mixed $objSource
	 * 			An image object, a path-<tt>string</tt> (relative to
	 * 			<tt>TL_ROOT</tt> or absolute) or <tt>File</tt> object denoting
	 * 			an image file in filesystem.
	 * @param number $numWidth
	 * 			Optional. Defaults to null.
	 * 			The width with <tt>$numWidth >= 1</tt>, that the thumb should
	 * 			fit.
	 * @param number $numHeight
	 * 			Optional. Defaults to null.
	 * 			The height with <tt>$numHeight >= 1</tt>, that the thumb should
	 * 			fit.
	 * @param string $strMode
	 * 			Optional. Defaults to <tt>''</tt> (the empty string).
	 * 			One of <tt>'box'</tt>, <tt>'none'</tt> or <tt>''</tt>.
	 * @return Image
	 * 			The <tt>Image</tt> object of the thumb-image.
	 * @throws Exception
	 * 			If the gd-library is not loaded.
	 * 			If arg-check fails.
	 * 			If image-format is not supported.
	 * 			If size of the given image is larger than MAX_SIZE.
	 * 			If target size is larger than MAX_SIZE.
	 * 			If image-creation fails.
	 * 
	 */
	public static function createThumb($objOriginal, Size $objDstSize, $intMode = self::CROP) {
		if(!($objOriginal instanceof Image)) {
			$objOriginal = self::createFromFile($objOriginal);
			$blnDelete = true;
		}
	
		if(!$objDstSize->getWidth() && !$objDstSize->getHeight()) {
			throw new InvalidArgumentException(sprintf(
				'Image::createThumb(): Either #2 $numWidth or #3 $numHeight must be a positive number, given width [%s], height [%s].',
				$objDstSize->getWidth(),
				$objDstSize->getHeight()
			));
		}
		
		$objOrigSize = $objOriginal->getSize();
		
		if($objDstSize->getArea()) {
			switch($intMode) {
				case self::FILL:
					$objDstSize = $objDstSize->ratiofyUp($objOrigSize->getRatio());
					break;
					
				case self::FIT:
					$objDstSize = $objDstSize->ratiofyDown($objOrigSize->getRatio());
					break;
				
				case self::CROP:
				default:
					$objSrcSize = $objOrigSize->ratiofyDown($objDstSize->getRatio());
					$objSrcPoint = $objSrcSize->centerize($objOrigSize);
					break;
			}
		} else {
			$objDstSize = $objDstSize->ratiofyUp($objOrigSize->getRatio());
		}
		
		$objThumb = $objOriginal->resample(
			null,
			$objDstSize,
			null,
			$objSrcSize,
			$objSrcPoint
		);
			
		if($blnDelete)
			unset($objOriginal);
		
		return $objThumb;
	}
	
	/**
	 * <p>
	 * Creates a thumb-image fitting the given width and height, scaled with the
	 * given scaling mode and stored in a tmp-file with the given quality.
	 * </p>
	 * <p>
	 * This method mirrors <tt>Controller->getImage()</tt> without the hook
	 * mechanism.
	 * </p>
	 * 
	 * @param mixed $objSource
	 * 			A path-<tt>string</tt> (relative to <tt>TL_ROOT</tt> or
	 * 			absolute) or <tt>File</tt> object denoting an image file in
	 * 			filesystem.
	 * @param number $numWidth
	 * 			Optional. Defaults to <tt>null</tt>.
	 * 			The width the thumb-image should fit with
	 * 			0 <= <tt>$numWidth</tt> <= 1200.
	 * @param number $numHeight
	 * 			Optional. Defaults to <tt>null</tt>.
	 * 			The height the thumb-image should fit with
	 * 			0 <= <tt>$numHeight</tt> <= 1200.
	 * @param string $strMode
	 * 			Optional. Defaults to <tt>''</tt> (the empty string).
	 * 			One of <tt>'proportional'</tt>, <tt>'box'</tt> or <tt>''</tt>.
	 * @param number $numQuality
	 * 			Optional. Defaults to <tt>90</tt>.
	 * 			A number specifying the quality of the stored image, if
	 * 			JPG-format is used, or the compression level, if PNG-format is
	 * 			used. (Higher is better quality / less compression.) Any number
	 * 			less than 1 will cause the default value to be used. Any number
	 * 			greater than 100 is treated like the value 100.
	 * @return string
	 *			If thumb-creation was successful, the path-string relative to
	 *			<tt>TL_ROOT</tt> denoting the file this image is stored in.
	 *			Ready to use for HTML output. Otherwise <tt>null</tt>.
	 * @see Image::createThumb();
	 */
	public static function getThumb($objOriginal, $numWidth = null, $numHeight = null, $strMode = '', $numQuality = 90, $fltTolerance = 0.03) {
		$objOriginal = self::getFile($objOriginal);
		$objOrigSize = Size::createFromFile($objOriginal);
		$objDstSize = new Size($numWidth, $numHeight);
		$objDstSize->getArea() || $objDstSize = $objDstSize->ratiofyUp($objOrigSize->getRatio());
		
		if(floatval($fltTolerance) != 0
		&& $objOrigSize->scale(1 + $fltTolerance)->wraps($objDstSize)
		&& $objOrigSize->scale(1 - $fltTolerance)->fits($objDstSize))
			return $objSource->value;
		
		$strCached = 'system/html/' . $objOriginal->filename . '-' . substr(md5('-w' . $numWidth . '-h' . $numHeight . '-' . $objOriginal->value . '-' . $strMode . '-' . $objOriginal->mtime), 0, 8) . '.' . $objOriginal->extension;
		if(is_file(TL_ROOT . '/' . $strCached))
			return $strCached;
	
		switch($strMode) {
			case self::CROP:
			case self::FILL:		
			case self::FIT:			break;
			case 'proportional':	$strMode = self::FILL;	break;
			case 'box':				$strMode = self::FIT;	break;
			default:				$strMode = self::CROP;	break;
		}
		
		try {
			$objThumb = self::createThumb($objOriginal, $objDstSize, $strMode);
			$objThumb->store($strCached, $numQuality, true);
		} catch(Exception $e) {
			$strCached = $objOriginal->value;
		}
		
		unset($objThumb);
		
		return $strCached;
	}
	
	protected $resImage;
	
	private $objSize;
		
	protected function __construct($resImage) {
		if(!@imagesx($resImage))
			throw new InvalidArgumentException('Image::__construct(): #1 $resImage is not a valid gdlib image ressource.');
		
		$this->resImage = $resImage;
		$this->objSize = new Size(imagesx($resImage), imagesy($resImage)); 
	}
	
	public function __destruct() {
		@imagedestroy($this->resImage);
	}
	
	public function __toString() {
		return '[Object: Image (' . $this->width . 'x' . $this->height . ')]';
	}
	
	public function getSize() {
		return $this->objSize;
	}
	
	public function getRessource() {
		return is_ressource($this->resImage) ? $this->resImage : null;
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
	
	public function watermark($objWatermark, $intPosition = self::BOTTOMLEFT, $fltSize = 0.5) {
		if(!$objWatermark instanceof Image) {
			$objWatermark = self::createFromFile($objWatermark);
		}
		
		$arrDstSize = Image::ratiofy(
			Image::scale($this, $fltSize > 0 ? min(1, $fltSize) : 0.5),
			$objWatermark->ratio
		);
		if($objWatermark->width < $arrDstSize[0]) { $arrDstSize = $objWatermark->dim; };
		
		if($intPosition & self::CENTER) {
			$objWatermark->resample($this, $arrDstSize,
				$this->centerize($arrDstSize), null, null, true);
		}
		
		if($intPosition & self::TOPLEFT) {
			$objWatermark->resample($this, $arrDstSize,
				null, null, null, true);
		}
		
		if($intPosition & self::TOPRIGHT) {
			$objWatermark->resample($this, $arrDstSize,
				array($this->width - $arrDstSize[0], 0), null, null, true);
		}
		
		if($intPosition & self::BOTTOMLEFT) {
			$objWatermark->resample($this, $arrDstSize,
				array(0, $this->height - $arrDstSize[1]), null, null, true);
		}
		
		if($intPosition & self::BOTTOMRIGHT) {
			$objWatermark->resample($this, $arrDstSize,
				array($this->width - $arrDstSize[0], $this->height - $arrDstSize[1]), null, null, true);
		}
		
		return $this;
	}
	
	public function resample(
			Image $objTarget		= null,
			Size $objDstSize		= null,
			Point2D $objDstPoint	= null,
			Size $objSrcSize		= null,
			Point2D $objSrcPoint	= null,
			$blnAlphaBlending		= false) {
		
		$objSrcPoint || $objSrcPoint = new Point2D(0, 0);
		$objSrcSize || $objSrcSize = Size::createFromPoint($this->getSize()->toPoint()->subtract($objSrcPoint));
		
		$objSrcSize->checkNonNullArea();
		$this->getSize()->checkValidSubArea($objSrcSize, $objSrcPoint);
		
		$objDstPoint || $objDstPoint = new Point2D(0, 0);
		$objDstSize || $objDstSize = Size::createFromPoint($objSrcSize->toPoint()->add($objDstPoint));
		
		$objDstSize->checkNonNullArea();
		
		$objTarget && $objTarget->getRessource() || $objTarget = call_user_func(array(__CLASS__, 'createEmpty'), $objDstSize);
		
		$objTarget->getSize()->checkValidSubArea($objDstSize, $objDstPoint);
		
		if($this->isTrueColorImage()) {
			imagealphablending($objTarget->resImage, $blnAlphaBlending);
			// filling the image with "transparent" color to ensure existance of alpha channel information
			$blnAlphaBlending || imagefill($objTarget->resImage, 0, 0, $objTarget->getColorIndex(new Color(0, 0, 0, 255)));
			imagesavealpha($objTarget->resImage, true);
		} else {
			$intTranspIndex = $objTarget->getColorIndex($this->getTransparentColor());
			imagefill($objTarget->resImage, 0, 0, $intTranspIndex);
			imagecolortransparent($objTarget->resImage, $intTranspIndex);
		}
		
		/*echo $arrDstPoint[0], 'x', $arrDstPoint[1], '/',
			 $arrSrcPoint[0], 'x', $arrSrcPoint[1], '/',
			 $arrDstSize[0], 'x', $arrDstSize[1], '/',
			 $arrSrcSize[0], 'x', $arrSrcSize[1], '/';*/
		
		imagecopyresampled($objTarget->resImage, $this->resImage,
			$objDstPoint->getX(), $objDstPoint->getY(),
			$objSrcPoint->getX(), $objSrcPoint->getY(),
			$objDstSize->getWidth(), $objDstSize->getHeight(),
			$objSrcSize->getWidth(), $objSrcSize->getHeight()
		);
		
		return $objTarget;
	}
	
	/**
	 * <p>
	 * Returns <tt>$varFile</tt> as a <tt>File</tt> object. If <tt>$varFile</tt>
	 * is a valid path-<tt>string</tt> (relative to <tt>TL_ROOT</tt> or
	 * absolute) and <tt>$blnCreate</tt> is <tt>true</tt>, any existing file
	 * with the same name will be deleted.
	 * </p>
	 * 
	 * @param mixed $varFile
	 * 			A path-<tt>string</tt> relative to <tt>TL_ROOT</tt> or
	 * 			<tt>File</tt> object.
	 * @param boolean $blnCreate
	 * 			Optional. Defaults to <tt>false</tt>.
	 * 			Delete and/or create the file denoted by the given path-string.
	 * @return mixed
	 * 			Returns the <tt>File</tt> object or <tt>false</tt>, if it could
	 * 			not be created for any reason.
	 */
	protected static function getFile($varFile, $blnCreate = false) {
		if($varFile instanceof File)
			return $varFile;
			
		if(!is_string($varFile)) {
			throw new InvalidArgumentException(sprintf(
				'Image::getFile(): #1 $varFile must be a string or a File object, given [%s]',
				$varFile
			));
		}
		
		$varFile = urldecode($varFile);
		if(strpos($varFile, TL_ROOT) === 0) {
			$varFile = substr($varFile, strlen(TL_ROOT));
		}
		
		$blnIsFile = is_file(TL_ROOT . '/' . $varFile);
		
		if(!$blnIsFile && !$blnCreate) {
			throw new Exception(sprintf(
				'Image::getFile(): File [%s] not found. Creation not allowed.',
				$varFile
			));
		} elseif($blnIsFile && $blnCreate) {
			$objFile = new File($varFile);
			$objFile->delete();
			unset($objFile);
		}
		
		return new File($varFile);
	}
	
	public abstract function toPaletteImage();
	
	public abstract function toTrueColorImage();
	
	public abstract function isPaletteImage();
	
	public abstract function isTrueColorImage();
	
	public abstract function getColorIndex(Color $objColor, $blnAllocate = true, $blnExact = true);
	
}
