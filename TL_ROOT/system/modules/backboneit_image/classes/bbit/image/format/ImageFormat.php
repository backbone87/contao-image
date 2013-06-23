<?php

namespace bbit\image\format;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
abstract class ImageFormat {

	const DEFAULT_FORMAT_CLASS = 'PNGFormat';

	const MODE_OVERWRITE = 'w';
	const MODE_CREATE = 'x';
	const MODE_APPEND = 'a';

	private static $formatClassesByExtension = array(
		'jpg'	=> 'JPEGFormat',
		'jpeg'	=> 'JPEGFormat',
		'gif'	=> 'GIFFormat',
		'png'	=> 'PNGFormat',
		'wbmp'	=> 'WBMPFormat'
	);

	public static function autoload($stream, $length = -1, $offset = -1, $ctx = null) {
		if(is_resource($stream)) {
			$data = stream_get_contents($stream, $length, $offset);
		} else {
			$data = file_get_contents($stream, false, $ctx, $offset, $length);
		}
		return CanvasFactory::createFromResource(imagecreatefromstring($data));
	}

	public static function autostore(Canvas $canvas, $stream, $length = -1, $offset = -1, $ctx = null, $formatClass = null) {
		if($formatClass === null && !is_resource($stream)) {
			$pos = strrpos($stream, '.');
			$pos === false || $formatClass = self::$formatClassesByExtension[substr($stream, $pos + 1)];
		}
		if($formatClass === null || !is_subclass_of($formatClass, __CLASS__)) {
			$formatClass = self::DEFAULT_FORMAT_CLASS;
		}

		$format = new $formatClass();
		return $format->store($canvas, $stream, $length, $offset, $ctx);
	}

	public static function registerFormatClass($class, $extensions) {
		if(!is_subclass_of($class, __CLASS__)) {
			return false;
		}
		foreach((array) $extensions as $extension) {
			self::$formatClassesByExtension[$extension] = $class;
		}
		return true;
	}


	protected function __construct() {
	}

	public function load($stream, $length = -1, $offset = -1, $ctx = null) {
		return self::autoload($stream, $length, $offset, $ctx);
	}

	public function store(Canvas $canvas, $stream, $ctx = null, $mode = self::MODE_OVERWRITE) {
		$data = $this->getBinary($canvas);

		if(!is_resource($stream)) {
			$stream = fopen($stream, $mode . 'b', null, $ctx);
			$close = true;
		}

		try {
			fwrite($stream, $data);
			$close && fclose($stream);
		} catch(\Exception $e) {
			$close && $stream && fclose($stream);
			throw new \RuntimeException('Failed to write image data to file', 1, $e);
		}

		return strlen($data);
	}

	public abstract function getBinary(Canvas $canvas);

	protected function createBinary(callable $creator, array $args = null) {
		ob_start();
		if(!@call_user_func_array($creator, (array) $args)) {
			ob_end_clean();
			$msg = error_get_last();
			throw new \RuntimeException(sprintf('Failed to create image data. Original message [%s].', $msg));
		}
		return ob_get_clean();
	}

}
