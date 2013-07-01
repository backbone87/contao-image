<?php

namespace bbit\image\format;

use bbit\image\Canvas;
use bbit\image\CanvasFactory;

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

	public static function autoload($stream, $length = -1, $offset = 0, $ctx = null) {
		if(is_resource($stream)) {
			$data = stream_get_contents($stream, $length, $offset);
			if(!$data) {
				throw new \RuntimeException('Given stream does not contain any data', 1);
			}
		} else {
			if(!is_file($stream)) {
				throw new \RuntimeException(sprintf('Given file "%s" does not exists', $stream), 1);
			}
			if($length > -1) {
				$data = file_get_contents($stream, false, $ctx, $offset, $length);
			} else {
				$data = file_get_contents($stream, false, $ctx, $offset);
			}
			if(!$data) {
				throw new \RuntimeException(sprintf('Given file "%s" does not contain any data or could not be read', $stream), 1);
			}
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
			$stream = $ctx === null ? fopen($stream, $mode . 'b') : fopen($stream, $mode . 'b', null, $ctx);
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

	public function getDataURL(Canvas $canvas) {
		return 'data:' . $this->getMIMEType() . ';base64,' . $this->getBase64Binary($canvas);
	}

	public function getBase64Binary(Canvas $canvas) {
		return base64_encode($this->getBinary($canvas));
	}

	public function sendContentTypeHeader() {
		header('Content-Type: ' . $this->getMIMEType());
		return $this;
	}

	public function send(Canvas $canvas, $exit = true, $header = true) {
		$header && $this->sendContentTypeHeader();
		while(ob_end_clean());
		echo $this->getBinary($canvas);
		if($exit) {
			exit;
		}
		return $this;
	}

	public abstract function getBinary(Canvas $canvas);

	public abstract function getMIMEType();

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
