<?php

namespace uujia\framework\base;


use uujia\framework\base\common\lib\Container\Container;
use uujia\framework\base\common\lib\Tree\TreeFunc;
use uujia\framework\base\common\traits\InstanceTrait;
use uujia\framework\base\common\traits\NameTrait;

/**
 * Class UU
 *
 * @package uujia\framework\base
 */
class UU {
	use NameTrait;
	use InstanceTrait;
	
	/**
	 * @var Container
	 */
	protected $_container;
	
	/**
	 * UU constructor.
	 * 依赖Container
	 *
	 * @param null|Container|mixed $container
	 */
	public function __construct($container = null) {
		// self::$_container = new Container(new TreeFunc()); // $this
		// self::$_container = $container ?? Container::getInstance(new TreeFunc());
		// $this->_container = $container ?? Container::getInstance(new TreeFunc());
		$this->_container = $container ?? new Container(new TreeFunc());
		
		$this->init();
	}
	
	// /**
	//  * @return Container
	//  */
	// public static function getContainer() {
	// 	// $me = static::getInstance();
	//
	// 	return self::$_container;
	// }
	//
	// /**
	//  * @param Container $container
	//  */
	// public static function _setContainer($container) {
	// 	self::$_container = $container;
	// }
	
	/**
	 * 初始化
	 * @return $this
	 */
	public function init() {
		$this->initNameInfo();
		
		return $this;
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
	 * @param string|array  $objName
	 * @param null|\Closure $obj
	 *
	 * @return mixed
	 */
	public static function C($objName, $obj = null) {
		/** @var UU $me */
		$me = static::getInstance();
		
		// 【注意】如果为数组 则批量注入（并非是获取 只有为字符串类名时才是获取）
		if (is_array($objName)) {
			foreach ($objName as $key => $row) {
				$me->getContainer()->set($row, $obj);
			}
			
			return $me->getContainer();
		}
		
		if ($obj === null) {
			// 读取
			return $me->getContainer()->get($objName);
		} else {
			// 设置
			return $me->getContainer()->set($objName, $obj);
		}
	}
	
	/**
	 * @return Container
	 */
	public function getContainer() {
		return $this->_container;
	}
	
	/**
	 * @param Container $container
	 *
	 * @return $this
	 */
	public function setContainer($container) {
		$this->_container = $container;
		
		return $this;
	}
	
	
}