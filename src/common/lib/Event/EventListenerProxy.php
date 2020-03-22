<?php


namespace uujia\framework\base\common\lib\Event;

use uujia\framework\base\common\lib\Server\ServerParameter;

/**
 * Class EventListenerProxy
 * 事件监听代理（接口实现）
 *  负责为不同类型监听者提供与事件供应商的通讯
 *  1、标准事件类 代理保存事件类的类名（包括完整命名空间）。需要触发时会访问容器自动为其实例化，由代理调用预定方法。
 *  2、闭包 代理
 *
 * @package uujia\framework\base\common\lib\Event
 */
class EventListenerProxy implements EventListenerProxyInterface {
	
	/**
	 *
	 * @var ServerParameter $_serverParameter
	 */
	protected $_serverParameter;
	
	/**
	 * EventListenerProxy constructor.
	 *
	 * @param ServerParameter $serverParameterObj
	 */
	public function __construct(ServerParameter $serverParameterObj) {
		$this->_serverParameter = $serverParameterObj;
	}
	
	
	
	
	
	
	
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
	/**
	 * @return ServerParameter
	 */
	public function getServerParameter(): ServerParameter {
		return $this->_serverParameter;
	}
	
	/**
	 * @param ServerParameter $serverParameter
	 */
	public function setServerParameter(ServerParameter $serverParameter): void {
		$this->_serverParameter = $serverParameter;
	}
	
	
}