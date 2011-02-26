<?php

class Raster {
	
	protected $intWidth;
	
	protected $intHeight;
	
	
	public function __construct($intWidth, $intHeight) {
		if($intWidth < 1 || $intHeight < 1)
			throw new InvalidArgumentException();
			
		$this->intWidth = floor($intWidth);
		$this->intHeight = floor($intHeight);
	}
	
	public function __get($strKey) {
		switch($strKey) {
			case 'width':
				return $this->intWidth;
				break;
				
			case 'height':
				return $this->intHeight;
				break;
				
			case 'dim':
			case 'size':
				return array($this->intWidth, $this->intHeight);
				break;
				
			case 'ratio':
				return $this->intWidth / $this->intHeight;
				break;
		}
	}
	
	public function isValidArea(array &$arrDim, array &$arrPoint = array(0, 0)) {
		$arrDim[0] = intval($arrDim[0]);
		$arrDim[1] = intval($arrDim[1]);
		$arrPoint[0] = intval($arrPoint[0]);
		$arrPoint[1] = intval($arrPoint[1]);
			
		if($arrDim[0] < 1
		|| $arrDim[1] < 1
		|| $arrPoint[0] < 0
		|| $arrPoint[1] < 0
		|| $arrDim[0] + $arrPoint[0] > $this->width
		|| $arrDim[1] + $arrPoint[1] > $this->height) {
			return false;
		}
	
		return true;
	}
	
	public function isValidPoint(array &$arrPoint) {
		$arrPoint[0] = intval($arrPoint[0]);
		$arrPoint[1] = intval($arrPoint[1]);
		
		if($arrPoint[0] < 0 || $arrPoint[1] < 0
		|| $arrPoint[0] >= $this->width || $arrPoint[1] >= $this->height) {
			return false;
		}
		
		return true;
	}
	
	public function scale($varDim, $fltScale) {
		if($varDim instanceof Image) {
			$varDim = $varDim->dim;
		} elseif(!is_array($varDim)) {
			throw new InvalidArgumentException(sprintf(
				'Image::scale(): #1 $varDim must be an Image object or a 2-element array of numbers, given [%s].',
				$varDim
			));
		}
		
		$fltScale = floatval($fltScale);
		
		$varDim[0] *= $fltScale;
		$varDim[1] *= $fltScale;
		
		return $varDim;
	}
	
	public function ratiofy($fltRatio) {
		if($fltRatio instanceof Raster) {
			$fltRatio = $fltRatio->ratio;
		} else {
			$fltRatio = floatval($fltRatio);
			if($fltRatio <= 0) {
				throw new InvalidArgumentException(sprintf(
					'Image::ratiofy(): #2 $fltRatio must be a positive number, given [%s].',
					$fltRatio
				));
			}
		}
			
		if($varDim[0] <= 0)
			return array(round($varDim[1] * $fltRatio), $varDim[1]);
		
		if($varDim[1] <= 0)
			return array($varDim[0], round($varDim[0] / $fltRatio));
		
		return $fltRatio < ($varDim[0] / $varDim[1])
			? array(round($varDim[1] * $fltRatio), $varDim[1])
			: array($varDim[0], round($varDim[0] / $fltRatio));
	}

	public function width($intHeight) {
		return round($intHeight * $this->ratio);
	}
	
	public function height($intWidth) {
		return round($intWidth / $this->ratio);
	}
	
}