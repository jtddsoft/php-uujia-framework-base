<?php


namespace uujia\framework\base\common\traits;


trait InstanceTrait {
	/**
	 * 实例对象
	 */
	protected static $instance;
	
	/**
	 * 反射对象
	 * @var \ReflectionClass
	 */
	protected static $reflection;
	
	protected function __clone() {
	}
	
	/**
	 * 单例模式获取实例
	 *
	 * @return $this
	 */
	public static function getInstance() {
		if (null === static::$instance) {
			// static::$instance = new static;
			
			// 反射构建实例化
			static::$reflection = new \ReflectionClass(static::class);
			static::$instance = static::$reflection->newInstanceArgs(func_get_args());// 传入的是关联数组
		}
		return static::$instance;
	}
	
	/**
	 * @return $this
	 */
	public static function factory() {
		return static::getInstance();
	}
	
	/**
	 * @return $this
	 */
	public static function me() {
		return static::getInstance();
	}
	
}