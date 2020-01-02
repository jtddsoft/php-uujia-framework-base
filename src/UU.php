<?php

namespace uujia\framework\base;

use uujia\framework\base\common\SimpleContainer;

class UU {
	protected static $instance;
	
	/** @var $ret SimpleContainer */
	protected static $container;
	
	protected function __clone() {
	}
	
	/**
	 * 单例模式获取实例
	 */
	public static function getInstance() {
		if (null === static::$instance) {
			static::$instance = new static;
		}
		return static::$instance;
	}
	
	/**
	 * Base constructor.
	 * 依赖Result
	 *
	 * @param String $name
	 */
	public function __construct() {
		self::$container = new SimpleContainer();
	}
	
	/**
	 * 返回从容器中获取对象实例
	 *
	 * @param      $objName
	 * @param null $obj
	 *
	 * @return mixed
	 */
	public static function C($objName, $obj = null) {
		$me = static::getInstance();
		
		if ($obj === null) {
			// 读取
			return self::$container->get($objName);
		} else {
			// 设置
			return self::$container->set($objName, $obj);
		}
	}
	
	
}