<?php

namespace bbit\image\util;

class Color {

	public static function create($r, $g, $b, $a = 0) {
		$color = new self();
		$color->set($r, $g, $b, $a);
		return $color;
	}

	public static function createFromCSSValue($color) {
		if(preg_match('/(?:^|#)([a-f0-9]{8}|[a-f0-9]{6}|[a-f0-9]{4}|[a-f0-9]{3})/i', $color, $matches)) {
			$color = str_split(strlen($matches[1]) < 5 ? preg_replace('/(.)/', '$1$1', $color) : $matches[1], 2);
			return self::create(
				hexdec($color[0]),
				hexdec($color[1]),
				hexdec($color[2]),
				$color[3] ? hexdec($color[3]) : 0
			);
		}
		throw new \InvalidArgumentException(sprintf('#1 $color is not a valid CSS color value, given [%s]', $color));
	}

	public static function createFromArray(array $color) {
		return self::create($color['red'], $color['green'], $color['blue'], $color['alpha']);
	}

	private $r;

	private $g;

	private $b;

	private $a;

	public function __construct() {
	}

	private function set($r, $g, $b, $a = 0) {
		$this->r = max(0, min(255, round($r)));
		$this->g = max(0, min(255, round($g)));
		$this->b = max(0, min(255, round($b)));
		$this->a = max(0, min(255, round(floatval($a))));
	}

	public function getRed() {
		return $this->r;
	}

	public function getGreen() {
		return $this->g;
	}

	public function getBlue() {
		return $this->b;
	}

	public function getAlpha() {
		return $this->a;
	}

	public function toHexString($alpha = null) {
		$alpha === null && $alpha = $this->a != 0;
		return dechex($this->r) . dechex($this->g) . dechex($this->b) . ($alpha ? dechex($this->a) : '');
	}

	public function __toString() {
		return sprintf('[Object: Color (R %s, G %s, B %s, A %s)]', $this->r, $this->g, $this->b, $this->a);
	}

}
