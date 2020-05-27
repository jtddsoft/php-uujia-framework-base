<?php

namespace uujia\framework\base\common\lib\Event;

use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Server\ServerParameter;
use uujia\framework\base\common\lib\Server\ServerParameterInterface;
use uujia\framework\base\common\lib\Server\ServerRouteInterface;
use uujia\framework\base\common\lib\Server\ServerRouteManager;
use uujia\framework\base\common\traits\ResultTrait;

/**
 * Class ServerRouteLocal
 *
 * @package uujia\framework\base\common\lib\Event
 */
class ServerRouteLocal extends BaseClass implements ServerRouteInterface {
	use ResultTrait;
	
	/**
	 * 父级
	 *
	 * @var ServerRouteManager
	 */
	protected $_parent;
	
	/**
	 * 服务器参数类
	 *
	 * @var ServerParameterInterface
	 */
	protected $_serverParameter = null;
	
	// /**
	//  * 附加参数
	//  *
	//  * @var array $_param;
	//  */
	// protected $_param = [];
	
	// /**
	//  * 回调
	//  *  （本地方式会直接回调 如果是远程方式 调用回调取回结果返回）
	//  *
	//  * @var callable $_callback
	//  */
	// protected $_callback = null;
	
	/**
	 * ServerRouteLocal constructor.
	 *
	 * @param ServerRouteManager            $parent
	 * @param ServerParameterInterface|null $serverParameter
	 *
	 * @AutoInjection(arg = "parent", type = "v" value = null)
	 * @AutoInjection(arg = "serverParameter", type = "v" value = null)
	 */
	public function __construct(ServerRouteManager $parent = null,
	                            ServerParameterInterface $serverParameter = null) {
		$this->_parent = $parent;
		$this->_serverParameter = $serverParameter;
		
		parent::__construct();
	}
	
	/**
	 * 初始化
	 * @return $this
	 */
	public function init() {
		parent::init();
		
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
	
	// /**
	//  * 附加参数
	//  *
	//  * @param null|array $param
	//  * @return $this|array
	//  */
	// public function param($param = null) {
	// 	if ($param === null) {
	// 		return $this->_param;
	// 	} else {
	// 		$this->_param = $param;
	// 	}
	//
	// 	return $this;
	// }
	
	/**
	 * @inheritDoc
	 */
	public function route() {
		$this->resetResult();
		
		$callback = $this->getCallback();
		
		if ($callback && is_callable($callback)) {
			$re = call_user_func_array($callback, [$this, $this->getServerParameter(), $this->getParent()]);
			// todo: 返回值
			$this->assignLastReturn($re);
			return $this;
		}
		
		return $this;
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
	 * @return ServerRouteManager
	 */
	public function getParent() {
		return $this->_parent;
	}
	
	/**
	 * @param ServerRouteManager $parent
	 * @return $this
	 */
	public function setParent($parent) {
		$this->_parent = $parent;
		
		return $this;
	}
	
	/**
	 * @return ServerParameterInterface
	 */
	public function getServerParameter() {
		return $this->_serverParameter;
	}
	
	/**
	 * @param ServerParameterInterface $serverParameter
	 * @return $this
	 */
	public function setServerParameter(ServerParameterInterface $serverParameter) {
		$this->_serverParameter = $serverParameter;
		
		return $this;
	}
	
	/**
	 * @return callable
	 */
	public function getCallback() {
		return $this->getServerParameter()->getCallback();
	}
	
	/**
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function _setCallback($callback) {
		$this->getServerParameter()->setCallback($callback);
		
		return $this;
	}
	
	
}