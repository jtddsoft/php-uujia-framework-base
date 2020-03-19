<?php

namespace uujia\framework\base\common\lib\Event;

use uujia\framework\base\common\lib\Server\ServerRouteInterface;
use uujia\framework\base\traits\NameBase;
use uujia\framework\base\traits\ResultBase;

class ServerRouteLocal implements ServerRouteInterface {
	use NameBase;
	use ResultBase;
	
	/**
	 * 父级
	 */
	protected $_parent;
	
	/**
	 * 附加参数
	 *
	 * @var array $_param;
	 */
	protected $_param = [];
	
	/**
	 * 回调
	 *  （本地方式会直接回调 如果是远程方式 会将结果返回）
	 *
	 * @var callable|array $_callback
	 */
	protected $_callback = null;
	
	/**
	 * Local constructor.
	 *
	 * @param      $parent
	 */
	public function __construct($parent = null) {
		$this->_parent = $parent;
		
		$this->init();
	}
	
	/**
	 * 初始化
	 * @return $this
	 */
	public function init() {
		$this->initNameInfo();
		$this->initRoute();
		
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
	 * @inheritDoc
	 */
	public function initRoute() {
		$this->_param = [];
	}
	
	/**
	 * 附加参数
	 *
	 * @param null|array $param
	 * @return $this|array
	 */
	public function param($param = null) {
		if ($param === null) {
			return $this->_param;
		} else {
			$this->_param = $param;
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function route() {
		return $this->ok();
	}
	
	// /**
	//  * 触发
	//  *
	//  * @param string $_listener    监听者名称
	//  * @param $params
	//  * @return mixed
	//  */
	// public function trigger($_listener, $params) {
	// 	// todo: 检查是否事件类 如果是则调用事件类的专用触发 如果只是一般回调就直接触发
	// 	$res = call_user_func_array($_listener, [$params]);
	//
	// 	return $res;
	// }
	
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
	
	/**
	 * @return callable|array
	 */
	public function getCallback() {
		return $this->_callback;
	}
	
	/**
	 * @param callable|array $callback
	 *
	 * @return $this
	 */
	public function setCallback($callback) {
		$this->_callback = $callback;
		
		return $this;
	}
	
	
}