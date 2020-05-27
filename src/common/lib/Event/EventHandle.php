<?php

namespace uujia\framework\base\common\lib\Event;

use Psr\EventDispatcher\StoppableEventInterface;
use uujia\framework\base\common\consts\ServerConst;
use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Event\Name\EventName;
use uujia\framework\base\common\lib\Runner\RunnerManager;
use uujia\framework\base\common\traits\ResultTrait;

/**
 * Class EventHandle
 * 事件具体监听及触发者
 *  每个事件类都要继承
 *
 * 事件定义（首字母小写驼峰）：
 *  addon|plugin|app|sys.{component_name|addon_name|plugin_name}.{event_name}.{behavior_name}.[{trigger_timing}]:{uuid}
 * 示例：
 *  app.order.goods.add.before:cdd64cb6-29b8-4663-b1b5-f4f515ed28ca
 *
 * 事件完整定义（缓存中的完整定义 evtl=event_listen  evtt=event_trigger）：
 *  {app_name}:{mode_name[evtl|evtt]}:
 *      addon|plugin|app|sys.{component_name|addon_name|plugin_name}.{event_name}.{behavior_name}[.{trigger_timing}]:{uuid}[:{tmp}]
 *      示例： shopMall:evtl:app.order.goods.add.before:cdd64cb6-29b8-4663-b1b5-f4f515ed28ca
 *
 * @package uujia\framework\base\common\lib\Event
 */
abstract class EventHandle extends BaseClass implements EventHandleInterface, StoppableEventInterface {
	use ResultTrait;
	
	/**
	 * 唯一标识
	 *  此处的值是Demo 继承类需要重新生成
	 *
	 * @var string
	 */
	protected $_uuid = '';
	
	/**
	 * 触发的事件名称
	 *
	 * @var string
	 */
	protected $_triggerName = '';
	
	/**
	 * 运行时管理对象
	 *
	 * @var RunnerManager
	 */
	protected $_runnerManagerObj = null;
	
	/**
	 * 事件名称
	 *
	 * @var EventName
	 */
	protected $_eventNameObj = null;
	
	// /**
	//  * 事件名称拆分后各个属性
	//  * @var array
	//  */
	// protected $_eventNameParse = [];
	
	/**
	 * 附加参数
	 *
	 * @var array
	 */
	protected $_param = [];
	
	/**
	 * 是否终止事件队列
	 *  不再触发之后的事件
	 *
	 * @var bool
	 */
	protected $_stopped = false;
	
	// /**
	//  * 事件名称
	//  *  用于触发和监听
	//  * @var string $_name
	//  */
	// protected $_name = '';
	
	/** @var ServerRouteLocal */
	// protected $_localObj = null;
	
	// todo: POST
	// protected $_postObj = null;
	
	/**
	 * EventHandle constructor.
	 *
	 * @param RunnerManager $runnerManagerObj
	 * @param EventName     $eventNameObj
	 *
	 * @AutoInjection(arg = "eventNameObj", type = "cc")
	 */
	public function __construct(RunnerManager $runnerManagerObj, EventName $eventNameObj) {
		parent::__construct();
		
		$this->_runnerManagerObj = $runnerManagerObj;
		$this->_eventNameObj = $eventNameObj; //new EventName($runnerManagerObj);
	}
	
	/**
	 * 初始化
	 *
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
		$this->name_info['name']  = self::class;
		$this->name_info['intro'] = '事件处理本地模板类';
	}
	
	/**************************************************
	 * local func
	 **************************************************/
	
	/**
	 * @inheritDoc
	 */
	public function isPropagationStopped(): bool {
		return $this->_stopped;
	}
	
	/**
	 * 事件名称解析
	 *
	 * @param string $triggerName
	 *
	 * @return $this
	 */
	public function parse($triggerName = '') {
		$this->resetResult();
		
		$tName = empty($triggerName) ? $this->getTriggerName() : $triggerName;
		$this->setTriggerName($tName);
		
		$_eventNameObj = $this->getEventNameObj();
		$_eventNameObj->parse($tName);
		
		if ($_eventNameObj->isErr()) {
			$this->assignLastReturn($_eventNameObj->getLastReturn());
			
			return $this;
		}
		
		// $this->setEventNameParse($_eventNameObj->property2Arr());
		
		return $this;
	}
	
	/**
	 * 事件触发
	 *
	 * @return $this
	 */
	public function _trigger() {
		if ($this->isErr()) {
			return $this;
		}
		
		// $this->resetResult();
		
		// 拆分后的事件属性
		// $evtNameParse = $this->getEventNameParse();
		// $_behavior = $evtNameParse[EventName::PCRE_NAME_BEHAVIOR_INDEX];
		// $_timing = $evtNameParse[EventName::PCRE_NAME_TIMING_INDEX];
		
		// 需要之前已解析parse
		$_behavior = $this->getEventNameObj()->getBehavior();
		$_timing   = $this->getEventNameObj()->getTiming();
		
		if (is_callable([$this, 'on' . ucfirst($_behavior) . ucfirst($_timing)])) {
			$re = call_user_func_array([$this, 'on' . ucfirst($_behavior) . ucfirst($_timing)], $this->getParam());
			$this->assignLastReturn($re);
			
			return $this;
		}
		
		return $this;
	}
	
	/**
	 * 事件触发执行
	 *  【内部调用】
	 *
	 * @return $this
	 */
	public function handle() {
		return $this->_trigger();
	}
	
	/**
	 * 事件触发
	 *  在需要触发的地方 启动一个事件
	 *
	 * @param string $triggerName 触发名称 app.order.goods.add.before:cdd64cb6-29b8-4663-b1b5-f4f515ed28ca
	 * @param array  $param       触发参数 ['data' => [1, 2, ...]]
	 *
	 * @return $this
	 */
	public function t($triggerName = '', $param = []) {
		// 如果都传空 需要事先预知对象内已存在必要属性 否则缺少参数而异常
		$_triggerName = !empty($triggerName) ? $triggerName : $this->getTriggerName();
		$_param = !empty($param) ? $param : $this->getParam();
		
		// todo:
		$c = $this->parse($_triggerName)->setParam($_param)->getContainer();
		
		/** @var EventDispatcher $_evtDispatcher */
		$_evtDispatcher = $c->get(EventDispatcher::class); // ->_trigger();
		
		
		return $this;
	}
	
	protected function _listen($params) {
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
	
	public function on($params) {
		return $this->_listen($params);
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
	
	// /**
	//  * @return ServerRouteLocal
	//  */
	// public function getLocalObj(): ServerRouteLocal {
	// 	$this->_localObj === null && $this->_localObj = new ServerRouteLocal($this);
	//
	// 	return $this->_localObj;
	// }
	//
	// /**
	//  * @param ServerRouteLocal $localObj
	//  *
	//  * @return $this
	//  */
	// public function _setLocal(ServerRouteLocal $localObj) {
	// 	$this->_localObj = $localObj;
	//
	// 	return $this;
	// }
	
	/**
	 * @return string
	 */
	public function getTriggerName(): string {
		return $this->_triggerName;
	}
	
	/**
	 * @param string $triggerName
	 *
	 * @return $this
	 */
	public function setTriggerName(string $triggerName) {
		$this->_triggerName = $triggerName;
		
		return $this;
	}
	
	/**
	 * 获取事件名称对象实例
	 *
	 * @return EventName
	 */
	public function getEventNameObj() {
		// if (empty($this->_eventNameObj)) {
		// 	// 应该容器来创造实例 如果没有 只能构造
		// 	$this->_eventNameObj = new EventName(); // EventName::getInstance();
		// }
		
		return $this->_eventNameObj;
	}
	
	/**
	 * @param EventName $eventNameObj
	 *
	 * @return $this
	 */
	public function setEventNameObj(EventName $eventNameObj) {
		$this->_eventNameObj = $eventNameObj;
		
		return $this;
	}
	
	// /**
	//  * @return array
	//  */
	// public function getEventNameParse(): array {
	// 	return $this->_eventNameParse;
	// }
	//
	// /**
	//  * @param array $eventNameParse
	//  *
	//  * @return $this
	//  */
	// public function setEventNameParse(array $eventNameParse) {
	// 	$this->_eventNameParse = $eventNameParse;
	//
	// 	return $this;
	// }
	
	/**
	 * @return array
	 */
	public function getParam() {
		return $this->_param;
	}
	
	/**
	 * @param array $param
	 *
	 * @return $this
	 */
	public function setParam(array $param) {
		$this->_param = $param;
		
		return $this;
	}
	
	
}