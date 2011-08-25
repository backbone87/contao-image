<?php

abstract class ImageFactory {
	
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
	 * @see Image::createEmpty()
	 */
	public static function createEmpty(Size $objSize) {
		GdLib::checkLoaded();
		GdLib::checkSizeAllowed($objSize);
		$objSize->checkNonNullArea();
		
		$resImage = @imagecreate($objSize->getWidth(), $objSize->getHeight());
		
		if(!$resImage) {
			throw new RuntimeException(sprintf(
				'Image::createEmpty(): Failed to create empty image. Original message [%s].',
				$php_errormsg
			));
		}
		
		return new self($resImage);
	}
	/**
	 * @see Image::createEmpty()
	 */
	public static function createEmpty(Size $objSize) {
		GdLib::checkLoaded();
		GdLib::checkSizeAllowed($objSize);
		$objSize->checkNonNullArea();
		
		$resImage = @imagecreatetruecolor($objSize->getWidth(), $objSize->getHeight());
		
		if(!$resImage) {
			throw new RuntimeException(sprintf(
				'Image::createEmpty(): Failed to create empty image. Original message [%s].',
				$php_errormsg
			));
		}
		
		return new self($resImage);
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
	public static function createFromFile($varFile, $strType = null) {
		GdLib::checkLoaded();
		
		$objFile = self::getFile($varFile);
		$arrImageInfo = getimagesize(TL_ROOT . '/' . $objFile->value);
		
		GdLib::checkTypeSupported($strType);
		
		$objSize = new Size($arrImageInfo[0], $arrImageInfo[1]); //Size::createFromFile($objFile);
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
	
}
