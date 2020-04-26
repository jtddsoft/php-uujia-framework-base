<?php


namespace uujia\framework\base\common\traits;


trait InstanceBase {
	/**
	 * 实例对象
	 */
	protected static $instance;
	
	protected function __clone() {
	}
	
	/**
	 * 单例模式获取实例
	 */
	public static function getInstance() {
		if (null === static::$instance) {
			// static::$instance = new static;
			
			// 反射构建实例化
			$reflection = new \ReflectionClass(static::class);
			static::$instance = $reflection->newInstanceArgs(func_get_args());// 传入的是关联数组
		}
		return static::$instance;
	}
	
	public static function factory() {
		return static::getInstance();
	}
	
}