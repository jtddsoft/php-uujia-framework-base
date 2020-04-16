<?php


namespace uujia\framework\base\common\lib\Event;

use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Cache\CacheClassInterface;
use uujia\framework\base\common\lib\Cache\CacheClassTrait;
use uujia\framework\base\common\lib\Server\ServerParameter;
use uujia\framework\base\common\lib\Server\ServerParameterInterface;
use uujia\framework\base\common\lib\Server\ServerRouteManager;

/**
 * Class EventListenerProxy
 * 事件监听代理（接口实现）
 *  负责为不同类型监听者提供与事件供应商的通讯
 *  1、标准事件类 代理保存事件类的类名（包括完整命名空间）。需要触发时会访问容器自动为其实例化，由代理调用预定方法。
 *  2、闭包 代理
 *
 * @package uujia\framework\base\common\lib\Event
 */
class EventListenerProxy extends BaseClass implements EventListenerProxyInterface {
	
	/**
	 * 服务器路由管理
	 * @var ServerRouteManager
	 */
	protected $_serverRouteManagerObj;
	
	/**
	 * 服务器参数
	 * @var ServerParameterInterface
	 */
	protected $_serverParameter;
	
	
	/**
	 * EventListenerProxy constructor.
	 *
	 * @param ServerRouteManager       $serverRouteManagerObj
	 * @param ServerParameterInterface $serverParameterObj
	 */
	public function __construct(ServerRouteManager $serverRouteManagerObj,
	                            ServerParameterInterface $serverParameterObj = null) {
		$this->_serverRouteManagerObj = $serverRouteManagerObj;
		$this->_serverParameter = $serverParameterObj ?? new ServerParameter();
		
		parent::__construct();
	}
	
	/**
	 * 执行触发
	 */
	public function handle() {
		$this->getServerRouteManagerObj()
		     ->setServerParameter($this->getServerParameter())
		     ->load();
	}
	
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
	/**
	 * @return ServerParameterInterface
	 */
	public function getServerParameter() {
		return $this->_serverParameter;
	}
	
	/**
	 * @param ServerParameterInterface $serverParameter
	 *
	 * @return EventListenerProxy
	 */
	public function setServerParameter($serverParameter) {
		$this->_serverParameter = $serverParameter;
		
		return $this;
	}
	
	/**
	 * @return ServerRouteManager
	 */
	public function getServerRouteManagerObj() {
		return $this->_serverRouteManagerObj;
	}
	
	/**
	 * @param ServerRouteManager $serverRouteManagerObj
	 * @return $this
	 */
	public function setServerRouteManagerObj($serverRouteManagerObj) {
		$this->_serverRouteManagerObj = $serverRouteManagerObj;
		
		return $this;
	}
	
	
}