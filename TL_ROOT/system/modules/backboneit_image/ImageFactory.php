<?php

abstract class ImageFactory {
	
	/**
	 * <p>
	 * Creates a new empty <tt>PaletteImage</tt> object with given width and
	 * height.
	 * </p>
	 * <p>
	 * The given size must not exceed the maximum of
	 * <tt>$GLOBALS['TL_CONFIG']['backboneit_image_maxsize']</tt>
	 * and 4.000.000 pixels.
	 * </p>
	 * 
	 * @param Size $objSize
	 * 			The size of the image-canvas.
	 * 
	 * @return PaletteImage
	 * 			The new <tt>PaletteImage</tt> object.
	 * 
	 * @throws RuntimeException
	 * 			If the gd-library is not loaded.
	 *			If given size is larger than currently allowed max size.
	 * 			If image-creation fails.
	 * @throws InvalidArgumentException
	 * 			If either the width or height is 0.
	 */
	public static function createPaletteImage(Size $objSize) {
		GdLib::checkLoaded();
		GdLib::checkSizeAllowed($objSize);
		$objSize->checkNonNullArea();
		
		$resImage = @imagecreate($objSize->getWidth(), $objSize->getHeight());
		
		if(!$resImage) {
			throw new RuntimeException(sprintf(
				'Image::createPaletteImage(): Failed to create palette image. Original message [%s].',
				$php_errormsg
			));
		}
		
		return new PaletteImage($resImage);
	}
	
	/**
	 * <p>
	 * Creates a new empty <tt>TrueColorImage</tt> object with given width and
	 * height.
	 * </p>
	 * <p>
	 * The given size must not exceed the maximum of
	 * <tt>$GLOBALS['TL_CONFIG']['backboneit_image_maxsize']</tt>
	 * and 4.000.000 pixels.
	 * </p>
	 * 
	 * @param Size $objSize
	 * 			The size of the image-canvas.
	 * 
	 * @return PaletteImage
	 * 			The new <tt>TrueColorImage</tt> object.
	 * 
	 * @throws RuntimeException
	 * 			If the gd-library is not loaded.
	 *			If given size is larger than currently allowed max size.
	 * 			If image-creation fails.
	 * @throws InvalidArgumentException
	 * 			If either the width or height is 0.
	 */
	public static function createTrueColorImage(Size $objSize) {
		GdLib::checkLoaded();
		GdLib::checkSizeAllowed($objSize);
		$objSize->checkNonNullArea();
		
		$resImage = @imagecreatetruecolor($objSize->getWidth(), $objSize->getHeight());
		
		if(!$resImage) {
			throw new RuntimeException(sprintf(
				'Image::createTrueColorImage(): Failed to create true color image. Original message [%s].',
				$php_errormsg
			));
		}
		
		return new TrueColorImage($resImage);
	}
	
	public static function createFromResource($resImage) {
		if(!is_resource($resImage) || !imagesx($resImage))
			throw new InvalidArgumentException('Image::createFromResource(): #1 $resImage is not a valid gdlib resource.');
		
		$strClass = imageistruecolor($resImage) ? 'TrueColorImage' : 'PaletteImage';
		
		return new $strClass($resImage);
	}
	
	/**
	 * <p>
	 * Creates a new <tt>TrueColorImage</tt> or <tt>PaletteImage</tt> object
	 * from the given file. The canvas of the <tt>Image</tt> object will have
	 * the width, height and contents of the image described by the file.
	 * </p>
	 * <p>
	 * The given size must not exceed the maximum of
	 * <tt>$GLOBALS['TL_CONFIG']['backboneit_image_maxsize']</tt>
	 * and 4.000.000 pixels.
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
		GdLib::checkLoaded();
		
		$objFile = self::getFile($varFile);
		$arrImageInfo = getimagesize(TL_ROOT . '/' . $objFile->value);
		
//		GdLib::checkTypeSupported($strType); // TODO supported types
		
		$objSize = new Size($arrImageInfo[0], $arrImageInfo[1]); //Size::createFromFile($objFile);
		GdLib::checkSizeAllowed($objSize);
		
		try {
			return ImageFormat::autoload($objFile);	
		} catch(Exception $e) {
			throw new RuntimeException(sprintf(
				'Image::createFromFile(): Failed to process supplied imagefile. File is maybe damaged or no valid imagefile. Original message [%s].',
				$php_errormsg
			));
		}
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
	public static function getThumb($objOriginal, $numWidth = null, $numHeight = null, $strMode = '', $fltTolerance = 0.03) {
		$objOriginal = self::getFile($objOriginal);
		
		$objOrigSize = Size::createFromFile($objOriginal);
		
		$objDstSize = new Size($numWidth, $numHeight);
		$objDstSize->getArea() || $objDstSize = $objDstSize->ratiofyUp($objOrigSize->getRatio());
		
		if(!$objDstSize->getArea() || $objDstSize->equals($objOrigSize, $fltTolerance))
			return $objSource->value;
		
		$strCached = 'system/html/' . $objOriginal->filename . '-' . substr(md5('-w' . $objDstSize->getWidth() . '-h' . $objDstSize->getHeight() . '-' . $objOriginal->value . '-' . $strMode . '-' . $objOriginal->mtime), 0, 8) . '.' . $objOriginal->extension;
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
			$objOp = new ThumbnailOperation($objDstSize, $strMode);
			$objOp->execute(self::createFromFile($objOriginal));
			ImageFormat::autostore($objOp->getResult(), self::getFile($strCached, true));
		} catch(Exception $e) {
			$strCached = $objOriginal->value;
		}
		
		unset($objOp);
		
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
	
}
