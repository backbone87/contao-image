<?php

class ResampleOperation extends TrueColorImageOperation {
	
	protected $objDstImage;
	
	public function setDstImage(Image $objDstImage = null) {
		$this->objDstImage = $objDstImage;
	}
	
	protected $objDstSize;
	
	protected $objDstPoint;
	
	public function setDstArea(Size $objDstSize = null, Point2D $objDstPoint = null) {
		$this->objDstSize = $objDstSize;
		$this->objDstPoint = $objDstPoint;
	}
	
	protected $objSrcSize;
	
	protected $objSrcPoint;
	
	public function setSrcArea(Size $objSrcSize = null, Point2D $objSrcPoint = null) {
		$this->objSrcSize = $objSrcSize;
		$this->objSrcPoint = $objSrcPoint;
	}
	
	protected function perform($objOriginal) {
		$objSrcPoint = $this->objSrcPoint ? $this->objSrcPoint : new Point2D(0, 0);
		$objSrcSize = $this->objSrcSize ? $this->objSrcSize : Size::createFromPoint($objOriginal->getSize()->toPoint()->subtract($objSrcPoint));
		
		$objSrcSize->checkNonNullArea();
		$objOriginal->getSize()->checkValidSubArea($objSrcSize, $objSrcPoint);
		
		$objDstPoint = $this->objDstPoint ? $this->objDstPoint : new Point2D(0, 0);
		$objDstSize = $this->objDstSize ? $this->objDstSize : Size::createFromPoint($objSrcSize->toPoint()->add($objDstPoint));
		
		$objDstSize->checkNonNullArea();
		
		$objTarget && $objTarget->getRessource() || $objTarget = call_user_func(array(__CLASS__, 'createEmpty'), $objDstSize);
		
		$objTarget->getSize()->checkValidSubArea($objDstSize, $objDstPoint);
		
		if($this->isTrueColorImage()) {
			imagealphablending($objTarget->resImage, $blnAlphaBlending);
			// filling the image with "transparent" color to ensure existance of alpha channel information
			$blnAlphaBlending || imagefill($objTarget->resImage, 0, 0, $objTarget->getColorIndex(new Color(0, 0, 0, 255)));
			imagesavealpha($objTarget->resImage, true);
		} else {
			$intTranspIndex = $objTarget->getColorIndex($this->getTransparentColor());
			imagefill($objTarget->resImage, 0, 0, $intTranspIndex);
			imagecolortransparent($objTarget->resImage, $intTranspIndex);
		}
		
		/*echo $arrDstPoint[0], 'x', $arrDstPoint[1], '/',
			 $arrSrcPoint[0], 'x', $arrSrcPoint[1], '/',
			 $arrDstSize[0], 'x', $arrDstSize[1], '/',
			 $arrSrcSize[0], 'x', $arrSrcSize[1], '/';*/
		
		imagecopyresampled($objTarget->resImage, $this->resImage,
			$objDstPoint->getX(), $objDstPoint->getY(),
			$objSrcPoint->getX(), $objSrcPoint->getY(),
			$objDstSize->getWidth(), $objDstSize->getHeight(),
			$objSrcSize->getWidth(), $objSrcSize->getHeight()
		);
		
		return $objTarget;
	}
	
}
