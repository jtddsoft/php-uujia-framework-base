<?php

namespace uujia\framework\base\common\lib\Base;


use Psr\Container\ContainerInterface;
use uujia\framework\base\common\traits\NameBase;

class BaseClass implements BaseClassInterface {
	use NameBase;
	
	/**
	 * 容器
	 * 自动注入时 由容器自主提供
	 * @var ContainerInterface
	 */
	protected $_container = null;
	
	/**
	 * BaseClass constructor.
	 */
	public function __construct() {
		
		$this->init();
		$this->reset();
	}
	
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
		$this->name_info['intro'] = '基础类';
	}
	
	/**
	 * 复位 属性归零
	 *
	 * @param array $exclude
	 *
	 * @return $this
	 */
	public function reset($exclude = []) {
		return $this;
	}
	
	/**
	 * @return ContainerInterface
	 */
	public function getContainer() {
		return $this->_container;
	}
	
	/**
	 * @param ContainerInterface $container
	 *
	 * @return $this
	 */
	public function _setContainer(ContainerInterface $container) {
		$this->_container = $container;
		
		return $this;
	}
	
}