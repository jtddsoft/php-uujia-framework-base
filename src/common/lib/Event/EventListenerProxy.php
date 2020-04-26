<?php


namespace uujia\framework\base\common\lib\Event;

use uujia\framework\base\common\consts\EventConst;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Cache\CacheClassInterface;
use uujia\framework\base\common\lib\Cache\CacheClassTrait;
use uujia\framework\base\common\lib\Event\Cache\EventCacheDataInterface;
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
	 * @param ServerRouteManager       $serverRouteManagerObj
	 * @param ServerParameterInterface $serverParameterObj
	 */
	public function __construct(ServerRouteManager $serverRouteManagerObj,
	                            ServerParameterInterface $serverParameterObj = null) {
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
		$this->setSPCallBack(
			function (ServerRouteInterface $serverRoute,
			          ServerParameterInterface $serverParameter,
			          ServerRouteManager $serverRouteManager) {
				if (!$this->getContainer()) {
					return;
				}
			});
	}
	
	/**
	 * 执行触发
	 */
	public function handle() {
		$this->getServerRouteManagerObj()
		     ->setServerParameter($this->getServerParameter())
		     ->load(null, null, true);
		
		
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