<?php

namespace bbit\image\util;

final class GdLib {

	public static function isLoaded() {
		return extension_loaded('gd');
	}

	public static function requireLoaded() {
		if(!self::isLoaded()) {
			throw new \RuntimeException('gdlib not loaded.');
		}
	}


	public static function getSupportedTypes() {
		static $supported;

		if(!isset($supported)) {
			$types = imagetypes();
			$supported = array_keys(array_filter(array(
				'png'	=> $types & IMG_PNG,
				'jpg'	=> $types & IMG_JPG,
				'gif'	=> $types & IMG_GIF,
				'wbmp'	=> $types & IMG_WBMP
			)));
			$supported = array_combine($supported, $supported);
		}

		return $supported;
	}

	public static function isSupportedType($type) {
		$type == 'jpeg' && $type = 'jpg';
		$supported = self::getSupportedTypes();
		return isset($supported[$type]);
	}

	public static function requireSupportedType($type) {
		if(!self::isSupportedType($type)) {
			throw new \RuntimeException(sprintf('Type [%s] not supported.', $type));
		}
	}


	public static function getMaxAreaAllowed() {
		return max(4000000, $GLOBALS['TL_CONFIG']['bbit_image_maxarea']);
	}

	public static function isSizeAllowed(Size $size) {
		return $size->getArea() <= self::getMaxAreaAllowed();
	}

	public static function requireSizeAllowed(Size $size) {
		if(!self::isSizeAllowed($size)) {
			throw new \RuntimeException(sprintf(
				'Area [%s] exceeds max allowed value of [%s]. Width [%s], height [%s].',
				$size->getArea(),
				self::getMaxAreaAllowed(),
				$size->getWidth(),
				$size->getHeight()
			));
		}
	}


	private function __construct() {
	}

	private function __clone() {
	}

}
