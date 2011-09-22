<?php

//namespace backboneit\image;

class Color {

	public static function createFromHexRGBA($strColor) {
		if(!preg_match('/(?:^|#)([a-f0-9]{8}|[a-f0-9]{6}|[a-f0-9]{4}|[a-f0-9]{3})/i', $strColor, $arrMatches))
			throw new InvalidArgumentException(sprintf(
				'Color::createFromHexRGBA(): #1 $strColor is not a valid hex color string, given [%s]',
				$strColor
			));
		$arrColor = str_split(strlen($arrMatches[1]) < 5 ? preg_replace('/(.)/', '$1$1', $strColor) : $arrMatches[1], 2);
	
		return new self(hexdec($arrColor[0]), hexdec($arrColor[1]), hexdec($arrColor[2]), $arrColor[3] ? hexdec($arrColor[3]) : 0);
	}
	
	public static function createFromAssoc($arrColor) {
		return new self($arrColor['red'], $arrColor['green'], $arrColor['blue'], $arrColor['alpha']);
	}
	
	private $intRed;
	
	private $intGreen;
	
	private $intBlue;
	
	private $intAlpha;
	
	public function __construct($intRed, $intGreen, $intBlue, $intAlpha = 0) {
		$this->intRed = max(min(round($intRed), 255), 0);
		$this->intGreen = max(min(round($intGreen), 255), 0);
		$this->intBlue = max(min(round($intBlue), 255), 0);
		$this->intAlpha = max(min(round(floatval($intAlpha)), 255), 0);
	}
	
	public function getRed() {
		return $this->intRed;
	}
	
	public function getGreen() {
		return $this->intGreen;
	}
	
	public function getBlue() {
		return $this->intBlue;
	}
	
	public function getAlpha() {
		return $this->intAlpha;
	}
	
	public function toHexString($blnAlpha = null) {
		$blnAlpha === null && $blnAlpha = $this->intAlpha != 0;
		return dechex($this->intRed) . dechex($this->intGreen) . dechex($this->intBlue) . ($blnAlpha ? dechex($this->intAlpha) : '');
	}
	
	public function __toString() {
		return sprintf('[Object: Color (R %s, G %s, B %s, A %s)]',
			$this->intRed,
			$this->intGreen,
			$this->intBlue,
			$this->intAlpha
		);
	}
	
}
