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
 *  addon|plugin|app|sys.{component_name|addon_name|plugin_name}.{event_name}.{behavior_name}:{uuid}
 * 示例：
 *  app.order.goods.addBefore:cdd64cb6-29b8-4663-b1b5-f4f515ed28ca
 *
 * @package uujia\framework\base\common\lib\Event\Name
 */
class EventName extends BaseClass {
	use InstanceBase;
	use ResultBase;
	
	// addon|plugin|app|sys.{component_name|addon_name|plugin_name}.{event_name}.{behavior_name}:{uuid}
	const PCRE_NAME = '/^(\w+)\.(\w+)\.(\w+)\.(\w+):{0,1}([0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12})?/';
	
	// 事件名称拆分后数量 4或5
	const PCRE_NAME_SPLIT_COUNT = [4, 5];
	
	// 事件类型在事件名称拆分后的位置
	const PCRE_NAME_TYPE_INDEX = 0;
	
	// 组件名在事件名称拆分后的位置
	const PCRE_NAME_COM_INDEX = 1;
	
	// 事件名在事件名称拆分后的位置
	const PCRE_NAME_EVENT_INDEX = 2;
	
	// 事件行为在事件名称拆分后的位置
	const PCRE_NAME_BEHAVIOR_INDEX = 3;
	
	// 事件UUID在事件名称拆分后的位置
	const PCRE_NAME_UUID_INDEX = 4;
	
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
	 * UUID
	 *
	 * @var string
	 */
	protected $_uuid = '';
	
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
		(!in_array('type', $exclude)) && $this->_type = '';
		(!in_array('com', $exclude)) && $this->_com = '';
		(!in_array('event', $exclude)) && $this->_event = '';
		(!in_array('behavior', $exclude)) && $this->_behavior = '';
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
		
		if (!in_array(count($m), self::PCRE_NAME_SPLIT_COUNT)) {
			// todo: 异常
			$this->error('事件名称解析格式不正确');
			return $this;
		}
		
		(count($m) > self::PCRE_NAME_TYPE_INDEX) && $this->setType($m[self::PCRE_NAME_TYPE_INDEX]);
		(count($m) > self::PCRE_NAME_COM_INDEX) && $this->setCom($m[self::PCRE_NAME_COM_INDEX]);
		(count($m) > self::PCRE_NAME_EVENT_INDEX) && $this->setEvent($m[self::PCRE_NAME_EVENT_INDEX]);
		(count($m) > self::PCRE_NAME_BEHAVIOR_INDEX) && $this->setBehavior($m[self::PCRE_NAME_BEHAVIOR_INDEX]);
		(count($m) > self::PCRE_NAME_UUID_INDEX) && $this->setUuid($m[self::PCRE_NAME_UUID_INDEX]);
		
		$this->validateProperty();
		if ($this->isErr()) {
			return $this;
		}
		
		$this->ok();
		
		return $this;
	}
	
	/**
	 * 重组事件名称
	 *
	 * @param array $arr
	 */
	public function makeEventName($arr = []) {
		$this->resetResult();
		
		$_arr = $arr;
		if (empty($_arr)) {
			$_arr[self::PCRE_NAME_TYPE_INDEX]     = $this->getType();
			$_arr[self::PCRE_NAME_COM_INDEX]      = $this->getCom();
			$_arr[self::PCRE_NAME_EVENT_INDEX]    = $this->getEvent();
			$_arr[self::PCRE_NAME_BEHAVIOR_INDEX] = $this->getBehavior();
			$_arr[self::PCRE_NAME_UUID_INDEX]     = $this->getUuid();
		} else {
			!empty($_arr[self::PCRE_NAME_TYPE_INDEX]) && $this->setType($_arr[self::PCRE_NAME_TYPE_INDEX]);
			!empty($_arr[self::PCRE_NAME_COM_INDEX]) && $this->setCom($_arr[self::PCRE_NAME_COM_INDEX]);
			!empty($_arr[self::PCRE_NAME_EVENT_INDEX]) && $this->setEvent($_arr[self::PCRE_NAME_EVENT_INDEX]);
			!empty($_arr[self::PCRE_NAME_BEHAVIOR_INDEX]) && $this->setBehavior($_arr[self::PCRE_NAME_BEHAVIOR_INDEX]);
			!empty($_arr[self::PCRE_NAME_UUID_INDEX]) && $this->setUuid($_arr[self::PCRE_NAME_UUID_INDEX]);
		}
		
		$this->validateProperty();
		if ($this->isErr()) {
			return $this;
		}
		
		$_type = $this->getType();
		$_com = $this->getCom();
		$_event = $this->getEvent();
		$_behavior = $this->getBehavior();
		$_uuid = $this->getUuid();
		
		$_eventName = !empty($_uuid) ? "{$_type}.{$_com}.{$_event}.{$_behavior}:$_uuid" : "{$_type}.{$_com}.{$_event}.{$_behavior}";
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
			return $this->error('事件行为验不正确');
		}
		
		return $this->ok();
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
	
	
}