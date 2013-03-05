<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

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
class ImageCanvas {

	
	/**
	 * @var string
	 * 			Image storage format types.
	 */
	const PNG	= 'png';
	const JPEG	= 'jpg';
	const GIF	= 'gif';
	const WBMP	= 'wbmp';
	
	const CROP	= 0;
	const FILL	= 1;
	const FIT	= 2;
	
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
	public static function createEmpty($numWidth, $numHeight) {
		self::checkGdLib();
		
		$intWidth = intval($numWidth);
		if($intWidth < 1) {
			throw new InvalidArgumentException(sprintf(
				'Image::createEmpty(): #1 $numWidth must be greater or equal than [1], given [%s] (intval: [%s]).',
				$numWidth,
				$intWidth
			));
		}
		
		$intHeight = intval($numHeight);
		if($intHeight < 1) {
			throw new InvalidArgumentException(sprintf(
				'Image::createEmpty(): #2 $numHeight must be greater or equal than [1], given [%s] (intval: [%s]).',
				$numHeight,
				$intHeight
			));
		}
		
		self::checkSize($intWidth, $intHeight);
		
		if(!$resImage = @imagecreatetruecolor($intWidth, $intHeight)) {
			throw new RuntimeException(sprintf(
				'Image::createEmpty(): Failed to create empty image. Original message [%s].',
				$php_errormsg
			));
		}
		
		return new Image($resImage);
	}
    
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
	public static function createFromFile($varFile) {
		self::checkGdLib();
		
		$objFile = self::getFile($varFile);
		self::checkType($objFile->extension);
		
		if(!$objFile->width) {
			throw new Exception(sprintf(
				'Image::createFromFile(): Data in file [%s] is no valid image or maybe damaged.',
				$objFile->value
			));
		}
		
		self::checkSize($objFile->width, $objFile->height);
		
		$funCreateFrom = self::$funCreateFrom[$objFile->extension];
		if(!$resImage = @$funCreateFrom(TL_ROOT . '/' . $objFile->value)) {
			throw new Exception(sprintf(
				'Image::createFromFile(): Failed to process supplied imagefile. File is maybe damaged or no valid imagefile. Original message [%s].',
				$php_errormsg
			));
		}
		
		return new Image($resImage);
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
	public static function createThumb($objOriginal, $numWidth = 0, $numHeight = 0, $intMode = self::CROP) {
		if(!$objOriginal instanceof Image) {
			$objOriginal = self::createFromFile($objOriginal);
			$blnDelete = true;
		}
	
		$arrDstSize = array(max(0, intval($numWidth)), max(0, intval($numHeight)));
		if($arrDstSize[0] === 0 && $arrDstSize[1] === 0) {
			throw new InvalidArgumentException(sprintf(
				'Image::createThumb(): Either #2 $numWidth or #3 $numHeight must be a positive number, given width [%s], height [%s].',
				$numWidth,
				$numHeight
			));
		}
		
		if($arrDstSize[0] === 0) {
			$arrDstSize[0] = $objOriginal->ratiofyWidth($arrDstSize[1]);
		} elseif($arrDstSize[1] === 0) {
			$arrDstSize[1] = $objOriginal->ratiofyHeight($arrDstSize[0]);
		} else {
			$dblRatio = $arrDstSize[0] / $arrDstSize[1];
			switch($intMode) {
				case self::FILL:
					if($dblRatio < $objOriginal->ratio) {
						$arrDstSize[0] = $objOriginal->ratiofyWidth($arrDstSize[1]);
					} else {
						$arrDstSize[1] = $objOriginal->ratiofyHeight($arrDstSize[0]);
					}
					break;
					
				case self::FIT:
					if($dblRatio < $objOriginal->ratio) {
						$arrDstSize[1] = $objOriginal->ratiofyHeight($arrDstSize[0]);
					} else {
						$arrDstSize[0] = $objOriginal->ratiofyWidth($arrDstSize[1]);
					}
					break;
				
				case self::CROP:
				default:
					$arrSrcDim = Image::ratiofy($objOriginal, $dblRatio);
					$arrSrcPoint = $objOriginal->centerize($arrSrcDim);
					break;
			}
			
		}
		
		$objThumb = $objOriginal->resample(
			null,
			array($intWidth, $intHeight),
			null,
			$arrSrcSize,
			$arrSrcPoint);
			
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
	public static function getThumb($objOriginal, $numWidth = null, $numHeight = null, $strMode = '', $numQuality = 90) {
		$objOriginal = self::getFile($objOriginal);
		
		if((!$numWidth || ($numWidth < $objOriginal->width * 1.03 && $numWidth > $objOriginal->width * 0.97))
		&& (!$numHeight || ($numHeight < $objOriginal->height * 1.03 && $numHeight > $objOriginal->height * 0.97)))
			return $objSource->value;
		
		$strCached = 'system/html/' . $objOriginal->filename . '-' . substr(md5('-w' . $numWidth . '-h' . $numHeight . '-' . $objOriginal->value . '-' . $strMode . '-' . $objOriginal->mtime), 0, 8) . '.' . $objOriginal->extension;
		if(is_file(TL_ROOT . '/' . $strCached))
			return $strCached;
	
		switch($strMode) {
			case 'proportional':	$strMode = self::FILL;	break;
			case 'box':				$strMode = self::FIT;	break;
			default:				$strMode = self::CROP;	break;
		}
		
		try {
			$objThumb = self::createThumb($objOriginal, $numWidth, $numHeight, $strMode);
			$objThumb->store($strCached, $numQuality, true);
		} catch(Exception $e) {
			$strCached = $objOriginal->value;
		}
		
		unset($objThumb);
		
		return $strCached;
	}
	
	protected $resImage;
		
	protected function __construct($resImage) {
		$this->resImage = $resImage;
	}
	
	public function __destruct() {
		@imagedestroy($this->resImage);
	}
	
	/**
	 * <p>
	 * If $strKey is 'res' or 'resource', returns the resource-reference of the
	 * image encapsulated by this <tt>Image</tt> object.
	 * If $strKey is 'width', returns the width of this image.
	 * If $strKey is 'height', returns the height of this image.
	 * </p>
	 * 
	 * @param string $strKey
	 * 			The get-key.
	 * @return mixed
	 * 			The particular value.
	 */
	public function __get($strKey) {
		if(!$this->resImage)
			return null;
		
		switch($strKey) {
			case 'res':
			case 'resource':
				return $this->resImage;
				break;
				
			case 'width':
				return imagesx($this->resImage);
				break;
				
			case 'height':
				return imagesy($this->resImage);
				break;
				
			case 'dim':
			case 'size':
				return array(imagesx($this->resImage), imagesy($this->resImage));
				break;
				
			case 'ratio':
				return imagesx($this->resImage) / imagesy($this->resImage);
				break;
		}
		
	}
	
	public function __toString() {
		return '[Object: Image (' . $this->width . 'x' . $this->height . ')]';
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
	public function store($varFile = null, $numQuality = 90, $blnForce = true, $strType = null) {
		if($varFile) {
			$objFile = self::getFile($varFile, $blnForce);
			if(!$strType) { $strType = $objFile->extension; }
		}
		
		self::checkType($strType); 
		
		$numQuality = $numQuality > 1 ? $numQuality > 100 ? 100 : $numQuality : 90;
		if($strType == self::PNG) { $numQuality = 10 - ceil($numQuality / 10); }
		
		$funStore = self::$funStore[$strType];
		
		ob_start();
		if(!@$funStore($this->resImage, null, $strType == self::WBMP ? null : intval($numQuality))) {
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
	
	public function centerize(array $arrDim) {
		if(!$this->isValidArea($arrDim))
			return;
		
		return array(round($arrDim[0] - $this->width / 2), round($arrDim[1] - $this->height / 2));
	}
	
	public function ratiofyWidth($intHeight) {
		return round($intHeight * $this->ratio);
	}
	
	public function ratiofyHeight($intWidth) {
		return round($intWidth / $this->ratio);
	}
	
	public function isValidArea(array &$arrDim, array &$arrPoint = array(0, 0)) {
		$arrDim[0] = intval($arrDim[0]);
		$arrDim[1] = intval($arrDim[1]);
		$arrPoint[0] = intval($arrPoint[0]);
		$arrPoint[1] = intval($arrPoint[1]);
			
		if($arrDim[0] < 1
		|| $arrDim[1] < 1
		|| $arrPoint[0] < 0
		|| $arrPoint[1] < 0
		|| $arrDim[0] + $arrPoint[0] > $this->width
		|| $arrDim[1] + $arrPoint[1] > $this->height) {
			return false;
		}
	
		return true;
	}
	
	public function isValidPoint(array &$arrPoint) {
		$arrPoint[0] = intval($arrPoint[0]);
		$arrPoint[1] = intval($arrPoint[1]);
		
		if($arrPoint[0] < 0 || $arrPoint[1] < 0
		|| $arrPoint[0] >= $this->width || $arrPoint[1] >= $this->height) {
			return false;
		}
		
		return true;
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
			Image $objTarget	= null,
			array $arrDstSize	= null,
			array $arrDstPoint	= null,
			array $arrSrcSize	= null,
			array $arrSrcPoint	= null,
			$blnAlphaBlending	= false) {
		
		if(!$arrSrcPoint) { $arrSrcPoint = array(0, 0); }
		if(!$arrSrcSize) { $arrSrcSize = array($this->width - $arrSrcPoint[0], $this->height - $arrSrcPoint[1]); }
		
		if(!$this->isValidArea($arrSrcSize, $arrSrcPoint)) {
			throw new Exception(sprintf(
				'Image->resample(): #4 $arrSrcSize and #5 $arrSrcPoint must describe a valid area of this image, given size [%s][%s], point [%s][%s]',
				$arrSrcSize[0],
				$arrSrcSize[1],
				$arrSrcPoint[0],
				$arrSrcPoint[1]
			));
		}
		
		if(!$arrDstPoint) { $arrDstPoint = array(0, 0); }
		if(!$arrDstSize) { $arrDstSize = array($arrSrcSize[0] + $arrDstPoint[0], $arrSrcSize[1] + $arrDstPoint[1]); }
		
		if(!$objTarget || !$objTarget->res) { $objTarget = self::createEmpty($arrDstSize[0], $arrDstSize[1]); }
		
		if(!$objTarget->isValidArea($arrDstSize, $arrDstPoint)) {
			throw new Exception(sprintf(
				'Image->resample(): #2 $arrDstSize and #3 $arrDstPoint must describe a valid area of the target image, given size [%s][%s], point [%s][%s]',
				$arrSrcSize[0],
				$arrSrcSize[1],
				$arrSrcPoint[0],
				$arrSrcPoint[1]
			));
		}
		
		if(imageistruecolor($this->resImage)) {
			imagealphablending($objTarget->res, $blnAlphaBlending);
			if(!$blnAlphaBlending) {
				$intTranspIndex = imagecolorallocatealpha($objTarget->res, 0, 0, 0, 127);
				imagefill($objTarget->res, 0, 0, $intTranspIndex);
			}
			imagesavealpha($objTarget->res, true);
		} else {
			$intTranspIndex = imagecolortransparent($this->resImage);
			if ($intTranspIndex >= 0 && $intTranspIndex < imagecolorstotal($this->resImage)) {
				$arrColor = imagecolorsforindex($this->resImage, $intTranspIndex);
				$intTranspIndex = imagecolorallocate($objTarget->res, $arrColor['red'], $arrColor['green'], $arrColor['blue']);
				imagefill($objTarget->res, 0, 0, $intTranspIndex);
				imagecolortransparent($objTarget->res, $intTranspIndex);
			}
		}
		
		/*echo $arrDstPoint[0], 'x', $arrDstPoint[1], '/',
			 $arrSrcPoint[0], 'x', $arrSrcPoint[1], '/',
			 $arrDstSize[0], 'x', $arrDstSize[1], '/',
			 $arrSrcSize[0], 'x', $arrSrcSize[1], '/';*/
		
		imagecopyresampled($objTarget->res, $this->resImage,
			$arrDstPoint[0], $arrDstPoint[1],
			$arrSrcPoint[0], $arrSrcPoint[1],
			$arrDstSize[0], $arrDstSize[1],
			$arrSrcSize[0], $arrSrcSize[1]);
		
		return $objTarget;
	}
	
	public static function scale($varDim, $fltScale) {
		if($varDim instanceof Image) {
			$varDim = $varDim->dim;
		} elseif(!is_array($varDim)) {
			throw new InvalidArgumentException(sprintf(
				'Image::scale(): #1 $varDim must be an Image object or a 2-element array of numbers, given [%s].',
				$varDim
			));
		}
		
		$fltScale = floatval($fltScale);
		
		$varDim[0] *= $fltScale;
		$varDim[1] *= $fltScale;
		
		return $varDim;
	}
	
	public static function ratiofy($varDim, $fltRatio) {
		$fltRatio = floatval($fltRatio);
		if($fltRatio <= 0) {
			throw new InvalidArgumentException(sprintf(
				'Image::ratiofy(): #2 $fltRatio must be a positive number, given [%s].',
				$fltRatio
			));
		}
		
		if($varDim instanceof Image) {
			$varDim = $varDim->dim;
		} elseif(!is_array($varDim)) {
			throw new InvalidArgumentException(sprintf(
				'Image::ratiofy(): #1 $varDim must be an Image object or a 2-element array of numbers, given [%s].',
				$varDim
			));
		}
		
		if($varDim[0] <= 0)
			return array(round($varDim[1] * $fltRatio), $varDim[1]);
		
		if($varDim[1] <= 0)
			return array($varDim[0], round($varDim[0] / $fltRatio));
		
		return $fltRatio < ($varDim[0] / $varDim[1])
			? array(round($varDim[1] * $fltRatio), $varDim[1])
			: array($varDim[0], round($varDim[0] / $fltRatio));
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
	public static function getFile($varFile, $blnCreate = false) {
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
	
	public static function checkSize($intWidth, $intHeight) {
		if($intWidth * $intHeight > max(4000000, $GLOBALS['TL_CONFIG']['backboneit_image_maxsize'])) {
			throw new RuntimeException(sprintf(
				'Image::checkSize(): Requested size [%s] of image-canvas exceeds max allowed size [%s]. Width given [%s], height given [%s].',
				$intWidth * $intHeight,
				max(4000000, $GLOBALS['TL_CONFIG']['backboneit_image_maxsize']),
				$intWidth,
				$intHeight
			));
		}
	}
	
	public static function checkType($strType) {
		$arrSupported = self::getSupportedTypes();
		if(!$arrSupported[$strType]) {
			throw new RuntimeException(sprintf(
				'Image::checkType(): Type [%s] not supported.',
				$strType
			));
		}
	}
	
	public static function checkGdLib() {
		if(!extension_loaded('gd')) {
			throw new RuntimeException('Image::checkGdLib(): gdlib not loaded.');
		}
	}
	
	protected static $arrSupported; 
	
	public static function getSupportedTypes() {
		if(self::$arrSupported)
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
	
	/**
	 * @var array
	 * 			Maps file-extensions to image-creation functions.
	 */
	protected static $funCreateFrom = array(
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
