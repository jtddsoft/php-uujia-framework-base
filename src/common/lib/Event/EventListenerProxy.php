<?php


namespace uujia\framework\base\common\lib\Event;

use uujia\framework\base\common\consts\EventConst;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Cache\CacheClassInterface;
use uujia\framework\base\common\lib\Cache\CacheClassTrait;
use uujia\framework\base\common\lib\Event\Cache\EventCacheDataInterface;
use uujia\framework\base\common\lib\Event\Name\EventName;
use uujia\framework\base\common\lib\Server\ServerParameter;
use uujia\framework\base\common\lib\Server\ServerParameterInterface;
use uujia\framework\base\common\lib\Server\ServerRouteInterface;
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
	 * 事件名称对象
	 *
	 * @var EventName
	 */
	protected $_eventNameObj;
	
	/**
	 * 服务器路由管理
	 *
	 * @var ServerRouteManager
	 */
	protected $_serverRouteManagerObj;
	
	/**
	 * 服务器参数
	 *
	 * @var ServerParameterInterface
	 */
	protected $_serverParameter;
	
	
	/**
	 * EventListenerProxy constructor.
	 *
	 * @param EventName                $eventNameObj
	 * @param ServerRouteManager       $serverRouteManagerObj
	 * @param ServerParameterInterface $serverParameterObj
	 */
	public function __construct(EventName $eventNameObj,
	                            ServerRouteManager $serverRouteManagerObj,
	                            ServerParameterInterface $serverParameterObj = null) {
		$this->_eventNameObj          = $eventNameObj;
		$this->_serverRouteManagerObj = $serverRouteManagerObj;
		$this->_serverParameter       = $serverParameterObj ?? new ServerParameter();
		
		parent::__construct();
	}
	
	/**************************************************************
	 * 构建 触发
	 **************************************************************/
	
	/**
	 * 构建
	 */
	public function make() {
		$_eventNameObj = $this->getEventNameObj();
		
		$this->setSPCallBack(
		// 如果本地触发将执行的操作
			function (ServerRouteInterface $serverRoute,
			          ServerParameterInterface $serverParameter,
			          ServerRouteManager $serverRouteManager) use ($_eventNameObj) {
				/**
				 * 调用容器执行对应事件对象中的触发方法
				 */
				
				// 判断容器对象是否存在
				if (!$this->getContainer()) {
					return;
				}
				
				$classNS = $serverParameter->getClassNameSpace();
				if (empty($classNS)) {
					return;
				}
				
				/** @var EventHandle $eventHandle */
				$eventHandle = $this->getContainer()
				                    ->get($classNS);
				
				$_params = $serverParameter->getParams();
				//$eventHandle->t($_eventNameObj->makeEventName(), $_params); // todo: 返回值
				// todo: 把eventName赋值eventH里 再出发Handle
			});
	}
	
	/**
	 * 执行触发
	 */
	public function handle() {
		$this->getServerRouteManagerObj()
		     ->setServerParameter($this->getServerParameter())
		     ->load(null, null, true)
		     ->route();
	}
	
	/**************************************************************
	 * data ServerParameter
	 **************************************************************/
	
	/**
	 * 载入缓存数据
	 *
	 * @param EventCacheDataInterface $cacheDataObj
	 *
	 * @return $this
	 */
	public function loadCache(EventCacheDataInterface $cacheDataObj) {
		$this->resetSP()
		     ->setSPServerName($cacheData[EventConst::CACHE_SP_SERVERNAME] ?? '')
		     ->setSPServerType($cacheData[EventConst::CACHE_SP_SERVERTYPE] ?? '')
		     ->setSPClassNameSpace($cacheData[EventConst::CACHE_SP_CLASSNAMESPACE] ?? '')
		     ->setSPParam($cacheData[EventConst::CACHE_SP__PARAM] ?? [])
		     ->make();
		
		return $this;
	}
	
	/**
	 * 重置ServerParameter
	 *
	 * @return $this
	 */
	public function resetSP() {
		$this->getServerParameter()
		     ->reset();
		
		return $this;
	}
	
	/**
	 * 清空ServerParameter返回值
	 *
	 * @return $this
	 */
	public function clearSPRet() {
		$this->getServerParameter()
		     ->resetRet();
		
		return $this;
	}
	
	/**
	 * 设置服务名称
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function setSPServerName($name = '') {
		$this->getServerParameter()
		     ->setServerName($name);
		
		return $this;
	}
	
	/**
	 * 设置服务类型
	 *
	 * @param string $type
	 *
	 * @return $this
	 */
	public function setSPServerType($type = '') {
		$this->getServerParameter()
		     ->setServerType($type);
		
		return $this;
	}
	
	/**
	 * 设置本地执行的类名
	 *
	 * @param string $classNameSpace
	 *
	 * @return $this
	 */
	public function setSPClassNameSpace($classNameSpace = '') {
		$this->getServerParameter()
		     ->setClassNameSpace($classNameSpace);
		
		return $this;
	}
	
	/**
	 * 设置服务回调
	 *
	 * @param \Closure $callback
	 *
	 * @return $this
	 */
	public function setSPCallBack(\Closure $callback) {
		$this->getServerParameter()
		     ->setCallback($callback);
		
		return $this;
	}
	
	/**
	 * 设置ServerParameter执行时附加参数
	 *
	 * @param array $param
	 *
	 * @return $this
	 */
	public function setSPParam($param = []) {
		$this->getServerParameter()
		     ->_setParams($param);
		
		return $this;
	}
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
	/**
	 * @return EventName
	 */
	public function getEventNameObj() {
		return $this->_eventNameObj;
	}
	
	/**
	 * @param EventName $eventNameObj
	 *
	 * @return $this
	 */
	public function setEventNameObj($eventNameObj) {
		$this->_eventNameObj = $eventNameObj;
		
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
	 *
	 * @return $this
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
	 *
	 * @return $this
	 */
	public function setServerRouteManagerObj($serverRouteManagerObj) {
		$this->_serverRouteManagerObj = $serverRouteManagerObj;
		
		return $this;
	}
	
	
}