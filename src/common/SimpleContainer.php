<?php


namespace uujia\framework\base\common;


use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use uujia\framework\base\traits\NameBase;

class SimpleContainer implements ContainerInterface {
	use NameBase;
	
	private $c = [];
	// 每次实例化都会存入对象实例 如果已存在就覆盖
	private $lastObj = [];
	
	public function __construct() {
		$this->init();
	}
	
	/**
	 * 初始化
	 */
	public function init() {
		$this->initNameInfo();
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = self::class;
		$this->name_info['intro'] = '容器管理';
	}
	
	function __set($k, $c) {
		$this->c[$k] = $c;
	}
	
	function __get($k) {
		if ($this->hasCache($k)) {
			return $this->cache($k);
		}
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
	
	/**
	 * 缓存实例
	 *
	 * @param $id
	 * @param $obj
	 *
	 * @return mixed
	 */
	public function cache($id, $obj = null) {
		if ($obj === null) {
			return $this->lastObj[$id];
		}
		
		$this->lastObj[$id] = $obj;
		return $obj;
	}
	
	/**
	 * 缓存实例是否存在
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	public function hasCache($id) {
		return array_key_exists($id, $this->lastObj);
	}
	
	/**
	 * 删除缓存值
	 *
	 * @param $id
	 */
	public function removeCache($id) {
		unset($this->lastObj[$id]);
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