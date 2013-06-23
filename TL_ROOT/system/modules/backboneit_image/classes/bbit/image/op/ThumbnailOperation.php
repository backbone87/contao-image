<?php

class ThumbnailOp extends CanvasOp {
	
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Thumbnail generation modes.
	 * 
	 * "CROP"
	 * Scales down the original until it matches at least one side of the
	 * requested size and finally crops the image centrally, if needed.
	 * 
	 * "FILL"
	 * Same as "CROP" but does not crops the image. Can result in a larger area
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
	 * <p>
	 * Returns an <tt>Canvas</tt> object containing a scaled version of the image
	 * denoted by <tt>$objSource</tt>.
	 * </p>
	 * <p>
	 * If no width or height is given this method behaves exactly like
	 * <tt>Canvas::createFromFile()</tt> with argument <tt>$objSource</tt>
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
	 * @return Canvas
	 * 			The <tt>Canvas</tt> object of the thumb-image.
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
		if(!($objOriginal instanceof Canvas)) {
			$objOriginal = self::createFromFile($objOriginal);
			$blnDelete = true;
		}
	
		if(!$objDstSize->getWidth() && !$objDstSize->getHeight()) {
			throw new InvalidArgumentException(sprintf(
				'Canvas::createThumb(): Either #2 $numWidth or #3 $numHeight must be a positive number, given width [%s], height [%s].',
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
	
}
