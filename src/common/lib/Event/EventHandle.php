<?php

namespace uujia\framework\base\common\lib\Event;

use Psr\EventDispatcher\StoppableEventInterface;
use uujia\framework\base\common\consts\ServerConst;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\traits\InstanceBase;
use uujia\framework\base\common\traits\NameBase;
use uujia\framework\base\common\traits\ResultBase;

/**
 * Class EventHandle
 * 事件具体监听及触发者
 *  每个事件类都要继承
 *
 * @package uujia\framework\base\common\lib\Event
 */
abstract class EventHandle extends BaseClass implements EventHandleInterface, StoppableEventInterface {
	use ResultBase;
	use InstanceBase;
	
	/**
	 * 唯一标识
	 *  此处的值是Demo 继承类需要重新生成
	 */
	protected $_uuid = 'cdd64cb6-29b8-4663-b1b5-f4f515ed28ca';
	
	/**
	 * 是否终止事件队列
	 *  不再触发之后的事件
	 * @var bool $_isStopped
	 */
	protected $_isStopped = false;
	
	// /**
	//  * 事件名称
	//  *  用于触发和监听
	//  * @var string $_name
	//  */
	// protected $_name = '';
	
	/** @var ServerRouteLocal $_local */
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
		
		parent::__construct();
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
	
	/**
	 * @inheritDoc
	 */
	public function isPropagationStopped(): bool {
		return $this->_isStopped;
	}
	
	public function _event_trigger() {
	
	}
	
	public function _event_listen($params) {
		// list ($data, $eventItem, $callParams, $name, $serverName, $serverConfig, $server) = $params;
		[$fParams, $name, $serverName, $serverConfig, $server] = $params;
		
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
	 * @return ServerRouteLocal
	 */
	public function getLocalObj(): ServerRouteLocal {
		$this->_localObj === null && $this->_localObj = new ServerRouteLocal($this);
		
		return $this->_localObj;
	}
	
	/**
	 * @param ServerRouteLocal $localObj
	 *
	 * @return $this
	 */
	public function _setLocal(ServerRouteLocal $localObj) {
		$this->_localObj = $localObj;
		
		return $this;
	}
	
	
	
}