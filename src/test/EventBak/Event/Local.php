<?php

namespace uujia\framework\base\common\lib\Event;

use uujia\framework\base\common\traits\NameBase;
use uujia\framework\base\common\traits\ResultBase;

class Local {
	use NameBase;
	use ResultBase;
	
	/**
	 * 父级
	 */
	protected $_parent;
	
	
	/**
	 * Local constructor.
	 *
	 * @param      $parent
	 */
	public function __construct($parent) {
		$this->_parent = $parent;
		
		$this->init();
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
		$this->name_info['intro'] = '事件处理本地服务';
	}
	
	/**
	 * 触发
	 *
	 * @param string $_listener    监听者名称
	 * @param $params
	 * @return mixed
	 */
	public function trigger($_listener, $params) {
		// todo: 检查是否事件类 如果是则调用事件类的专用触发 如果只是一般回调就直接触发
		$res = call_user_func_array($_listener, [$params]);
		
		return $res;
	}
	
	/**************************************************
	 * getter setter
	 **************************************************/
	
	/**
	 * @return mixed
	 */
	public function getParent() {
		return $this->_parent;
	}
	
	/**
	 * @param mixed $parent
	 * @return $this
	 */
	public function _setParent($parent) {
		$this->_parent = $parent;
		
		return $this;
	}
	
	
}