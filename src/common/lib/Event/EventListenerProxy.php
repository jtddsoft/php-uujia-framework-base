<?php


namespace uujia\framework\base\common\lib\Event;


use uujia\framework\base\common\consts\EventConstInterface;
use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Event\Cache\EventCacheDataInterface;
use uujia\framework\base\common\lib\Event\Name\EventName;
use uujia\framework\base\common\lib\Event\EventRunStatus;
use uujia\framework\base\common\lib\Server\ServerParameter;
use uujia\framework\base\common\lib\Server\ServerParameterInterface;
use uujia\framework\base\common\lib\Server\ServerRouteInterface;
use uujia\framework\base\common\lib\Server\ServerRouteManager;
use uujia\framework\base\common\traits\ResultTrait;

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
	use ResultTrait;
	
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
	 * @var EventRunStatus
	 * @AutoInjection(name = "EventRunStatus")
	 */
	protected $_runStatus;
	
	
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
	 *
	 * @return $this
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
					return $this->code(10019); // 未找到容器对象
				}
				
				// todo: 导入最后一次返回值
				
				$classNS = $serverParameter->getClassNameSpace();
				if (empty($classNS)) {
					return $this->code(10102); // 未知的事件监听者
				}
				
				/** @var EventHandle $eventHandle */
				$eventHandle = $this->getContainer()
				                    ->get($classNS);
				
				$_params = $serverParameter->getParams();
				//$eventHandle->t($_eventNameObj->makeEventName(), $_params); // todo: 返回值
				// todo: 把eventName赋值eventH里 再触发Handle
				
				// 将eventName克隆值到EventHandle
				$eventHandle->getEventNameObj()
				            ->assign($_eventNameObj);
				
				// 触发 内部会根据eventName 查找对应的on监听方法 并执行
				$eventHandle->setParam($_params)
				            ->handle();
				
				$ret = $eventHandle->getLastReturn();
				
				// $serverParameter->_setRet($ret)
				
				$result = [
					'last_return' => $ret,
					'run_status'  => $eventHandle->assignToArray(),
				];
				
				return $result;
			});
		
		return $this;
	}
	
	/**
	 * 执行触发
	 */
	public function handle() {
		$this->resetResult();
		
		$re = $this->getServerRouteManagerObj()
		           ->setServerParameter($this->getServerParameter())
		           ->load(null, null, true)
		           ->route();
		
		if ($re === false) {
			// 记录最后返回值
			$this->assignLastReturn($this->error('非法的返回值'));
			
			// 记录运行状态 是否终止向下执行
			$this->getRunStatus()->assignFromArray(false);
			
			return $this;
		}
		
		// 记录最后返回值
		$this->assignLastReturn($re['last_return'] ?? $this->error('非法的返回值'));
		
		// 记录运行状态 是否终止向下执行
		$this->getRunStatus()->assignFromArray($re['run_status'] ?? false);
		
		return $this;
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
		// $this->resetSP()
		//      ->setSPServerName($cacheData[EventConstInterface::CACHE_SP_SERVERNAME] ?? '')
		//      ->setSPServerType($cacheData[EventConstInterface::CACHE_SP_SERVERTYPE] ?? '')
		//      ->setSPClassNameSpace($cacheData[EventConstInterface::CACHE_SP_CLASSNAMESPACE] ?? '')
		//      ->setSPParam($cacheData[EventConstInterface::CACHE_SP__PARAM] ?? [])
		//      ->make();
		
		$this->resetSP()
		     ->setSPServerName($cacheDataObj->getServerName())
		     ->setSPServerType($cacheDataObj->getServerType())
		     ->setSPClassNameSpace($cacheDataObj->getClassNameSpace())
		     ->setSPParam($cacheDataObj->getParam())
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
	
	/**
	 * @return EventRunStatus
	 */
	public function getRunStatus(): EventRunStatus {
		if (empty($this->_runStatus)) {
			$this->_runStatus = new EventRunStatus();
		}
		
		return $this->_runStatus;
	}
	
	/**
	 * @param EventRunStatus $runStatus
	 *
	 * @return $this
	 */
	public function _setRunStatus(EventRunStatus $runStatus) {
		$this->_runStatus = $runStatus;
		
		return $this;
	}
	
	
}