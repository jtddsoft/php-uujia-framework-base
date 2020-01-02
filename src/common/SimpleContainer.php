<?php


namespace uujia\framework\base\common;


use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class SimpleContainer implements ContainerInterface {
	private $c = [];
	
	function __set($k, $c) {
		$this->c[$k] = $c;
	}
	
	function __get($k) {
		return $this->c[$k]($this);
	}
	
	/**
	 * 获取
	 * @inheritDoc
	 */
	public function get($id) {
		return $this->$id;
	}
	
	/**
	 * 是否存在
	 * @inheritDoc
	 */
	public function has($id) {
		return array_key_exists($id, $this->c);
	}
	
	/**
	 * 设置
	 *
	 * @param $id
	 * @param $c
	 *
	 * @return $this
	 */
	public function set($id, $c) {
		$this->$id = $c;
		return $this;
	}
}

// demo
// $class = new Container();
//
// $class->c = function () {
// 	return new C();
// };
// $class->b = function ($class) {
// 	return new B($class->c);
// };
// $class->a = function ($class) {
// 	return new A($class->b);
// };