<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2013 Leo Feyer
 *
 * @package Backboneit_image
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'bbit',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Classes
	'bbit\image\Canvas'             => 'system/modules/backboneit_image/classes/bbit/image/Canvas.php',
	'bbit\image\CanvasFactory'      => 'system/modules/backboneit_image/classes/bbit/image/CanvasFactory.php',
	'bbit\image\format\GIFFormat'   => 'system/modules/backboneit_image/classes/bbit/image/format/GIFFormat.php',
	'bbit\image\format\ImageFormat' => 'system/modules/backboneit_image/classes/bbit/image/format/ImageFormat.php',
	'bbit\image\format\JPEGFormat'  => 'system/modules/backboneit_image/classes/bbit/image/format/JPEGFormat.php',
	'bbit\image\format\PNG24Format' => 'system/modules/backboneit_image/classes/bbit/image/format/PNG24Format.php',
	'bbit\image\format\PNG8Format'  => 'system/modules/backboneit_image/classes/bbit/image/format/PNG8Format.php',
	'bbit\image\format\PNGFormat'   => 'system/modules/backboneit_image/classes/bbit/image/format/PNGFormat.php',
	'bbit\image\format\WBMPFormat'  => 'system/modules/backboneit_image/classes/bbit/image/format/WBMPFormat.php',
	'bbit\image\op\CanvasOp'        => 'system/modules/backboneit_image/classes/bbit/image/op/CanvasOp.php',
	'bbit\image\op\ResampleOp'      => 'system/modules/backboneit_image/classes/bbit/image/op/ResampleOp.php',
	'bbit\image\op\ThumbnailOp'     => 'system/modules/backboneit_image/classes/bbit/image/op/ThumbnailOp.php',
	'bbit\image\op\WatermarkOp'     => 'system/modules/backboneit_image/classes/bbit/image/op/WatermarkOp.php',
	'bbit\image\PaletteCanvas'      => 'system/modules/backboneit_image/classes/bbit/image/PaletteCanvas.php',
	'bbit\image\TrueColorCanvas'    => 'system/modules/backboneit_image/classes/bbit/image/TrueColorCanvas.php',
	'bbit\image\util\Color'         => 'system/modules/backboneit_image/classes/bbit/image/util/Color.php',
	'bbit\image\util\GdLib'         => 'system/modules/backboneit_image/classes/bbit/image/util/GdLib.php',
	'bbit\image\util\Point2D'       => 'system/modules/backboneit_image/classes/bbit/image/util/Point2D.php',
	'bbit\image\util\Size'          => 'system/modules/backboneit_image/classes/bbit/image/util/Size.php',
));
