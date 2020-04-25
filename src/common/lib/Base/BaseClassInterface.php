<?php

namespace uujia\framework\base\common\lib\Base;

/**
 * Interface BaseClassInterface
 *
 * @package uujia\framework\base\common\lib\Base
 */
interface BaseClassInterface {
	
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
	 * 复位 属性归零
	 *
	 * @param array $exclude
	 *
	 * @return $this
	 */
	public function reset($exclude = []);
	
	
}