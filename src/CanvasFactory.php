<?php

namespace bbit\image;

use bbit\image\util\Size;
use bbit\image\util\GdLib;

abstract class CanvasFactory {

	/**
	 * <p>
	 * Creates a new empty <tt>PaletteCanvas</tt> object with given width and
	 * height.
	 * </p>
	 * <p>
	 * The given size must not exceed the maximum of
	 * <tt>$GLOBALS['TL_CONFIG']['backboneit_image_maxsize']</tt>
	 * and 4.000.000 pixels.
	 * </p>
	 *
	 * @param Size $size
	 * 			The size of the image-canvas.
	 *
	 * @return PaletteCanvas
	 * 			The new <tt>PaletteCanvas</tt> object.
	 *
	 * @throws RuntimeException
	 * 			If the gd-library is not loaded.
	 *			If given size is larger than currently allowed max size.
	 * 			If image-creation fails.
	 * @throws InvalidArgumentException
	 * 			If either the width or height is 0.
	 */
	public static function createPaletteCanvas(Size $size) {
		GdLib::requireLoaded();
		GdLib::requireSizeAllowed($size);
		$size->requireNonNullArea();

		$canvas = @imagecreate($size->getWidth(), $size->getHeight());

		if(!$canvas) {
			$msg = error_get_last();
			throw new \RuntimeException(sprintf('Failed to create palette image. Original message [%s].', $msg));
		}

		return new PaletteCanvas($canvas);
	}

	/**
	 * <p>
	 * Creates a new empty <tt>TrueColorCanvas</tt> object with given width and
	 * height.
	 * </p>
	 * <p>
	 * The given size must not exceed the maximum of
	 * <tt>$GLOBALS['TL_CONFIG']['backboneit_image_maxsize']</tt>
	 * and 4.000.000 pixels.
	 * </p>
	 *
	 * @param Size $size
	 * 			The size of the image-canvas.
	 *
	 * @return PaletteCanvas
	 * 			The new <tt>TrueColorCanvas</tt> object.
	 *
	 * @throws RuntimeException
	 * 			If the gd-library is not loaded.
	 *			If given size is larger than currently allowed max size.
	 * 			If image-creation fails.
	 * @throws InvalidArgumentException
	 * 			If either the width or height is 0.
	 */
	public static function createTrueColorCanvas(Size $size) {
		GdLib::requireLoaded();
		GdLib::requireSizeAllowed($size);
		$size->requireNonNullArea();

		$canvas = @imagecreatetruecolor($size->getWidth(), $size->getHeight());

		if(!$canvas) {
			$msg = error_get_last();
			throw new \RuntimeException(sprintf('Failed to create true color image. Original message [%s].', $msg));
		}

		return new TrueColorCanvas($canvas);
	}

	public static function createFromResource($canvas) {
		if(!is_resource($canvas) || !@imagesx($canvas)) {
			throw new \InvalidArgumentException('#1 $canvas is not a valid gdlib resource.');
		}

		$strClass = imageistruecolor($canvas) ? '\bbit\image\TrueColorCanvas' : '\bbit\image\PaletteCanvas';

		return new $strClass($canvas);
	}

}
