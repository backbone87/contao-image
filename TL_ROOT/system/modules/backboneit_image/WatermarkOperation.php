<?php

class WatermarkOperation extends ImageOperation {
	
	/**
	 * Positioning identifiers.
	 */
	const CENTER		= 1;
	const TOPLEFT		= 2;
	const TOPRIGHT		= 4;
	const BOTTOMRIGHT	= 8;
	const BOTTOMLEFT	= 16;

	public function watermark($objWatermark, $intPosition = self::BOTTOMLEFT, $fltSize = 0.5) {
		if(!$objWatermark instanceof Image) {
			$objWatermark = self::createFromFile($objWatermark);
		}
		
		$arrDstSize = Image::ratiofy(
			Image::scale($this, $fltSize > 0 ? min(1, $fltSize) : 0.5),
			$objWatermark->ratio
		);
		if($objWatermark->width < $arrDstSize[0]) { $arrDstSize = $objWatermark->dim; };
		
		if($intPosition & self::CENTER) {
			$objWatermark->resample($this, $arrDstSize,
				$this->centerize($arrDstSize), null, null, true);
		}
		
		if($intPosition & self::TOPLEFT) {
			$objWatermark->resample($this, $arrDstSize,
				null, null, null, true);
		}
		
		if($intPosition & self::TOPRIGHT) {
			$objWatermark->resample($this, $arrDstSize,
				array($this->width - $arrDstSize[0], 0), null, null, true);
		}
		
		if($intPosition & self::BOTTOMLEFT) {
			$objWatermark->resample($this, $arrDstSize,
				array(0, $this->height - $arrDstSize[1]), null, null, true);
		}
		
		if($intPosition & self::BOTTOMRIGHT) {
			$objWatermark->resample($this, $arrDstSize,
				array($this->width - $arrDstSize[0], $this->height - $arrDstSize[1]), null, null, true);
		}
		
		return $this;
	}
	
}
