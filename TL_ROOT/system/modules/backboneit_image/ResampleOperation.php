<?php

//namespace backboneit\image\operations;

//use backboneit\image\Point2D as Point2D;
//use backboneit\image\Size as Size;
//use backboneit\image\Image as Image;

class ResampleOperation extends ImageOperation {

	public function __construct(Image $objOriginal = null, $blnOriginalImmutable = true) {
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
	
	protected function perform(Image $objSource) {
		$objSrcPoint = $this->objSrcPoint ? $this->objSrcPoint : new Point2D(0, 0);
		$objSrcSize = $this->objSrcSize ? $this->objSrcSize : Size::createFromPoint($objSource->getSize()->toPoint()->subtract($objSrcPoint));
		
		$objSrcSize->checkNonNullArea();
		$objSource->getSize()->checkValidSubArea($objSrcSize, $objSrcPoint);
		
		$objDstPoint = $this->objDstPoint ? $this->objDstPoint : new Point2D(0, 0);
		$objDstSize = $this->objDstSize ? $this->objDstSize : Size::createFromPoint($objSrcSize->toPoint()->add($objDstPoint));
		
		$objDstSize->checkNonNullArea();
		
		$objDstImage = $this->objDstImage ? $this->objDstImage->toTrueColorImage() : ImageFactory::createTrueColorImage($objDstSize);
		$resDstImage = $objDstImage->getResource();
		
		$objDstImage->getSize()->checkValidSubArea($objDstSize, $objDstPoint);
		
		$blnAlphaBlending = $objDstImage->getAlphaBlending();
		$objDstImage->setAlphaBlending(true);
		$objBottomRight = $objDstImage->getSize()->toPoint();
		imagefilledrectangle($resDstImage,
			0, 0,
			$objBottomRight->getX(), $objBottomRight->getY(),
			$objDstImage->getColorIndex(new Color(0, 0, 0, 255))
		);
		$objDstImage->setAlphaBlending($this->blnAlphaBlending);
		
		imagecopyresampled($resDstImage, $objSource->getResource(),
			$objDstPoint->getX(), $objDstPoint->getY(),
			$objSrcPoint->getX(), $objSrcPoint->getY(),
			$objDstSize->getWidth(), $objDstSize->getHeight(),
			$objSrcSize->getWidth(), $objSrcSize->getHeight()
		);
		
		$objDstImage->setAlphaBlending($blnAlphaBlending);
		
		return $objDstImage;
	}
	
	public function modifiesOriginal() {
		return $this->objDstImage && $this->objDstImage == $this->getOriginalImage();
	}
	
}
