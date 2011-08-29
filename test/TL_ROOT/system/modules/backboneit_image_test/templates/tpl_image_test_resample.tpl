<?php

define('IMAGE_TEST_RESAMPLE_DIR', 'system/modules/backboneit_image_test/images/resample');

function image_test_resample($strFile) {
	try {
		echo '<br/><br/><br/><h1>' . $strFile . '</h1>';
		
		$objFile = ImageFactory::getFile(IMAGE_TEST_RESAMPLE_DIR . '/' . $strFile);
		echo '<br/> 1 getFile OK';
		
		$objImage = ImageFactory::createFromFile($objFile);
		echo '<br/> 2 createFromFile OK';
		
		$objOp = new ResampleOperation($objImage);
		$objOp->setDstArea(new Size(100, 100));
		$objOp->execute();
		
		$objFormat = new PNGFormat();
		echo '<br/><img src="' . IMAGE_TEST_RESAMPLE_DIR . '/' . $strFile . '" width="" height="" />';
		echo '<br/><img src="data:image/png;base64,' . base64_encode($objFormat->getBinary($objOp->getResult(), true)) . '" width="100" height="100" />';
		
		echo $strFile . ' -> OK';
		
	} catch(Exception $e) {
		echo $strFile . ' -> ERROR -> ' . $e->getMessage();
	}
}

array_map('image_test_resample', scan(TL_ROOT . '/' . IMAGE_TEST_RESAMPLE_DIR));
