<?php


namespace uujia\framework\base\common;


use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use uujia\framework\base\common\lib\FactoryCacheTree;
use uujia\framework\base\traits\NameBase;
use uujia\framework\base\traits\ResultBase;

/**
 * Class Container
 * 基础容器
 *
 * @package uujia\framework\base\common
 */
class Container implements ContainerInterface {
	use NameBase;
	use ResultBase;
	
	// private $c = [];
	// // 每次实例化都会存入对象实例 如果已存在就覆盖
	// private $lastObj = [];
	
	/** @var $_list FactoryCacheTree */
	protected $_list;
	
	public function __construct(FactoryCacheTree $list) {
		$this->_list = $list;
		
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
	
	// function __set($k, $c) {
	// 	$this->c[$k] = $c;
	// }
	
	// function __get($k) {
	// 	if ($this->hasCache($k)) {
	// 		return $this->cache($k);
	// 	}
	// 	return $this->c[$k]($this);
	// }
	
	/**
	 * 获取
	 *
	 * @param $id
	 * @return mixed
	 * @inheritDoc
	 */
	public function get($id) {
		// return $this->$id;
		// return $this->list()->get($id, [$this]);
		return $this->list()->get($id)->getDataValue();
	}
	
	/**
	 * 是否存在
	 *
	 * @param $id
	 *
	 * @return bool
	 * @inheritDoc
	 */
	public function has($id) {
		// return array_key_exists($id, $this->c);
		return $this->list()->has($id);
	}
	
	/**
	 * 设置
	 *
	 * @param $id
	 * @param \Closure $c
	 *
	 * @return $this
	 */
	public function set($id, \Closure $c) {
		$item = new FactoryCacheTree();
		$item->getData()->set(function ($data, $it) use ($c) {
			return call_user_func_array($c, [$data, $it, $this]);
		});
		
		$this->list()->set($id, $item);
		return $this;
	}
	
	/**
	 * 获取or设置 list
	 *
	 * @param null $list
	 * @return $this|FactoryCacheTree
	 */
	public function list($list = null): FactoryCacheTree {
		if ($list === null) {
			return $this->_list;
		} else {
			$this->_list = $list;
		}
		
		return $this;
	}
	
	// public function __call($method, $args) {
	// 	if ($this->isErr()) { return $this->return_error(); }
	//
	// 	// 从list中查找方法
	// 	if (is_callable([$this->list(), $method])) {
	// 		return call_user_func_array([$this->list(), $method], $args);
	// 	}
	//
	// 	// 方法不存在
	// 	$this->error('方法不存在', 1000);
	// 	return $this;
	// }
	
	// /**
	//  * 设置
	//  *
	//  * @param $id
	//  * @param $c
	//  *
	//  * @return $this
	//  */
	// public function set($id, $c) {
	// 	$this->$id = $c;
	// 	return $this;
	// }
	//
	// /**
	//  * 缓存实例
	//  *
	//  * @param $id
	//  * @param $obj
	//  *
	//  * @return mixed
	//  */
	// public function cache($id, $obj = null) {
	// 	if ($obj === null) {
	// 		return $this->lastObj[$id];
	// 	}
	//
	// 	$this->lastObj[$id] = $obj;
	// 	return $obj;
	// }
	//
	// /**
	//  * 缓存实例是否存在
	//  *
	//  * @param $id
	//  *
	//  * @return bool
	//  */
	// public function hasCache($id) {
	// 	return array_key_exists($id, $this->lastObj);
	// }
	//
	// /**
	//  * 删除缓存值
	//  *
	//  * @param $id
	//  */
	// public function removeCache($id) {
	// 	unset($this->lastObj[$id]);
	// }
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