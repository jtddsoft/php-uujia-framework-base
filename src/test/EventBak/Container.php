<?php


namespace uujia\framework\base\common;


use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionMethod;
use ReflectionParameter;
use uujia\framework\base\common\lib\Container\Container;
use uujia\framework\base\common\lib\Tree\TreeFuncData;
use uujia\framework\base\common\lib\Tree\TreeFunc;
use uujia\framework\base\common\lib\Utils\Reflection;
use uujia\framework\base\common\traits\NameBase;
use uujia\framework\base\common\traits\ResultBase;

/**
 * Class Container
 * 基础容器
 *
 * @package uujia\framework\base\common
 */
class Container extends Container {
	
	
	/**
	 * Container constructor.
	 *
	 * @param TreeFunc|null $list
	 */
	public function __construct(TreeFunc $list = null) {
		parent::__construct($list);
	}
	
	/**
	 * 初始化
	 * @return $this
	 */
	public function init() {
		parent::init();
		
		return $this;
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = self::class;
		$this->name_info['intro'] = '容器管理';
	}
	
}
