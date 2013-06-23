<?php

namespace bbit\image\operation;

use bbit\image\Canvas;

abstract class CanvasOp {

	protected $sourceImmutable;

	protected function __construct($sourceImmutable = true) {
		$this->setOriginalImmutable($sourceImmutable);
	}

	public function setSourceImmutable($sourceImmutable) {
		$this->sourceImmutable = $sourceImmutable;
		return $this;
	}

	public function isSourceImmutable() {
		return $this->sourceImmutable;
	}

	public function execute(Canvas $src) {
		$src->requireValid();
		$this->modifiesSource($src) && $this->sourceImmutable && $src = clone $src;
		return $src;
	}

	protected function modifiesSource(Canvas $src) {
		return true;
	}

	protected abstract function perform(Canvas $src);

}