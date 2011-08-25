<?php

//namespace backboneit\image\operations;

//use backboneit\image\Point2D as Point2D;
//use backboneit\image\Size as Size;
//use backboneit\image\Image as Image;

class ResampleOperation extends TrueColorImageOperation {

	protected function __construct(Image $objOriginal = null, $blnOriginalImmutable = true) {
		parent::__construct($objOriginal, $blnOriginalImmutable);
	}
	
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
	
	protected $blnAlphaBlending = false;
	
	public function setAlphaBlending($blnAlphaBlending = false) {
		$this->blnAlphaBlending = !!$blnAlphaBlending;
	}
	
	public function isAlphaBlending() {
		return $this->blnAlphaBlending;
	}
	
	protected function perform($objOriginal) {
		$objSrcPoint = $this->objSrcPoint ? $this->objSrcPoint : new Point2D(0, 0);
		$objSrcSize = $this->objSrcSize ? $this->objSrcSize : Size::createFromPoint($objOriginal->getSize()->toPoint()->subtract($objSrcPoint));
		
		$objSrcSize->checkNonNullArea();
		$objOriginal->getSize()->checkValidSubArea($objSrcSize, $objSrcPoint);
		
		$objDstPoint = $this->objDstPoint ? $this->objDstPoint : new Point2D(0, 0);
		$objDstSize = $this->objDstSize ? $this->objDstSize : Size::createFromPoint($objSrcSize->toPoint()->add($objDstPoint));
		
		$objDstSize->checkNonNullArea();
		
		$objDstImage = $this->objDstImage ? $this->objDstImage : TrueColorImage::createEmpty($objDstSize);
		$resDstImage = $objDstImage->getRessource();
		
		$objDstImage->getSize()->checkValidSubArea($objDstSize, $objDstPoint);
		
		imagealphablending($resDstImage, true);
		$objBottomRight = $objDstImage->getSize()->toPoint();
		imagefilledrectangle($resDstImage,
			0, 0,
			$objBottomRight->getX(), $objBottomRight->getY(),
			$objDstImage->getColorIndex(new Color(0, 0, 0, 255))
		);
		imagealphablending($resDstImage, $blnAlphaBlending);
		imagesavealpha($resDstImage, true);
		
		imagecopyresampled($resDstImage, $objOriginal->getRessource(),
			$objDstPoint->getX(), $objDstPoint->getY(),
			$objSrcPoint->getX(), $objSrcPoint->getY(),
			$objDstSize->getWidth(), $objDstSize->getHeight(),
			$objSrcSize->getWidth(), $objSrcSize->getHeight()
		);
		
		return $objTarget;
	}
	
	public function modifiesOriginal() {
		return $this->objDstImage && $this->objDstImage == $this->getOriginalImage();
	}
	
}
