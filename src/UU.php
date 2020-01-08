<?php

namespace uujia\framework\base;

use uujia\framework\base\common\SimpleContainer;
use uujia\framework\base\traits\InstanceBase;

class UU {
	use InstanceBase;
	
	/** @var $ret SimpleContainer */
	protected static $container;
	
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