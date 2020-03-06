<?php

namespace uujia\framework\base\common\lib\Event;

use uujia\framework\base\common\consts\ServerConst;
use uujia\framework\base\traits\InstanceBase;
use uujia\framework\base\traits\NameBase;
use uujia\framework\base\traits\ResultBase;

abstract class EventHandle {
	use NameBase;
	use ResultBase;
	use InstanceBase;
	
	/**
	 * 唯一标识
	 *  此处的值是Demo 继承类需要重新生成
	 */
	protected $_uuid = 'cdd64cb6-29b8-4663-b1b5-f4f515ed28ca';
	
	// /**
	//  * 事件名称
	//  *  用于触发和监听
	//  * @var string $_name
	//  */
	// protected $_name = '';
	
	/** @var Local $_local */
	protected $_localObj = null;
	
	// todo: POST
	protected $_postObj = null;
	
	/**
	 * EventHandle constructor.
	 *
	 * @param $uuid
	 */
	public function __construct($uuid = 'cdd64cb6-29b8-4663-b1b5-f4f515ed28ca') {
		$this->_uuid = $uuid;
		
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
		$this->name_info['intro'] = '事件处理本地模板类';
	}
	
	/**************************************************
	 * local func
	 **************************************************/
	
	public function _event_trigger() {
	
	}
	
	public function _event_listen($params) {
		// list ($data, $eventItem, $callParams, $name, $serverName, $serverConfig, $server) = $params;
		list ($fParams, $name, $serverName, $serverConfig, $server) = $params;
		
		// 根据类型 知道是本地还是远端
		switch ($server['type']) {
			case ServerConst::TYPE_LOCAL_NORMAL:
				// 本地服务器
				$_local = $this->getLocalObj();
				
				// 触发事件时执行回调
				// $res = call_user_func_array($_listener, [$params, $_lastResult, $_results]);
				$res = $_local->trigger($name, $fParams);
				
				// // Local返回值复制
				// $this->setLastReturn($_local->getLastReturn());
				//
				// $it->getParent()->addKeyParam('result', $_local->getLastReturn());
				break;
			
			default:
				// 远程服务器
				// todo：MQ通信 POST请求之类
				break;
		}
	}
	
	/**************************************************
	 * func
	 **************************************************/
	
	
	
	
	
	
	
	/**************************************************
	 * getter setter
	 **************************************************/
	
	/**
	 * @return string
	 */
	public function getUuid(): string {
		return $this->_uuid;
	}
	
	/**
	 * @param string $uuid
	 *
	 * @return $this
	 */
	public function setUuid(string $uuid) {
		$this->_uuid = $uuid;
		
		return $this;
	}
	
	/**
	 * @return Local
	 */
	public function getLocalObj(): Local {
		$this->_localObj === null && $this->_localObj = new Local($this);
		
		return $this->_localObj;
	}
	
	/**
	 * @param Local $localObj
	 * @return $this
	 */
	public function _setLocal(Local $localObj) {
		$this->_localObj = $localObj;
		
		return $this;
	}
	
	
	
}