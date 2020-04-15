<?php


namespace uujia\framework\base\test;


use uujia\framework\base\common\Config;
use uujia\framework\base\common\Error;

class ErrorCodeListTest {
	/** @var Error $err */
	public $err;
	
	public function __construct() {
		$this->err = new Error(new Config());
	}
	
	public function toString() {
		$l = $this->err->find(101);
		var_dump($l);
	}
	
	
}

