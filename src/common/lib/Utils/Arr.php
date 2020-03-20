<?php

namespace uujia\framework\base\common\lib\Utils;


use uujia\framework\base\common\traits\InstanceBase;

class Arr {
	use InstanceBase;
	
	protected $_arr;
	
	/**
	 * @param array $arr
	 * @return Arr
	 */
	public static function from(array $arr) {
		/** @var Arr $me */
		$me = static::getInstance();
		$me->setArr($arr);
		
		return $me;
	}
	
	public function first() {
		return $this->_arr[0] ?? null;
	}
	
	/**
	 * last
	 *
	 * @return mixed
	 */
	public function last() {
		return $this->_arr[count($this->_arr) - 1] ?? null;
	}
	
	/**
	 * @return $this
	 */
	public function getArr() {
		return $this->_arr;
	}
	
	/**
	 * @param array $arr
	 * @return $this
	 */
	public function setArr(array $arr) {
		$this->_arr = $arr;
		
		return $this;
	}
	
	
}