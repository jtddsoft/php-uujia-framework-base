<?php


namespace uujia\framework\base\test;


use uujia\framework\base\common\Config;
use uujia\framework\base\common\ErrorCodeList;

class ErrorCodeListTest {
	/** @var ErrorCodeList $err */
	public $err;
	
	public function __construct() {
		$this->err = new ErrorCodeList(new Config());
	}
	
	public function toString() {
		$l = $this->err->find(101);
		var_dump($l);
	}
	
	
}

