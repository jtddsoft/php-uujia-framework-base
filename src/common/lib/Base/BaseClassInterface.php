<?php

namespace uujia\framework\base\common\lib\Base;

use Psr\Container\ContainerInterface;
use uujia\framework\base\common\interfaces\BaseInterface;

/**
 * Interface BaseClassInterface
 *
 * @package uujia\framework\base\common\lib\Base
 */
interface BaseClassInterface extends BaseInterface {
	
	/**
	 * 初始化
	 * @return $this
	 */
	public function init();
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo();
	
	/**
	 * 分配
	 *  对象数据克隆（推荐使用clone关键字 此处只提供另一种可继承的显式克隆途径）
	 *
	 * @param $obj
	 *
	 * @return $this
	 */
	public function assign($obj);
	
	/**
	 * 复位 属性归零
	 *
	 * @param array $exclude
	 *
	 * @return $this
	 */
	public function reset($exclude = []);
	
	/**
	 * @return ContainerInterface
	 */
	public function getContainer();
	
	/**
	 * @param ContainerInterface $container
	 *
	 * @return $this
	 */
	public function _setContainer(ContainerInterface $container);
	
}