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
	
	const PCRE_NAME = '/^(\w+)\.(\w+)\.(\w+)\.(\w+):{0,1}([0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12})?/';
	
	/**
	 * 事件完整名称
	 * @var string
	 */
	protected $_eventName = '';
	
	/**
	 * 事件类型
	 *  addon|plugin|app|sys
	 * @var string
	 */
	protected $_type = '';
	
	/**
	 * 组件名
	 *  {component_name|addon_name|plugin_name}
	 * @var string
	 */
	protected $_com = '';
	
	/**
	 * 事件名
	 *  {event_name}
	 * @var string
	 */
	protected $_event = '';
	
	/**
	 * 事件行为
	 *  {behavior_name}
	 * @var string
	 */
	protected $_behavior = '';
	
	/**
	 * UUID
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
		
		preg_match_all(self::PCRE_NAME, $eventName, $m, PREG_SET_ORDER);
		if (empty($m)) {
			$this->error('事件名称解析失败');
		}
		
		
		
		return $this;
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