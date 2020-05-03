<?php


namespace uujia\framework\base\common\lib\Event\Name;


use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\traits\InstanceBase;
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
 * @package uujia\framework\base\common\lib\Event\Name
 */
class EventName extends BaseClass implements EventNameInterface {
	// use InstanceBase;
	use ResultBase;
	
	/**
	 * 事件完整名称
	 *
	 * @var string
	 */
	protected $_eventName = '';
	
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
	protected $_isParsed = false;
	
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
		(!in_array('isParsed', $exclude)) && $this->_isParsed = false;
		
		(!in_array('type', $exclude)) && $this->_type = '';
		(!in_array('com', $exclude)) && $this->_com = '';
		(!in_array('event', $exclude)) && $this->_event = '';
		(!in_array('behavior', $exclude)) && $this->_behavior = '';
		(!in_array('timing', $exclude)) && $this->_timing = '';
		(!in_array('uuid', $exclude)) && $this->_uuid = '';
		
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
		$this->setEventName($eventName);
		
		$re = preg_match_all(self::PCRE_NAME, $_eventName, $m, PREG_SET_ORDER);
		if ($re === false) {
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
		
		(count($m) > self::PCRE_NAME_TYPE_INDEX) && $this->setType($m[self::PCRE_NAME_TYPE_INDEX]);
		(count($m) > self::PCRE_NAME_COM_INDEX) && $this->setCom($m[self::PCRE_NAME_COM_INDEX]);
		(count($m) > self::PCRE_NAME_EVENT_INDEX) && $this->setEvent($m[self::PCRE_NAME_EVENT_INDEX]);
		(count($m) > self::PCRE_NAME_BEHAVIOR_INDEX) && $this->setBehavior($m[self::PCRE_NAME_BEHAVIOR_INDEX]);
		(count($m) > self::PCRE_NAME_TIMING_INDEX) && $this->setTiming($m[self::PCRE_NAME_TIMING_INDEX]);
		(count($m) > self::PCRE_NAME_UUID_INDEX) && $this->setUuid($m[self::PCRE_NAME_UUID_INDEX]);
		
		$this->validateProperty();
		if ($this->isErr()) {
			return $this;
		}
		
		$this->setIsParsed(true);
		$this->ok();
		
		return $this;
	}
	
	/**
	 * 重组事件名称
	 *
	 * @param array $arr
	 * @param bool  $isIgnoreUUID
	 *
	 * @return $this
	 */
	public function makeEventName($arr = [], $isIgnoreUUID = false) {
		$this->resetResult();
		
		$_arr = $arr;
		if (empty($_arr)) {
			$_arr = $this->property2Arr();
		} else {
			$this->arr2Property($_arr);
		}
		
		$this->validateProperty();
		if ($this->isErr()) {
			return $this;
		}
		
		$_type     = $this->getType();
		$_com      = $this->getCom();
		$_event    = $this->getEvent();
		$_behavior = $this->getBehavior();
		$_timing   = $this->getTiming();
		$_uuid     = $this->getUuid();
		
		if (empty($_timing)) {
			$_eventName = (!$isIgnoreUUID && !empty($_uuid)) ?
				"{$_type}.{$_com}.{$_event}.{$_behavior}:$_uuid" :
				"{$_type}.{$_com}.{$_event}.{$_behavior}";
		} else {
			$_eventName = (!$isIgnoreUUID && !empty($_uuid)) ?
				"{$_type}.{$_com}.{$_event}.{$_behavior}.{$_timing}:$_uuid" :
				"{$_type}.{$_com}.{$_event}.{$_behavior}.{$_timing}";
		}
		
		$this->setEventName($_eventName);
		
		$this->ok();
		
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
	 * 数组转属性
	 *  必须为全称 事件名称部分只能为全称5项 不得使用4项 不足可以随便补x
	 *
	 * @param array $arr
	 *
	 * @return $this
	 */
	public function arr2Property($arr = []) {
		$_arr = $arr;
		
		!empty($_arr[self::PCRE_NAME_TYPE_INDEX]) && $this->setType($_arr[self::PCRE_NAME_TYPE_INDEX]);
		!empty($_arr[self::PCRE_NAME_COM_INDEX]) && $this->setCom($_arr[self::PCRE_NAME_COM_INDEX]);
		!empty($_arr[self::PCRE_NAME_EVENT_INDEX]) && $this->setEvent($_arr[self::PCRE_NAME_EVENT_INDEX]);
		!empty($_arr[self::PCRE_NAME_BEHAVIOR_INDEX]) && $this->setBehavior($_arr[self::PCRE_NAME_BEHAVIOR_INDEX]);
		!empty($_arr[self::PCRE_NAME_TIMING_INDEX]) && $this->setTiming($_arr[self::PCRE_NAME_TIMING_INDEX]);
		!empty($_arr[self::PCRE_NAME_UUID_INDEX]) && $this->setUuid($_arr[self::PCRE_NAME_UUID_INDEX]);
		
		return $this;
	}
	
	/**
	 * 属性转数组
	 *  事件名称部分为全称5项 不足就是空
	 *
	 * @return array
	 */
	public function property2Arr() {
		$_arr = [];
		
		$_arr[self::PCRE_NAME_TYPE_INDEX]     = $this->getType();
		$_arr[self::PCRE_NAME_COM_INDEX]      = $this->getCom();
		$_arr[self::PCRE_NAME_EVENT_INDEX]    = $this->getEvent();
		$_arr[self::PCRE_NAME_BEHAVIOR_INDEX] = $this->getBehavior();
		$_arr[self::PCRE_NAME_TIMING_INDEX]   = $this->getTiming();
		$_arr[self::PCRE_NAME_UUID_INDEX]     = $this->getUuid();
		
		return $_arr;
	}
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
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
	public function isIsParsed(): bool {
		return $this->_isParsed;
	}
	
	/**
	 * @return bool
	 */
	public function isParsed(): bool {
		return $this->_isParsed;
	}
	
	/**
	 * @param bool $isParsed
	 *
	 * @return $this
	 */
	public function setIsParsed(bool $isParsed) {
		$this->_isParsed = $isParsed;
		
		return $this;
	}
	
	
}