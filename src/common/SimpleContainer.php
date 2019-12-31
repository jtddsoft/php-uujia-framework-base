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
	 * @inheritDoc
	 */
	public function get($id) {
		return $this->$id;
	}
	
	/**
	 * @inheritDoc
	 */
	public function has($id) {
		return array_key_exists($id, $this->c);
	}
	
	public function set($id, $c) {
		return $this->$id = $c;
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