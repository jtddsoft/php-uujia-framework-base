<?php

namespace uujia\framework\base;

use uujia\framework\base\common\Result;

class Base {
	/** @var $ret Result */
	protected $ret;
	
	/**
	 * Base constructor.
	 * 依赖Result
	 *
	 * @param Result $ret
	 */
	public function __construct(Result $ret) {
		$this->ret = $ret;
	}
	
	// public static function call($name) {
	// 	return app('painter_logic_' . $name);
	// }
	
	// public function __get($name) {
	// 	return $this->$name;
	// }
	
	// public function __set($name, $value) {
	// 	$this->$name = $value;
	// }
	
	
}