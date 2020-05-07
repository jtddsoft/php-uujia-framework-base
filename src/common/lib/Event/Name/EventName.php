<?php


namespace uujia\framework\base\common\lib\Event\Name;


use uujia\framework\base\common\consts\EventConst;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Runner\RunnerManager;
use uujia\framework\base\common\lib\Utils\Arr;
use uujia\framework\base\common\traits\ResultBase;

/**
 * Class EventName
 * 事件名称分离器
 *
 * 事件定义（首字母小写驼峰）：
 *  addon|plugin|app|sys.{component_name|addon_name|plugin_name}.{event_name}.{behavior_name}.[{trigger_timing}]:{uuid}
 * 示例：
 *  app.order.goods.add.before:cdd64cb6-29b8-4663-b1b5-f4f515ed28ca
 *
 * 事件完整定义（缓存中的完整定义 evtl=event_listen  evtt=event_trigger evttl=event_trigger_listen）：
 *  {app_name}:{mode_name[evtl|evtt|evttl]}:
 *      addon|plugin|app|sys.{component_name|addon_name|plugin_name}.{event_name}.{behavior_name}[.{trigger_timing}]:{uuid}[:{tmp}]
 *      示例： shopMall:evtl:app.order.goods.add.before:cdd64cb6-29b8-4663-b1b5-f4f515ed28ca
 *
 * @package uujia\framework\base\common\lib\Event\Name
 */
class EventName extends BaseClass implements EventNameInterface {
	use ResultBase;
	
	/**
	 * 运行时管理对象
	 *  要取app_name
	 *
	 * @var RunnerManager
	 */
	protected $_runnerManagerObj;
	
	/**
	 * 事件完整名称
	 *
	 * @var string
	 */
	protected $_eventName = '';
	
	/**
	 * 应用名称
	 *
	 * @var string
	 */
	protected $_appName = '';
	
	/**
	 * 事件模式角色
	 *  监听者: evtl
	 *  触发者: evtt
	 *  监听者和触发者: evttl
	 *
	 * @var string
	 */
	protected $_modeName = '';
	
	/**
	 * 临时标识
	 *  tmp（在新添加触发者时 需要重建匹配的监听者 由于只需要重建他自己 为区分已有触发者 添加tmp标识）
	 *
	 * @var string
	 */
	protected $_tmp = '';
	
	/**
	 * 事件类型
	 *  addon|plugin|app|sys
	 *
	 * @var string
	 */
	protected $_type = '';
	
	/**
	 * 组件名
	 *  {component_name|addon_name|plugin_name}
	 *
	 * @var string
	 */
	protected $_com = '';
	
	/**
	 * 事件名
	 *  {event_name}
	 *
	 * @var string
	 */
	protected $_event = '';
	
	/**
	 * 事件行为
	 *  {behavior_name}
	 *
	 * @var string
	 */
	protected $_behavior = '';
	
	/**
	 * 事件触发时机
	 *  {trigger_timing}
	 *
	 * @var string
	 */
	protected $_timing = '';
	
	/**
	 * UUID
	 *
	 * @var string
	 */
	protected $_uuid = '';
	
	/**
	 * 是否解析
	 *
	 * @var bool
	 */
	protected $_parsed = false;
	
	/**
	 * 忽略临时标识tmp
	 *  （在缓存新添加触发者时 要重新构建只针对他自己的监听者
	 *    区别其他触发者使用 构建好要重命名）
	 *
	 * @var bool
	 */
	protected $_ignoreTmp = true;
	
	/**
	 * 忽略应用名称
	 *
	 * @var bool
	 */
	protected $_ignoreAppName = false;
	
	/**
	 * 忽略模式名称(evtl evtt evttl)
	 *
	 * @var bool
	 */
	protected $_ignoreModeName = false;
	
	/**
	 * 忽略UUID
	 *
	 * @var bool
	 */
	protected $_ignoreUUID = false;
	
	/**
	 * EventName constructor.
	 *
	 * @param RunnerManager $runnerManagerObj
	 */
	public function __construct(RunnerManager $runnerManagerObj) {
		$this->_runnerManagerObj = $runnerManagerObj;
		
		parent::__construct();
	}
	
	/**************************************************************
	 * init
	 **************************************************************/
	
	/**
	 * 复位 属性归零
	 *
	 * @param array $exclude
	 *
	 * @return $this
	 */
	public function reset($exclude = []) {
		(!in_array('parsed', $exclude)) && $this->_parsed = false;
		
		(!in_array('appName', $exclude)) && $this->_appName = '';
		(!in_array('modeName', $exclude)) && $this->_modeName = '';
		
		(!in_array('type', $exclude)) && $this->_type = '';
		(!in_array('com', $exclude)) && $this->_com = '';
		(!in_array('event', $exclude)) && $this->_event = '';
		(!in_array('behavior', $exclude)) && $this->_behavior = '';
		(!in_array('timing', $exclude)) && $this->_timing = '';
		(!in_array('uuid', $exclude)) && $this->_uuid = '';
		
		(!in_array('tmp', $exclude)) && $this->_tmp = self::EVENT_NAME_TMP_TEXT;
		
		$this->resetResult();
		
		return parent::reset($exclude);
	}
	
	/**************************************************************
	 * data
	 **************************************************************/
	
	/**
	 * 载入事件名称 拆分事件属性
	 *
	 * @param string $eventName
	 *
	 * @return $this
	 */
	public function parse($eventName = '') {
		$this->resetResult();
		
		$_eventName = !empty($eventName) ? $eventName : $this->getEventName();
		
		// 如果为精简模式 则默认填写app_name和mode_name （isIgnoreAppName和isIgnoreModeName必须配合成对使用）
		if ($this->isIgnoreAppName() && $this->isIgnoreModeName()) {
			$_appName = $this->getAppName() ?: ($this->getRunnerManagerObj()->getAppName() ?: 'app');
			$_modeName = $this->getModeName() ?: EventConst::CACHE_KEY_PREFIX_LISTEN;
			
			$_eventName = "{$_appName}:{$_modeName}:{$_eventName}";
		}
		
		$this->setEventName($eventName);
		
		$re = preg_match_all(self::PCRE_NAME, $_eventName, $m, PREG_SET_ORDER);
		if (!empty($re)) {
			// todo: 异常
			$this->error('事件名称解析失败');
			
			return $this;
		}
		
		if (empty($m)) {
			// todo: 异常
			$this->error('事件名称格式不正确解析失败');
			
			return $this;
		}
		
		// 校验匹配后所得数组元素个数 由于0的位置是匹配的全字符 要先减去1 所剩为真正匹配的各个属性
		if (!in_array(count($m) - 1, self::PCRE_NAME_SPLIT_COUNT)) {
			// todo: 异常
			$this->error('事件名称解析格式不正确');
			
			return $this;
		}
		
		(count($m) > self::PCRE_NAME_APPNAME_INDEX) && $this->setAppName($m[self::PCRE_NAME_APPNAME_INDEX]);
		(count($m) > self::PCRE_NAME_MODENAME_INDEX) && $this->setModeName($m[self::PCRE_NAME_MODENAME_INDEX]);
		
		(count($m) > self::PCRE_NAME_TYPE_INDEX) && $this->setType($m[self::PCRE_NAME_TYPE_INDEX]);
		(count($m) > self::PCRE_NAME_COM_INDEX) && $this->setCom($m[self::PCRE_NAME_COM_INDEX]);
		(count($m) > self::PCRE_NAME_EVENT_INDEX) && $this->setEvent($m[self::PCRE_NAME_EVENT_INDEX]);
		(count($m) > self::PCRE_NAME_BEHAVIOR_INDEX) && $this->setBehavior($m[self::PCRE_NAME_BEHAVIOR_INDEX]);
		(count($m) > self::PCRE_NAME_TIMING_INDEX) && $this->setTiming($m[self::PCRE_NAME_TIMING_INDEX]);
		(count($m) > self::PCRE_NAME_UUID_INDEX) && $this->setUuid($m[self::PCRE_NAME_UUID_INDEX]);
		
		(count($m) > self::PCRE_NAME_TMP_INDEX) && $this->setTmp($m[self::PCRE_NAME_TMP_INDEX]);
		
		$this->validateProperty();
		if ($this->isErr()) {
			return $this;
		}
		
		$this->setParsed(true);
		$this->ok();
		
		return $this;
	}
	
	/**
	 * 重组事件名称
	 *
	 * @return $this
	 */
	public function makeEventName() {
		$this->resetResult();
		
		// $_arr = $arr;
		// if (empty($_arr)) {
		// 	$_arr = $this->property2Arr();
		// } else {
		// 	$this->arr2Property($_arr);
		// }
		
		$this->validateProperty();
		if ($this->isErr()) {
			return $this;
		}
		
		$_appName  = $this->getAppName();
		$_modeName = $this->getModeName();
		
		$_type     = $this->getType();
		$_com      = $this->getCom();
		$_event    = $this->getEvent();
		$_behavior = $this->getBehavior();
		$_timing   = $this->getTiming();
		$_uuid     = $this->getUuid();
		
		$_eventNameArr = [];
		
		if ($this->isIgnoreAppName()) {
			$_eventNameArr[] = $_appName;
		}
		
		if ($this->isIgnoreModeName()) {
			$_eventNameArr[] = $_modeName;
		}
		
		// 事件名主体
		$_evNameArr   = [];
		$_evNameArr[] = $_type;
		$_evNameArr[] = $_com;
		$_evNameArr[] = $_event;
		$_evNameArr[] = $_behavior;
		!empty($_timing) && $_evNameArr[] = $_timing;
		
		$_eventNameArr[] = Arr::arrToStr($_evNameArr, '.');
		
		if ($this->isIgnoreUUID()) {
			$_eventNameArr[] = $_uuid;
		}
		
		if ($this->isIgnoreTmp()) {
			$_eventNameArr[] = self::EVENT_NAME_TMP_TEXT;
		}
		
		$_eventName = Arr::arrToStr($_eventNameArr, ':');
		
		$this->setEventName($_eventName);
		
		$this->ok();
		
		return $this;
	}
	
	/**************************************************************
	 * switch
	 **************************************************************/
	
	/**
	 * 切换为事件主体精简模式
	 *  只包含事件主体 忽略应用名称、事件模式角色evtt|evtl|evttl
	 *
	 * @return $this
	 */
	public function switchLite() {
		$this->setIgnoreAppName(true);
		$this->setIgnoreModeName(true);
		// $this->setIgnoreTmp(true);
		$this->setIgnoreUUID(false);
		
		return $this;
	}
	
	/**
	 * 切换为完整事件形式
	 *
	 * @return $this
	 */
	public function switchFull() {
		$this->setIgnoreAppName(false);
		$this->setIgnoreModeName(false);
		// $this->setIgnoreTmp(true);
		$this->setIgnoreUUID(false);
		
		return $this;
	}
	
	/**************************************************************
	 * validate
	 **************************************************************/
	
	/**
	 * 校验事件属性
	 *
	 * @return array|\think\response\Json
	 */
	public function validateProperty() {
		if (empty($this->getType()) || !in_array($this->getType(), ['addon', 'plugin', 'app', 'sys'])) {
			return $this->error('事件类型校验不正确');
		}
		
		if (empty($this->getCom())) {
			return $this->error('组件名校验不正确');
		}
		
		if (empty($this->getEvent())) {
			return $this->error('事件名校验不正确');
		}
		
		if (empty($this->getBehavior())) {
			return $this->error('事件行为校验不正确');
		}
		
		// if (empty($this->getTiming())) {
		// 	return $this->error('事件触发时机校验不正确');
		// }
		
		return $this->ok();
	}
	
	/**
	 * 转成前缀数组
	 *  用于缓存数据供应商的缓存key前缀
	 *
	 * @return array
	 */
	public function toPrefixArr() {
		$_arr = [];
		$_arr[] = $this->getAppName();
		$_arr[] = $this->getModeName();
		
		return $_arr;
	}
	
	/**
	 * 前缀数组还原属性
	 *  用于从缓存数据供应商的缓存key前缀还原
	 *
	 * @param array $arr
	 *
	 * @return EventName
	 */
	public function fromPrefixArr(array $arr = []) {
		$this->setAppName($arr[self::PCRE_NAME_APPNAME_INDEX] ?? $this->getAppName());
		$this->setModeName($arr[self::PCRE_NAME_MODENAME_INDEX] ?? $this->getModeName());
		
		return $this;
	}
	
	// /**
	//  * 数组转属性
	//  *  必须为全称 事件名称部分只能为全称5项 不得使用4项 不足可以随便补x
	//  *
	//  * @param array $arr
	//  *
	//  * @return $this
	//  */
	// public function arr2Property($arr = []) {
	// 	$_arr = $arr;
	//
	// 	!empty($_arr[self::PCRE_NAME_TYPE_INDEX]) && $this->setType($_arr[self::PCRE_NAME_TYPE_INDEX]);
	// 	!empty($_arr[self::PCRE_NAME_COM_INDEX]) && $this->setCom($_arr[self::PCRE_NAME_COM_INDEX]);
	// 	!empty($_arr[self::PCRE_NAME_EVENT_INDEX]) && $this->setEvent($_arr[self::PCRE_NAME_EVENT_INDEX]);
	// 	!empty($_arr[self::PCRE_NAME_BEHAVIOR_INDEX]) && $this->setBehavior($_arr[self::PCRE_NAME_BEHAVIOR_INDEX]);
	// 	!empty($_arr[self::PCRE_NAME_TIMING_INDEX]) && $this->setTiming($_arr[self::PCRE_NAME_TIMING_INDEX]);
	// 	!empty($_arr[self::PCRE_NAME_UUID_INDEX]) && $this->setUuid($_arr[self::PCRE_NAME_UUID_INDEX]);
	//
	// 	return $this;
	// }
	//
	// /**
	//  * 属性转数组
	//  *  事件名称部分为全称5项 不足就是空
	//  *
	//  * @return array
	//  */
	// public function property2Arr() {
	// 	$_arr = [];
	//
	// 	$_arr[self::PCRE_NAME_TYPE_INDEX]     = $this->getType();
	// 	$_arr[self::PCRE_NAME_COM_INDEX]      = $this->getCom();
	// 	$_arr[self::PCRE_NAME_EVENT_INDEX]    = $this->getEvent();
	// 	$_arr[self::PCRE_NAME_BEHAVIOR_INDEX] = $this->getBehavior();
	// 	$_arr[self::PCRE_NAME_TIMING_INDEX]   = $this->getTiming();
	// 	$_arr[self::PCRE_NAME_UUID_INDEX]     = $this->getUuid();
	//
	// 	return $_arr;
	// }
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
	/**
	 * @return RunnerManager
	 */
	public function getRunnerManagerObj(): RunnerManager {
		return $this->_runnerManagerObj;
	}
	
	/**
	 * @param RunnerManager $runnerManagerObj
	 * @return $this
	 */
	public function setRunnerManagerObj(RunnerManager $runnerManagerObj) {
		$this->_runnerManagerObj = $runnerManagerObj;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getEventName(): string {
		return $this->_eventName;
	}
	
	/**
	 * @param string $eventName
	 *
	 * @return $this
	 */
	public function setEventName(string $eventName) {
		$this->_eventName = $eventName;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getAppName(): string {
		return $this->_appName;
	}
	
	/**
	 * @param string $appName
	 * @return $this
	 */
	public function setAppName(string $appName) {
		$this->_appName = $appName;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getModeName(): string {
		return $this->_modeName;
	}
	
	/**
	 * @param string $modeName
	 * @return $this
	 */
	public function setModeName(string $modeName) {
		$this->_modeName = $modeName;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getType(): string {
		return $this->_type;
	}
	
	/**
	 * @param string $type
	 *
	 * @return $this
	 */
	public function setType(string $type) {
		$this->_type = $type;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getCom(): string {
		return $this->_com;
	}
	
	/**
	 * @param string $com
	 *
	 * @return $this
	 */
	public function setCom(string $com) {
		$this->_com = $com;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getEvent(): string {
		return $this->_event;
	}
	
	/**
	 * @param string $event
	 *
	 * @return $this
	 */
	public function setEvent(string $event) {
		$this->_event = $event;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getBehavior(): string {
		return $this->_behavior;
	}
	
	/**
	 * @param string $behavior
	 *
	 * @return $this
	 */
	public function setBehavior(string $behavior) {
		$this->_behavior = $behavior;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getTiming(): string {
		return $this->_timing;
	}
	
	/**
	 * @param string $timing
	 *
	 * @return $this
	 */
	public function setTiming(string $timing) {
		$this->_timing = $timing;
		
		return $this;
	}
	
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
	 * @return bool
	 */
	public function isParsed(): bool {
		return $this->_parsed;
	}
	
	/**
	 * @param bool $parsed
	 * @return $this
	 */
	public function setParsed(bool $parsed) {
		$this->_parsed = $parsed;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getTmp(): string {
		return $this->_tmp;
	}
	
	/**
	 * @param string $tmp
	 * @return $this
	 */
	public function setTmp(string $tmp) {
		$this->_tmp = $tmp;
		
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isIgnoreTmp(): bool {
		return $this->_ignoreTmp;
	}
	
	/**
	 * @param bool $ignoreTmp
	 *
	 * @return $this
	 */
	public function setIgnoreTmp(bool $ignoreTmp) {
		$this->_ignoreTmp = $ignoreTmp;
		
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isIgnoreAppName(): bool {
		return $this->_ignoreAppName;
	}
	
	/**
	 * @param bool $ignoreAppName
	 *
	 * @return $this
	 */
	public function setIgnoreAppName(bool $ignoreAppName) {
		$this->_ignoreAppName = $ignoreAppName;
		
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isIgnoreModeName(): bool {
		return $this->_ignoreModeName;
	}
	
	/**
	 * @param bool $ignoreModeName
	 *
	 * @return $this
	 */
	public function setIgnoreModeName(bool $ignoreModeName) {
		$this->_ignoreModeName = $ignoreModeName;
		
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isIgnoreUUID(): bool {
		return $this->_ignoreUUID;
	}
	
	/**
	 * @param bool $ignoreUUID
	 *
	 * @return $this
	 */
	public function setIgnoreUUID(bool $ignoreUUID) {
		$this->_ignoreUUID = $ignoreUUID;
		
		return $this;
	}
	
	
}