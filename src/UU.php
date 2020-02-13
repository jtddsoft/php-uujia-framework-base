<?php

namespace uujia\framework\base;

use uujia\framework\base\common\lib\FactoryCacheTree;
use uujia\framework\base\common\Container;
use uujia\framework\base\traits\InstanceBase;
use uujia\framework\base\traits\NameBase;

class UU {
	use NameBase;
	use InstanceBase;
	
	/** @var $ret Container */
	protected static $container;
	
	/**
	 * UU constructor.
	 * 依赖SimpleContainer
	 */
	public function __construct() {
		self::$container = new Container(new FactoryCacheTree()); // $this
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
		$this->name_info['intro'] = '容器总管';
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