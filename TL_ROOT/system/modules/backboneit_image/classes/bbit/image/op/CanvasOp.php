<?php

namespace bbit\image\op;

use bbit\image\Canvas;

abstract class CanvasOp {

	private $subject;
	private $preserveSubject = false;

	protected function __construct() {
	}

	public function getSubject() {
		return $this->subject;
	}

	public function setSubject(Canvas $subject) {
		$this->subject = $subject;
		return $this;
	}

	public function shouldPreserveSubject() {
		return $this->preserveSubject;
	}

	public function setPreserveSubject($preserveSubject) {
		$this->preserveSubject = (bool) $preserveSubject;
		return $this;
	}

	protected function prepareSubject() {
		$subject = $this->getSubject();
		if(!$subject) {
			throw new \LogicException('No subject canvas set for operation');
		}
		$subject->requireValid();
		$this->shouldPreserveSubject() && $this->isModifyingSubject() && $subject = $subject->fork();
		return $subject;
	}

	protected function isModifyingSubject() {
		return true;
	}

	public abstract function execute();

}
